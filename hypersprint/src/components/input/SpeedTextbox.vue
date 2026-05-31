<script setup>
import { ref, computed } from 'vue';

/*
 * Reactive state
 * -------------------------------------------------------------------------
 * 
 * Props accepted by this component and local storage variables.
 * 
 */
const props = defineProps({
  id: { type: String, default: null },
  name: { type: String, default: null },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: 'Enter your text...' },
  regex: { type: String, default: null },
  error: { type: String, default: 'Your input is not valid!' }
});

// Stores the input ID
const inputId = computed(() => { return props.id; });

// Stores the input name
const inputName = computed(() => { return props.name; });

// Stores the input type
const inputType = ref(props.type);

// Stores the input content
const userInput = ref('');

// Stores the regular expression error message
const error = ref(props.error);

// Timestamp when the current typing session began
const sessionStart = ref(null);

// Accumulated active typing time in milliseconds
const accumulatedTime = ref(0);

// Tracks whether the textbox is currently focused and active
const isActive = ref(false);

// Tracks visibility of sanitized text state (for password show/hide)
const isVisible = ref(false);

// Checks if the input string passes regular expression validation
const isValid = computed(() => {
  if(!props.regex) return true;

  try {
    const pattern = new RegExp(props.regex);
    return pattern.test(userInput.value);
  } catch {
    return false;
  }
});

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
  isVisible.value = false;
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
 * Handle input focus
 * -------------------------------------------------------------------------
 * 
 * Resumes timing when focus returns after a pause.
 * 
 */
const handleFocus = () => { resumeSession(); };

/*
 * Handle input blur
 * -------------------------------------------------------------------------
 * 
 * Pauses active timing when focus is lost.
 * 
 */
const handleBlur = () => { pauseSession(); };

/*
 * Toggle text visibility
 * -------------------------------------------------------------------------
 * 
 * Dynamically switch between 'password' and 'text' types.
 * 
 */
const toggleVisibility = () => {
  if(inputType.value === 'text') {
    inputType.value = 'password';
    isVisible.value = false;
  } else if(inputType.value === 'password') {
    inputType.value = 'text';
    isVisible.value = true;
  }
};

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

  // Package all the data
  var data = {
    valid: isValid.value,
    text: (isValid.value ? userInput.value : error.value ),
    cpm: cpm.value,
    duration: Math.round(activeDurationSeconds.value),
    characters: totalCharacters.value
  };

  // Cleanup and return data
  reset();
  return(data);
};

// Component modifications
defineOptions({
  inheritAttrs: false
});

// Component public access
defineExpose({
  submit,
  reset
});
</script>

<template>
  <!-- User input -->
  <div class="speed-textbox">
    <input
      v-model="userInput"
      v-bind="$attrs"
      :id="inputId"
      :name = "inputName"
      :type="inputType"
      :placeholder="placeholder"
      :regex="regex"
      :error="error"
      @input="handleInput"
      @focus="handleFocus"
      @blur="handleBlur"
      class="speed-input form-control form-control-lg md-2 bg-grey border-secondary no-round font-monospace text-white"
      autocapitalize="none"
      autocorrect="off"
      autofocus
      rows="1"
    />

    <!-- Toggle Button (only shows if type is 'password') -->
    <button 
      v-if="type === 'password' && totalCharacters > 0"
      type="button"
      @click="toggleVisibility"
      class="btn btn-visible"
      tabindex="-1"
    >
      {{ isVisible ? 'Hide' : 'Show' }}
    </button>

    <!-- Live CPM label -->
    <!-- Implements styling from main.css (glow-text-lime) -->
    <div class="label glow-text-lime">
      {{ cpm }} cpm
    </div>
  </div>
</template>

<!-- Base styling included for fallback stability (reusable site-wide) -->
<style scoped>
:root {
  --color-black: #0c1017;
  --color-grey: #ffffff66;
  --color-blue: #0d6efd;
}

.no-round { border-radius: 0 !important; }
.bg-grey { background-color: #2a2a2a !important; }

.speed-textbox {
  display: inline-flex;
  gap: 0.5em;
  width: 100%;
  height: 2em;
  margin-bottom: 1em;
}

.speed-input {
  width: 80%;
  height: 100%;
  resize: none;
}
.speed-input::placeholder { color: #ffffff66 !important; }
.speed-input:focus { border-color: var(--color-blue); outline: none; }

.btn-visible {
  position: inline-block;
  transform: translateX(-3.8em);
  height: 100%;
  color: var(--color-blue);
  text-align: center;
  margin-right: -3.8em;
}

.label {
  position: inline-block;
  align-self: center;
  text-wrap: nowrap;
}
</style>