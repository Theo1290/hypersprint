<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { callApi } from '../utils/api';
import { useAuthStore } from '@/stores/auth';
import Pusher from 'pusher-js';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();
const raceUuid = route.params.uuid;

// State
const challenge = ref(null);
const userInput = ref('');
const isFinished = ref(false);
const startTime = ref(null);
const countdown = ref(null);
const raceStarted = ref(false);
const error = ref('');

// Multiplayer State
const opponentProgress = ref(0); // Index of word opponent is on
const opponentName = ref('Connecting...');
const opponentFinished = ref(false);

// Pusher
let pusher = null;
let channel = null;

// Derived state
const wordArray = computed(() => challenge.value ? challenge.value.content_to_type.split(' ') : []);
const typedWords = computed(() => userInput.value.split(' '));
const currentWordIndex = computed(() => typedWords.value.length - 1);

const myProgressPercent = computed(() => {
  if (wordArray.value.length === 0) return 0;
  return (currentWordIndex.value / wordArray.value.length) * 100;
});

const opponentProgressPercent = computed(() => {
  if (wordArray.value.length === 0) return 0;
  return (opponentProgress.value / wordArray.value.length) * 100;
});

// Logic
const fetchRaceData = async () => {
  try {
    const data = await callApi(`/api/challenges.php?race_uuid=${raceUuid}`);
    if (data.challenge) {
      challenge.value = data.challenge;
      initPusher();
    }
  } catch (err) {
    error.value = 'Failed to load race data: ' + err.message;
  }
};

const initPusher = () => {
  pusher = new Pusher('5285b141d0e1f0c0cae6', { cluster: 'ap4' });
  channel = pusher.subscribe('race-' + raceUuid);

  // Listen for opponent progress
  channel.bind('progress-update', (data) => {
    if (parseInt(data.user_id) !== auth.user.id) {
      opponentProgress.value = data.word_index;
      if (data.username && opponentName.value === 'Connecting...') {
        opponentName.value = data.username;
      }
      if (data.finished) opponentFinished.value = true;
    }
  });

  // Listen for race start signal
  channel.bind('start-race', (data) => {
    console.log('[Race] Start signal received:', data);
    if (data.players) {
        Object.keys(data.players).forEach(id => {
            if (parseInt(id) !== auth.user.id) {
                opponentName.value = data.players[id];
                console.log('[Race] Opponent identified:', opponentName.value);
            }
        });
    }
    startCountdown();
  });

  // Broadcast that we are ready
  callApi('/api/race_sync.php', 'POST', { 
    action: 'ready', 
    race_uuid: raceUuid 
  });
};

const startCountdown = () => {
  countdown.value = 3;
  const timer = setInterval(() => {
    countdown.value--;
    if (countdown.value === 0) {
      clearInterval(timer);
      countdown.value = 'GO!';
      raceStarted.value = true;
      startTime.value = Date.now();
      setTimeout(() => { countdown.value = null; }, 1000);
    }
  }, 1000);
};

const handleInput = () => {
  if (!raceStarted.value || isFinished.value) return;

  // Send progress to opponent every word
  if (userInput.value.endsWith(' ')) {
    broadcastProgress(false);
  }

  // Check completion
  if (typedWords.value.length >= wordArray.value.length) {
      finishRace();
  }
};

const broadcastProgress = (finished) => {
  callApi('/api/race_sync.php', 'POST', {
    action: 'progress',
    race_uuid: raceUuid,
    user_id: auth.user.id,
    word_index: currentWordIndex.value,
    finished: finished
  });
};

const finishRace = () => {
  isFinished.value = true;
  broadcastProgress(true);
};

onMounted(fetchRaceData);
onUnmounted(() => {
  if (channel) channel.unbind_all();
  if (pusher) pusher.disconnect();
});

const getCharClass = (wordIndex, charIndex) => {
  const typedWord = typedWords.value[wordIndex];
  if (!typedWord || charIndex >= typedWord.length) return '';
  const targetWord = wordArray.value[wordIndex];
  if (!targetWord) return '';
  return typedWord[charIndex] === targetWord[charIndex] ? 'text-success' : 'text-danger-char';
};

const getWordClass = (index) => {
  if (index < currentWordIndex.value) return 'text-muted';
  return index === currentWordIndex.value ? 'current-word' : '';
};
</script>

<template>
  <div class="y2k-page-wrapper">
    <div class="race-wrapper p-4 d-flex flex-column h-100">
    <!-- Header: Match Info -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="glow-text-cyan m-0">1v1 BATTLEGRID</h1>
        <div class="badge border border-magenta text-magenta p-2 fs-5">SYNCED RACE</div>
    </div>

    <!-- The Track -->
    <div class="track-container mb-5 p-4 bg-black border-main">
        <!-- My Row -->
        <div class="racer-row mb-4">
            <div class="d-flex justify-content-between mb-2">
                <span class="glow-text-cyan fw-bold">YOU ({{ auth.user?.username }})</span>
                <span v-if="isFinished" class="badge bg-success">FINISHED</span>
            </div>
            <div class="progress-track">
                <div class="progress-bar-cyan" :style="{ width: myProgressPercent + '%' }">
                    <div class="car-head"></div>
                </div>
            </div>
        </div>

        <!-- Opponent Row -->
        <div class="racer-row">
            <div class="d-flex justify-content-between mb-2">
                <span class="glow-text-magenta fw-bold">{{ opponentName }}</span>
                <span v-if="opponentFinished" class="badge bg-danger">FINISHED</span>
            </div>
            <div class="progress-track">
                <div class="progress-bar-magenta" :style="{ width: opponentProgressPercent + '%' }">
                    <div class="car-head"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Countdown Overlay -->
    <div v-if="countdown !== null" class="countdown-overlay">
        <div class="countdown-text">{{ countdown }}</div>
    </div>

    <!-- Typing Grid -->
    <div v-if="challenge" class="typing-grid flex-grow-1 d-flex flex-column">
        <div class="typing-area p-4 bg-dark-glass mb-4 fs-3">
            <span v-for="(word, wIdx) in wordArray" :key="wIdx" :class="['word-span', getWordClass(wIdx)]">
                <span v-for="(char, cIdx) in word" :key="cIdx" :class="getCharClass(wIdx, cIdx)">{{ char }}</span>
                <span class="space">&nbsp;</span>
            </span>
        </div>

        <textarea
            v-model="userInput"
            @input="handleInput"
            :disabled="!raceStarted || isFinished"
            class="race-input p-3"
            placeholder="Wait for signal..."
            autofocus
        ></textarea>
    </div>

    <div v-if="error" class="alert alert-danger mt-4">{{ error }}</div>
  </div>
</div>
</template>

<style scoped>
.race-wrapper { min-height: 90vh; font-family: 'VT323', monospace; }
.border-main { border: 3px solid rgba(255, 255, 255, 0.2); }

.track-container { background: rgba(0,0,0,0.5); position: relative; }

.progress-track {
    height: 12px;
    background: rgba(255,255,255,0.1);
    border-radius: 6px;
    position: relative;
    overflow: visible;
}

.progress-bar-cyan {
    height: 100%;
    background: var(--y2k-cyan);
    box-shadow: 0 0 15px var(--y2k-cyan);
    transition: width 0.3s ease;
    position: relative;
}

.progress-bar-magenta {
    height: 100%;
    background: var(--y2k-magenta);
    box-shadow: 0 0 15px var(--y2k-magenta);
    transition: width 0.3s ease;
    position: relative;
}

.car-head {
    position: absolute;
    right: -5px;
    top: -10px;
    width: 20px;
    height: 32px;
    background: white;
    border: 2px solid black;
    box-shadow: 0 0 10px white;
}

.countdown-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.countdown-text {
    font-size: 15rem;
    color: var(--y2k-lime);
    text-shadow: 0 0 30px var(--y2k-lime);
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.bg-dark-glass { background: rgba(0,0,0,0.4); border: 2px solid rgba(255,255,255,0.1); color: #aaa; }
.word-span { display: inline-block; margin-right: 8px; }
.current-word { background: rgba(0, 255, 255, 0.2); color: white; border-bottom: 2px solid var(--y2k-cyan); }
.text-success { color: var(--y2k-lime) !important; }
.text-danger-char { color: #ff3366 !important; background: rgba(255, 51, 102, 0.2); }

.race-input {
    width: 100%;
    height: 100px;
    background: #111;
    border: 2px solid var(--y2k-cyan);
    color: white;
    font-size: 1.5rem;
    resize: none;
    outline: none;
}

.race-input:focus { box-shadow: 0 0 15px var(--y2k-cyan); }

.glow-text-cyan { color: var(--y2k-cyan); text-shadow: 0 0 8px var(--y2k-cyan); }
.glow-text-magenta { color: var(--y2k-magenta); text-shadow: 0 0 8px var(--y2k-magenta); }
.glow-text-lime { color: var(--y2k-lime); text-shadow: 0 0 8px var(--y2k-lime); }
</style>
