<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { callApi } from '../utils/api';

// Configuration
const modes = {
  time: [15, 30, 60],
  words: [25, 50, 100]
};

// State
const selectedMode = ref('words');
const selectedValue = ref(25);
const challenge = ref(null);
const userInput = ref('');
const startTime = ref(null);
const endTime = ref(null);
const isFinished = ref(false);
const results = ref(null);
const timeLeft = ref(null);
const timerInterval = ref(null);
const recentSprints = ref([]);

// Refs for scrolling
const typingAreaRef = ref(null);

// Derived state
const wordArray = computed(() => challenge.value ? challenge.value.content_to_type.split(' ') : []);
const typedWords = computed(() => userInput.value.split(' '));
const currentWordIndex = computed(() => typedWords.value.length - 1);

// Result for each word
const completedWordResults = computed(() => {
  return typedWords.value.slice(0, -1).map((typed, i) => {
    const target = wordArray.value[i];
    return {
      correct: typed === target,
      word: target
    };
  });
});

// Fetch dynamic challenge
const fetchChallenge = async () => {
  resetState();
  try {
    const data = await callApi(`/api/challenges.php?mode=${selectedMode.value}&value=${selectedValue.value}`);
    if (data.challenge) {
      challenge.value = data.challenge;
      if (selectedMode.value === 'time') {
        timeLeft.value = selectedValue.value;
      }
    }
  } catch (err) {
    console.error('Failed to fetch challenge:', err);
  }
};

const resetState = () => {
  userInput.value = '';
  startTime.value = null;
  endTime.value = null;
  isFinished.value = false;
  results.value = null;
  clearInterval(timerInterval.value);
  timeLeft.value = selectedMode.value === 'time' ? selectedValue.value : null;
};

onMounted(fetchChallenge);
onUnmounted(() => clearInterval(timerInterval.value));

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

// Mode switching
const setMode = (mode) => {
  selectedMode.value = mode;
  selectedValue.value = modes[mode][0];
  fetchChallenge();
};

const setValue = (val) => {
  selectedValue.value = val;
  fetchChallenge();
};

// Typing logic
const startTimer = () => {
  if (!startTime.value) {
    startTime.value = Date.now();
    if (selectedMode.value === 'time') {
      timerInterval.value = setInterval(() => {
        timeLeft.value--;
        if (timeLeft.value <= 0) {
          finishChallenge();
        }
      }, 1000);
    }
  }
};

const handleInput = (e) => {
  if (isFinished.value) return;
  startTimer();

  if (selectedMode.value === 'words' && challenge.value) {
    if (typedWords.value.length > wordArray.value.length || userInput.value === challenge.value.content_to_type) {
      finishChallenge();
    }
  }
};

const finishChallenge = async () => {
  clearInterval(timerInterval.value);
  endTime.value = Date.now();
  isFinished.value = true;
  
  const stats = calculateStats();
  results.value = stats;

  recentSprints.value.unshift({
    id: Date.now(),
    mode: selectedMode.value === 'time' ? `${selectedValue.value}s` : `${selectedValue.value} words`,
    wpm: stats.wpm,
    accuracy: stats.accuracy
  });

  try {
    await callApi('/api/results.php', 'POST', {
      challenge_uuid: challenge.value.challenge_id,
      wpm: stats.wpm,
      accuracy: stats.accuracy,
      time_taken: stats.timeTaken,
      experience: Math.round(stats.wpm * (stats.accuracy / 100))
    });
  } catch (err) {
    console.error('Failed to submit results:', err);
  }
};

const calculateStats = () => {
  const duration = (endTime.value - startTime.value) / 1000;
  const finalTypedWords = userInput.value.trim().split(' ');
  const finalResults = finalTypedWords.map((typed, i) => ({
    correct: typed === wordArray.value[i],
    word: wordArray.value[i] || ''
  }));

  const correctWords = finalResults.filter(r => r.correct);
  const totalCorrectChars = correctWords.reduce((sum, r) => sum + r.word.length + 1, 0);
  
  const wpm = Math.round((totalCorrectChars / 5) / (duration / 60)) || 0;
  const accuracy = finalResults.length > 0 
    ? Math.round((correctWords.length / finalResults.length) * 100) 
    : 0;

  return { wpm, accuracy, timeTaken: Math.round(duration) };
};

const getWordClass = (index) => {
  if (index < currentWordIndex.value) {
    const res = completedWordResults.value[index];
    return res.correct ? 'text-success' : 'text-danger';
  }
  return index === currentWordIndex.value ? 'current-word' : '';
};

const getCharClass = (wordIndex, charIndex) => {
  const typedWord = typedWords.value[wordIndex];
  if (!typedWord || charIndex >= typedWord.length) return '';
  const targetWord = wordArray.value[wordIndex];
  if (!targetWord) return '';
  return typedWord[charIndex] === targetWord[charIndex] ? 'text-success' : 'text-danger-char';
};
</script>

<template>
  <div class="row g-4 flex-grow-1 align-items-stretch mb-4">
    <!-- Recent Sprints Sidebar -->
    <div class="col-lg-2 d-flex">
      <div class="y2k-box w-100 d-flex flex-column no-round border-main bg-purple overflow-hidden">
        <div class="card-header bg-info text-black fw-bold no-round py-3 px-3">
          <i class="bi bi-clock-history me-2"></i>RECENT
        </div>
        <div class="card-body p-0 custom-scrollbar overflow-auto flex-grow-1">
          <ul class="list-group list-group-flush no-round">
            <li v-for="sprint in recentSprints" :key="sprint.id" class="list-group-item bg-transparent border-secondary text-light d-flex justify-content-between align-items-center py-3 px-3">
              <div>
                <div class="small text-info text-uppercase font-monospace">{{ sprint.mode }}</div>
                <div class="h4 mb-0 fw-bold">{{ sprint.wpm }} <small class="fs-6 opacity-50">WPM</small></div>
              </div>
              <div class="text-end">
                <div class="small opacity-50 font-monospace">ACC</div>
                <div class="fw-bold text-success">{{ sprint.accuracy }}%</div>
              </div>
            </li>
            <li v-if="recentSprints.length === 0" class="list-group-item bg-transparent text-center py-5 opacity-50">
              No sprints recorded
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Main Challenge Area -->
    <div class="col-lg-10 d-flex">
      <div v-if="challenge" class="y2k-box flex-grow-1 d-flex flex-column p-4 border-main bg-purple no-round">
        
        <!-- Aligned Top Bar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center gap-4">
            <h2 class="text-primary m-0 font-pixel h1">
              {{ challenge.title }}
            </h2>
            <span v-if="selectedMode === 'time'" class="badge border border-info text-uppercase no-round text-info fs-5 py-2 px-3">
              {{ timeLeft }}s
            </span>
          </div>

          <div class="d-flex gap-3">
            <div class="btn-group no-round border-main-thin">
              <button v-for="m in ['time', 'words']" :key="m" @click="setMode(m)" :class="['btn btn-std-font no-round', selectedMode === m ? 'btn-primary' : 'btn-outline-primary']">
                {{ m.toUpperCase() }}
              </button>
            </div>
            <div class="btn-group no-round border-main-thin">
              <button v-for="v in modes[selectedMode]" :key="v" @click="setValue(v)" :class="['btn btn-std-font no-round', selectedValue === v ? 'btn-info' : 'btn-outline-info']">
                {{ v }}
              </button>
            </div>
          </div>
        </div>
        
        <!-- Typing Area -->
        <div ref="typingAreaRef" class="typing-area position-relative mb-4 p-3 bg-black border border-secondary font-monospace fs-4 text-white-50 no-round custom-scrollbar overflow-auto">
          <span v-for="(word, wIdx) in wordArray" :key="wIdx" :class="['word-span', getWordClass(wIdx)]">
            <span v-for="(char, cIdx) in word" :key="cIdx" :class="getCharClass(wIdx, cIdx)">{{ char }}</span>
            <span class="space">&nbsp;</span>
          </span>
        </div>

        <!-- Input Box -->
        <textarea
          v-model="userInput"
          @input="handleInput"
          :disabled="isFinished"
          class="form-control form-control-lg mb-3 bg-grey text-white border-secondary font-monospace no-round custom-scrollbar"
          placeholder="Start typing..."
          rows="3"
          autofocus
        ></textarea>

        <!-- Persistent Restart Button -->
        <div class="text-center mt-2 mb-3">
          <button @click="fetchChallenge" class="btn btn-outline-light border-0 p-0 restart-btn" title="Restart Challenge">
            <i class="bi bi-arrow-clockwise fs-1"></i>
          </button>
        </div>

        <!-- Completion Stats -->
        <div v-if="isFinished" class="alert alert-success mt-auto bg-success text-white border-0 no-round shadow-sm">
          <h4 class="alert-heading fw-bold">Sprint Finished!</h4>
          <div class="row text-center mt-3">
            <div class="col">
              <div class="h3 mb-0 fw-bold">{{ results.wpm }}</div>
              <small class="opacity-75">WPM</small>
            </div>
            <div class="col border-start border-end border-white border-opacity-25">
              <div class="h3 mb-0 fw-bold">{{ results.accuracy }}%</div>
              <small class="opacity-75">ACC</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-round { border-radius: 0 !important; }

.bg-purple { background-color: var(--y2k-bg) !important; }
.bg-black { background-color: #000 !important; }
.bg-grey { background-color: #2a2a2a !important; }

/* Main consistent border color */
.border-main { border: 3px solid rgba(255, 255, 255, 0.2) !important; }
.border-main-thin { border: 1px solid rgba(255, 255, 255, 0.2) !important; }

.y2k-box {
  min-height: 100%;
  background: var(--y2k-bg);
}

.font-pixel {
  font-family: 'VT323', monospace !important;
  font-size: 2.5rem !important;
}

.typing-area { 
  height: 120px; 
  line-height: 1.6; 
  white-space: pre-wrap; 
  word-break: break-all; 
  scroll-behavior: smooth;
}

textarea { 
  height: 120px; 
  resize: none; 
}

textarea::placeholder {
  color: rgba(255, 255, 255, 0.4) !important;
}

.word-span { display: inline-block; padding: 0 2px; }
.current-word { background: rgba(13, 110, 253, 0.25); border-bottom: 2px solid #0d6efd; }
.text-success { color: #00ff88 !important; }
.text-danger { color: #ff3366 !important; text-decoration: line-through; }
.text-danger-char { color: #ff3366 !important; background: rgba(255, 51, 102, 0.2); }

textarea:focus { border-color: #0d6efd; outline: none; }

.list-group-item { border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important; }
.list-group-item:hover { background: rgba(255, 255, 255, 0.05); }

.btn-std-font {
  font-family: sans-serif !important;
  font-weight: 900;
  letter-spacing: 1px;
}

.restart-btn {
  transition: transform 0.2s ease-in-out;
  color: rgba(255, 255, 255, 0.6);
}

.restart-btn:hover {
  transform: rotate(90deg);
  color: white;
  background: transparent !important;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #000; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border: 1px solid #555; }
</style>
