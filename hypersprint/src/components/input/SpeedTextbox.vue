<script setup>
import { ref, computed } from 'vue';

/*
 * Reactive state
 * -------------------------------------------------------------------------
 * 
 * Props accepted by this component and local storage variables.
 * 
 */
defineProps({
  placeholder: { type: String, default: 'Enter your text...' }
});

// Stores the textarea content
const userInput = ref('');

// Timestamp when the current typing session began
const sessionStart = ref(null);

// Accumulated active typing time in milliseconds
const accumulatedTime = ref(0);

// Tracks whether the textbox is currently focused and active
const isActive = ref(false);

/*
 * Reset component state
 * -------------------------------------------------------------------------
 * 
 * Clears all typing data and restores initial state.
 * 
 */

const reset = () => {
  userInput.value = '';
  sessionStart.value = null;
  accumulatedTime.value = 0;
  isActive.value = false;
};

/*
 * Pause active typing session
 * -------------------------------------------------------------------------
 * 
 * Adds the current active session duration into accumulated time.
 * 
 */

const pauseSession = () => {
  if(isActive.value && sessionStart.value) {
    accumulatedTime.value += (Date.now()-sessionStart.value);
    sessionStart.value = null;
    isActive.value = false;
  }
};

/*
 * Resume typing session
 * -------------------------------------------------------------------------
 * 
 * Resume session if user input text already exists.
 * 
 */
const resumeSession = () => {
  if(userInput.value.length && !isActive.value) {
    sessionStart.value = Date.now();
    isActive.value = true;
  }
};

/*
 * Handle user input
 * -------------------------------------------------------------------------
 * 
 * Starts timing on the very first typed character.
 * User input changes automatically recalculate cpm.
 * 
 */
const handleInput = () => {
  // If the user clears the box, reset everything
  if(!userInput.value.length) {
    reset();
    return;
  }

  // Start timing on the very first character entered
  if(sessionStart.value === null) {
    sessionStart.value = Date.now();
    isActive.value = true;
  }
};

/*
 * Handle textarea focus
 * -------------------------------------------------------------------------
 * 
 * Resumes timing when focus returns after a pause.
 * 
 */
const handleFocus = () => { resumeSession(); };

/*
 * Handle textarea blur
 * -------------------------------------------------------------------------
 * 
 * Pauses active timing when focus is lost.
 * 
 */
const handleBlur = () => { pauseSession(); };

/*
 * Computed active duration
 * -------------------------------------------------------------------------
 * 
 * Returns total active typing duration in milliseconds.
 * 
 */
const activeDurationMs = computed(() => {
  // If currently typing, include live session time
  if(sessionStart.value !== null) {
    return(accumulatedTime.value+(Date.now()-sessionStart.value));
  }

  // Otherwise return stored accumulated time
  return accumulatedTime.value;
});

/*
 * Computed typing duration (seconds)
 * -------------------------------------------------------------------------
 * 
 * Returns total elapsed time in seconds (sum of keystroke gaps).
 * 
 */
const activeDurationSeconds = computed(() => {
  return activeDurationMs.value/1000;
});

/*
 * Computed character count
 * -------------------------------------------------------------------------
 * 
 * Returns total characters inputted by the user.
 * 
 */
const totalCharacters = computed(() => {
  return userInput.value.length;
});

/*
 * Computed characters-per-minute (cpm)
 * -------------------------------------------------------------------------
 * 
 * Formula:
 * cpm = characters / minutes
 * 
 */
const cpm = computed(() => {
  if(totalCharacters.value === 0) return 0;

  // If currently typing, dynamically add the time elapsed in this session
  let totalTimeMs = accumulatedTime.value;
  if(isActive.value && sessionStart.value) {
    totalTimeMs += (Date.now()-sessionStart.value);
  }

  const minutes = totalTimeMs/1000/60;

  // Prevent divide-by-zero or extreme spikes on the first keystroke
  if(minutes < 0.001) return 0;

  return Math.round(totalCharacters.value/minutes);
});

/*
 * Public submit method
 * -------------------------------------------------------------------------
 * 
 * Returns current textbox content and typing metrics.
 * 
 */

const submit = () => {
  // Ensure active session is paused before final calculation
  pauseSession();

  return {
    text: userInput.value,
    cpm: cpm.value,
    duration: Math.round(activeDurationSeconds.value),
    characters: totalCharacters.value
  };
};

defineExpose({
  submit,
  reset
});
</script>

<template>
  <div class="speed-textbox">
    <textarea
      v-model="userInput"
      :placeholder="placeholder"
      @input="handleInput"
      @focus="handleFocus"
      @blur="handleBlur"
      class="form-control form-control-lg md-2 bg-grey text-white border-secondary font-monospace no-round"
      rows="1"
      autofocus
    />

    <!-- Live CPM label -->
    <div class="small label">
      {{ cpm }} cpm
    </div>
  </div>
</template>

<style scoped>
.no-round { border-radius: 0 !important; }
.bg-black { background-color: #000 !important; }
.bg-grey { background-color: #2a2a2a !important; }

textarea { 
  height: 45px; 
  resize: none; 
}
textarea::placeholder { color: rgba(255, 255, 255, 0.4) !important; }
textarea:focus { border-color: #0d6efd; outline: none; }

.label {
  text-align: right;
  margin-right: 1em;
  margin-bottom: 1em;
}
</style>