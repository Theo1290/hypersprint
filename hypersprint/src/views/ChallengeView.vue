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
const isSidebarOpen = ref(true);

// Refs for scrolling and focus
const typingAreaRef = ref(null);
const hiddenInputRef = ref(null);

// Derived state
const wordArray = computed(() => challenge.value ? challenge.value.content_to_type.split(' ') : []);
const typedWords = computed(() => userInput.value.split(' '));
const currentWordIndex = computed(() => typedWords.value.length - 1);
const currentCharIndex = computed(() => typedWords.value[currentWordIndex.value].length);

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
    const data = await callApi(`/cos30043/s103982457/Project/api/challenges.php?mode=${selectedMode.value}&value=${selectedValue.value}`);
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
  nextTick(() => hiddenInputRef.value?.focus());
};

onMounted(() => {
  fetchChallenge();
  window.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
  clearInterval(timerInterval.value);
  window.removeEventListener('keydown', handleGlobalKeydown);
});

const handleGlobalKeydown = (e) => {
  if (isFinished.value) return;
  // If user starts typing anywhere, focus the hidden input
  if (e.key.length === 1 || e.key === 'Backspace') {
    hiddenInputRef.value?.focus();
  }
};

const focusInput = () => {
  hiddenInputRef.value?.focus();
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

  let val = userInput.value;
  if (val.startsWith(' ')) {
    val = val.trimStart();
  }
  if (val.includes('  ')) {
    val = val.replace(/  +/g, ' ');
  }
  if (val !== userInput.value) {
    userInput.value = val;
  }

  if (selectedMode.value === 'words' && challenge.value) {
    const currentTyped = val.split(' ');
    const isLastWord = currentTyped.length === wordArray.value.length;
    const lastWordTyped = currentTyped[currentTyped.length - 1];
    const lastWordTarget = wordArray.value[wordArray.value.length - 1];

    if (currentTyped.length > wordArray.value.length || 
       (isLastWord && lastWordTyped === lastWordTarget)) {
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
    await callApi('/cos30043/s103982457/Project/api/results.php', 'POST', {
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

const getRenderedChars = (wIdx) => {
  const target = wordArray.value[wIdx] || '';
  const typed = typedWords.value[wIdx] || '';
  const len = Math.max(target.length, typed.length);
  const chars = [];
  for (let i = 0; i < len; i++) {
    chars.push({
      char: i < target.length ? target[i] : typed[i]
    });
  }
  return chars;
};

const getCharClass = (wordIndex, charIndex) => {
  const typedWord = typedWords.value[wordIndex];
  if (!typedWord || charIndex >= typedWord.length) return '';
  const targetWord = wordArray.value[wordIndex] || '';
  if (charIndex >= targetWord.length) return 'text-danger-char extra-char';
  return typedWord[charIndex] === targetWord[charIndex] ? 'text-success' : 'text-danger-char';
};

const isCurrentChar = (wIdx, cIdx) => {
  return wIdx === currentWordIndex.value && cIdx === currentCharIndex.value;
};
</script>

<template>
  <div v-if="challenge" class="d-flex flex-column flex-grow-1 h-100">
    
    <!-- Header with controls -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center gap-4">
        <h2 class="m-0 font-pixel h1">
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

    <div class="row g-4 flex-grow-1">
      <!-- Main Challenge Area -->
      <div :class="[isSidebarOpen ? 'col-lg-9' : 'col-lg-11', 'd-flex flex-column transition-all']">
        
        <!-- Unified Typing Engine -->
        <div 
          ref="typingAreaRef" 
          @click="focusInput"
          class="typing-engine position-relative mb-4 p-4 bg-black border-main font-monospace fs-3 text-white-50 no-round custom-scrollbar overflow-auto cursor-text"
        >
          <!-- Hidden Input Capture -->
          <input
            ref="hiddenInputRef"
            v-model="userInput"
            @input="handleInput"
            :disabled="isFinished"
            type="text"
            class="hidden-input"
            autofocus
          />

          <!-- Text Render Layer -->
          <div class="text-layer">
            <span class="word-wrapper" v-for="(word, wIdx) in wordArray" :key="wIdx">
              <span :class="['word-span', getWordClass(wIdx)]">
                <span v-for="(charObj, cIdx) in getRenderedChars(wIdx)" :key="cIdx" :class="['char-span', getCharClass(wIdx, cIdx), { 'is-current': isCurrentChar(wIdx, cIdx) }]">
                  {{ charObj.char }}
                </span>
              </span>
              <span :class="['space', { 'is-current': isCurrentChar(wIdx, getRenderedChars(wIdx).length) }]">&nbsp;</span>
            </span>
          </div>
        </div>

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

      <!-- Recent Sprints Sidebar -->
      <div :class="[isSidebarOpen ? 'col-lg-3' : 'col-lg-1', 'd-flex flex-column transition-all']">
        <div 
          :class="['y2k-sidebar-card d-flex flex-column h-100 bg-purple overflow-hidden cursor-pointer border-main', !isSidebarOpen ? 'collapsed-sidebar' : '']"
          @click="isSidebarOpen = !isSidebarOpen"
        >
          <div class="card-header bg-info text-black fw-bold no-round py-2 px-3 small d-flex justify-content-between align-items-center">
            <span v-if="isSidebarOpen"><i class="bi bi-clock-history me-2"></i>RECENT SPRINTS</span>
            <i :class="['bi', isSidebarOpen ? 'bi-chevron-right' : 'bi-chevron-left mx-auto']"></i>
          </div>
          
          <div v-if="isSidebarOpen" class="card-body p-0 custom-scrollbar overflow-auto flex-grow-1" @click.stop>
            <ul class="list-group list-group-flush no-round">
              <li v-for="sprint in recentSprints" :key="sprint.id" class="list-group-item bg-transparent border-main-thin border-top-0 border-start-0 border-end-0 text-light d-flex justify-content-between align-items-center py-2 px-3">
                <div>
                  <div class="small text-info text-uppercase font-monospace" style="font-size: 0.7rem;">{{ sprint.mode }}</div>
                  <div class="h5 mb-0 fw-bold">{{ sprint.wpm }} <small class="fs-6 opacity-50">WPM</small></div>
                </div>
                <div class="text-end">
                  <div class="small opacity-50 font-monospace" style="font-size: 0.7rem;">ACC</div>
                  <div class="fw-bold text-success">{{ sprint.accuracy }}%</div>
                </div>
              </li>
              <li v-if="recentSprints.length === 0" class="list-group-item bg-transparent text-center py-5 opacity-50 border-0">
                No sprints recorded
              </li>
            </ul>
          </div>
          
          <div v-else class="flex-grow-1 d-flex align-items-center justify-content-center">
             <div class="vertical-text fw-bold text-info small">RECENT</div>
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
.border-main { border: 2px solid rgba(0, 255, 255, 0.25) !important; }
.border-main-thin { border: 1px solid rgba(0, 255, 255, 0.25) !important; }

.font-pixel {
  font-family: 'VT323', monospace !important;
  font-size: 2.5rem !important;
}

.typing-engine { 
  height: 400px; 
  line-height: 1.8; 
  white-space: pre-wrap; 
  word-break: break-all; 
  scroll-behavior: smooth;
  outline: none;
}

.hidden-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.word-wrapper { display: inline-block; }
.word-span { display: inline-block; padding: 0 4px; position: relative; }
.char-span { position: relative; }
.space { position: relative; display: inline-block; width: 0.5em; }

/* The Cursor */
.is-current::before {
  content: '';
  position: absolute;
  left: 0;
  bottom: 10%;
  width: 2px;
  height: 80%;
  background-color: var(--y2k-cyan);
  animation: blink 1s infinite;
  box-shadow: 0 0 8px var(--y2k-cyan);
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50% { opacity: 0; }
}

.current-word { border-bottom: 2px solid rgba(13, 110, 253, 0.3); }

.text-success { 
  color: #00ff88 !important; 
  text-shadow: 0 0 5px #00ff88;
  opacity: 1;
}
.text-danger { 
  color: #ff3366 !important; 
  text-decoration: line-through;
  opacity: 1;
}
.text-danger-char { 
  color: #ff3366 !important; 
  background: rgba(255, 51, 102, 0.2);
  opacity: 1;
}

.list-group-item:hover { background: rgba(0, 255, 255, 0.05) !important; }

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

.transition-all {
  transition: all 0.3s ease-in-out;
}

.y2k-sidebar-card {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 0;
  min-height: 300px;
}

.y2k-sidebar-card .card-header {
  letter-spacing: 1px;
  font-size: 0.8rem;
}

.cursor-pointer {
  cursor: pointer;
}

.collapsed-sidebar {
  opacity: 0.7;
}

.collapsed-sidebar:hover {
  opacity: 1;
  background: rgba(0, 255, 255, 0.05);
}

.vertical-text {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  transform: rotate(180deg);
  letter-spacing: 4px;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #000; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border: 1px solid #555; }
</style>