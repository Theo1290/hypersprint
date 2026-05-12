<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue';
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
const currentWordInput = ref('');
const currentWordIndex = ref(0);
const wordResults = ref([]); // Array of { correct: boolean, word: string }
const startTime = ref(null);
const endTime = ref(null);
const isFinished = ref(false);
const results = ref(null);
const timeLeft = ref(null);
const timerInterval = ref(null);

// Derived state
const wordArray = computed(() => challenge.value ? challenge.value.content_to_type.split(' ') : []);

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
  currentWordInput.value = '';
  currentWordIndex.value = 0;
  wordResults.value = [];
  startTime.value = null;
  endTime.value = null;
  isFinished.value = false;
  results.value = null;
  clearInterval(timerInterval.value);
  timeLeft.value = selectedMode.value === 'time' ? selectedValue.value : null;
};

onMounted(fetchChallenge);
onUnmounted(() => clearInterval(timerInterval.value));

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

  const input = currentWordInput.value;
  
  // Check for space key to complete a word
  if (input.endsWith(' ')) {
    const typedWord = input.trim();
    const targetWord = wordArray.value[currentWordIndex.value];
    
    // Record result
    wordResults.value.push({
      correct: typedWord === targetWord,
      word: targetWord
    });
    
    currentWordIndex.value++;
    currentWordInput.value = '';

    // Check if we've finished the last word in "words" mode
    if (selectedMode.value === 'words' && currentWordIndex.value >= wordArray.value.length) {
      finishChallenge();
    }
  }
};

const finishChallenge = async () => {
  clearInterval(timerInterval.value);
  endTime.value = Date.now();
  
  // If in time mode, add the partial current word result if it matches
  if (selectedMode.value === 'time' && currentWordInput.value.trim().length > 0) {
    wordResults.value.push({
      correct: currentWordInput.value.trim() === wordArray.value[currentWordIndex.value],
      word: wordArray.value[currentWordIndex.value]
    });
  }

  isFinished.value = true;
  const stats = calculateStats();
  results.value = stats;

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

// Stats calculation
const calculateStats = () => {
  const duration = (endTime.value - startTime.value) / 1000;
  
  // Only count correct words for WPM calculation
  const correctWords = wordResults.value.filter(r => r.correct);
  const totalCorrectChars = correctWords.reduce((sum, r) => sum + r.word.length + 1, 0); // +1 for space
  
  // Standard WPM = (correct characters / 5) / (seconds / 60)
  const wpm = Math.round((totalCorrectChars / 5) / (duration / 60)) || 0;
  
  const accuracy = wordResults.value.length > 0 
    ? Math.round((correctWords.length / wordResults.value.length) * 100) 
    : 0;

  return { wpm, accuracy, timeTaken: Math.round(duration) };
};

// Word display helper
const getWordClass = (index) => {
  if (index < currentWordIndex.value) {
    return wordResults.value[index].correct ? 'text-success' : 'text-danger';
  }
  if (index === currentWordIndex.value) {
    return 'current-word';
  }
  return '';
};

// Check current word characters for live feedback
const getCharClass = (wordIndex, charIndex) => {
  if (wordIndex !== currentWordIndex.value) return '';
  const input = currentWordInput.value;
  if (charIndex >= input.length) return '';
  
  return input[charIndex] === wordArray.value[wordIndex][charIndex] 
    ? 'text-success' 
    : 'text-danger-char';
};

</script>

<template>
  <div class="container mt-5">
    <!-- Mode Selector -->
    <div class="d-flex justify-content-center gap-4 mb-4">
      <div class="btn-group no-round">
        <button 
          v-for="mode in ['time', 'words']" 
          :key="mode"
          @click="setMode(mode)"
          :class="['btn', selectedMode === mode ? 'btn-primary' : 'btn-outline-primary']"
        >
          {{ mode.toUpperCase() }}
        </button>
      </div>
      
      <div class="btn-group no-round">
        <button 
          v-for="val in modes[selectedMode]" 
          :key="val"
          @click="setValue(val)"
          :class="['btn', selectedValue === val ? 'btn-info' : 'btn-outline-info']"
        >
          {{ val }}
        </button>
      </div>
    </div>

    <div v-if="challenge" class="card p-4 shadow-lg border-primary bg-dark text-light no-round">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-primary m-0">
          <span v-if="selectedMode === 'time'" class="font-monospace">
            {{ timeLeft }}s remaining
          </span>
          <span v-else>
            {{ challenge.title }}
          </span>
        </h2>
        <span class="badge bg-secondary text-uppercase no-round">{{ challenge.difficulty }}</span>
      </div>
      
      <div class="typing-area position-relative mb-4 p-3 bg-black border border-secondary font-monospace fs-4 text-white-50 overflow-hidden no-round">
        <!-- Render words -->
        <span 
          v-for="(word, wIndex) in wordArray" 
          :key="wIndex" 
          :class="['word-span', getWordClass(wIndex)]"
        >
          <span 
            v-for="(char, cIndex) in word" 
            :key="cIndex" 
            :class="getCharClass(wIndex, cIndex)"
          >{{ char }}</span>
          <span class="space">&nbsp;</span>
        </span>
      </div>

      <input
        v-model="currentWordInput"
        @input="handleInput"
        :disabled="isFinished"
        type="text"
        class="form-control form-control-lg mb-3 bg-dark text-white border-secondary font-monospace no-round"
        placeholder="Type the words here..."
        autofocus
      />

      <div v-if="isFinished" class="alert alert-success mt-3 bg-success text-white border-0 no-round">
        <h4 class="alert-heading fw-bold">Sprint Finished!</h4>
        <div class="row text-center mt-3">
          <div class="col">
            <div class="h3 mb-0 fw-bold">{{ results.wpm }}</div>
            <small class="opacity-75">WPM</small>
          </div>
          <div class="col border-start border-end border-white border-opacity-25">
            <div class="h3 mb-0 fw-bold">{{ results.accuracy }}%</div>
            <small class="opacity-75">Accuracy</small>
          </div>
          <div class="col">
            <div class="h3 mb-0 fw-bold">{{ results.timeTaken }}s</div>
            <small class="opacity-75">Duration</small>
          </div>
        </div>
        <button @click="fetchChallenge" class="btn btn-light w-100 mt-4 fw-bold no-round">Restart Challenge</button>
      </div>
    </div>
    
    <div v-else class="text-center mt-5">
      <div class="spinner-border text-primary no-round" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-round {
  border-radius: 0 !important;
}

.typing-area {
  min-height: 120px;
  max-height: 200px;
  line-height: 1.6;
  white-space: pre-wrap;
  word-break: break-all;
  user-select: none;
}

.word-span {
  display: inline-block;
}

.current-word {
  background-color: rgba(13, 110, 253, 0.2);
  border-bottom: 2px solid #0d6efd;
}

.text-success {
  color: #00ff88 !important;
}

.text-danger {
  color: #ff3366 !important;
  text-decoration: line-through;
}

.text-danger-char {
  color: #ff3366 !important;
  background-color: rgba(255, 51, 102, 0.2);
}

input {
  transition: all 0.2s ease;
}

input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 15px rgba(13, 110, 253, 0.3);
  outline: none;
}

.card {
  border-width: 2px;
  background: linear-gradient(145deg, #1a1a1a, #0a0a0a);
}

.btn-group .btn {
  border-width: 2px;
  font-weight: bold;
}
</style>
