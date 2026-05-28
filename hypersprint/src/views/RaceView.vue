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
const raceResult = ref(null); // 'win' or 'lose'
const showResultModal = ref(false);

// Refs for scrolling and focus
const typingAreaRef = ref(null);
const hiddenInputRef = ref(null);

// Pusher
let pusher = null;
let channel = null;

// Derived state
const wordArray = computed(() => challenge.value ? challenge.value.content_to_type.split(' ') : []);
const typedWords = computed(() => userInput.value.split(' '));
const currentWordIndex = computed(() => typedWords.value.length - 1);
const currentCharIndex = computed(() => {
  const currentTyped = typedWords.value[currentWordIndex.value];
  return currentTyped ? currentTyped.length : 0;
});

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
      if (data.finished) {
          opponentFinished.value = true;
          // If we haven't finished yet, the opponent won
          if (!isFinished.value) {
              raceResult.value = 'lose';
              finishRace(false);
          }
      }
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

  // Listen for opponent aborting
  channel.bind('opponent-aborted', (data) => {
      if (parseInt(data.user_id) !== auth.user.id && !isFinished.value) {
          raceResult.value = 'win';
          error.value = `${data.username} has aborted the mission. You win by default!`;
          finishRace(true);
      }
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
      setTimeout(() => { 
          countdown.value = null; 
          nextTick(() => hiddenInputRef.value?.focus());
      }, 1000);
    }
  }, 1000);
};

const handleGlobalKeydown = (e) => {
  if (isFinished.value || !raceStarted.value) return;
  if (e.key.length === 1 || e.key === 'Backspace') {
    hiddenInputRef.value?.focus();
  }
};

const focusInput = () => {
  hiddenInputRef.value?.focus();
};

const isCurrentChar = (wIdx, cIdx) => {
  return wIdx === currentWordIndex.value && cIdx === currentCharIndex.value;
};

const handleInput = () => {
  if (!raceStarted.value || isFinished.value) return;

  const currentIdx = currentWordIndex.value;
  const targetWord = wordArray.value[currentIdx];
  const typedWord = typedWords.value[currentIdx];

  // 1. Validation for moving to next word (space pressed)
  if (userInput.value.endsWith(' ')) {
    const completedIdx = currentIdx - 1;
    if (completedIdx >= 0) {
      const completedWord = typedWords.value[completedIdx];
      const expectedWord = wordArray.value[completedIdx];
      
      if (completedWord !== expectedWord) {
        userInput.value = userInput.value.slice(0, -1);
        return;
      }
      broadcastProgress(false);
    }
  }

  // 2. Completion Check
  if (currentIdx === wordArray.value.length - 1 && typedWord === targetWord) {
    finishRace(true);
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

const finishRace = (isWinner = false) => {
  if (isFinished.value) return;
  isFinished.value = true;
  
  if (isWinner && !raceResult.value) {
    raceResult.value = 'win';
  } else if (!isWinner && !raceResult.value) {
    raceResult.value = 'lose';
  }

  broadcastProgress(true);
  
  setTimeout(() => {
    showResultModal.value = true;
  }, 800);
};

const abortRace = async () => {
    if (isFinished.value) {
        router.push('/multiplayer');
        return;
    }
    
    try {
        await callApi('/api/race_sync.php', 'POST', {
            action: 'abort',
            race_uuid: raceUuid
        });
    } catch (err) {
        console.error('Abort failed:', err);
    }
    router.push('/multiplayer');
};

onMounted(() => {
    fetchRaceData();
    window.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
  if (!isFinished.value && raceStarted.value) {
      callApi('/api/race_sync.php', 'POST', {
          action: 'abort',
          race_uuid: raceUuid
      });
  }
  if (channel) channel.unbind_all();
  if (pusher) pusher.disconnect();
  window.removeEventListener('keydown', handleGlobalKeydown);
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

// Auto-scroll logic
watch(currentWordIndex, () => {
  nextTick(() => {
    if (typingAreaRef.value) {
      const currentWordEl = typingAreaRef.value.querySelector('.current-word');
      if (currentWordEl) {
        currentWordEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });
});
</script>

<template>
  <div class="y2k-page-wrapper">
    <div class="race-wrapper p-4 d-flex flex-column h-100">
    <!-- Header: Match Info -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="glow-text-cyan m-0">1v1 BATTLEGRID</h1>
        <div class="d-flex gap-3 align-items-center">
            <button @click="abortRace" class="btn btn-outline-danger btn-sm font-pixel">ABORT MISSION</button>
            <div class="badge border border-magenta text-magenta p-2 fs-5">SYNCED RACE</div>
        </div>
    </div>

    <!-- The Track -->
    <div class="track-container mb-5 p-4 bg-black border-main">
        <!-- My Row -->
        <div class="racer-row mb-4">
            <div class="d-flex justify-content-between mb-2">
                <span class="glow-text-cyan fw-bold">YOU ({{ auth.user?.username }})</span>
                <span v-if="isFinished && raceResult === 'win'" class="badge bg-success">WINNER</span>
                <span v-else-if="isFinished" class="badge bg-secondary">FINISHED</span>
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
                <span v-if="opponentFinished && raceResult === 'lose'" class="badge bg-danger">WINNER</span>
                <span v-else-if="opponentFinished" class="badge bg-secondary">FINISHED</span>
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
        <div 
          ref="typingAreaRef" 
          @click="focusInput"
          class="typing-display-grid p-4 bg-dark-glass border-main mb-4 cursor-text"
        >
          <!-- Stealth Input -->
          <textarea
            ref="hiddenInputRef"
            v-model="userInput"
            @input="handleInput"
            :disabled="!raceStarted || isFinished"
            class="stealth-input"
            spellcheck="false"
            autofocus
          ></textarea>

          <div class="text-layer">
            <span class="word-wrapper" v-for="(word, wIdx) in wordArray" :key="wIdx">
              <span :class="['word-span', getWordClass(wIdx)]">
                <span v-for="(char, cIdx) in word" :key="cIdx" :class="['char-span', getCharClass(wIdx, cIdx), { 'is-current': isCurrentChar(wIdx, cIdx) }]">
                  {{ char }}
                </span>
              </span>
              <span :class="['space', { 'is-current': isCurrentChar(wIdx, word.length) }]">&nbsp;</span>
            </span>
          </div>
        </div>
    </div>

    <div v-if="error" class="alert alert-warning mt-4 font-pixel fs-4">{{ error }}</div>

    <!-- Result Modal Overlay -->
    <div v-if="showResultModal" class="result-modal-overlay">
        <div class="result-card p-5 text-center">
            <h1 :class="raceResult === 'win' ? 'glow-text-lime' : 'glow-text-magenta'" class="mb-4 display-3">
                {{ raceResult === 'win' ? 'VICTORY' : 'DEFEAT' }}
            </h1>
            
            <div class="result-body fs-4 mb-5">
                <p v-if="raceResult === 'win'" class="text-white">
                    You dominated the battlegrid! The opponent was left in your wake.
                </p>
                <p v-else class="text-white">
                    The opponent reached the finish line first. Your pulse was too slow.
                </p>
            </div>

            <div class="d-flex flex-column gap-3">
                <button @click="router.push('/multiplayer')" class="y2k-btn y2k-btn-cyan">NEW CHALLENGE</button>
                <button @click="router.push('/')" class="y2k-btn y2k-btn-magenta">RETURN TO HUB</button>
            </div>        </div>
    </div>
  </div>
</div>
</template>


<style scoped>
/* Local styles moved to main.css */
</style>
