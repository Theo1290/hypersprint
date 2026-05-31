<script setup>
import { ref, onMounted, computed } from 'vue'

// Define properties (to handle form loading/submit states)
const props = defineProps({
  id: { type: String, default: null },
  name: { type: String, default: null },
  disabled: { type: Boolean, default: false }
})

// Define emits (to update the parent form status)
const emit = defineEmits(['verify']);

// Stores the input ID
const inputId = computed(() => { return props.id; });

// Stores the input name
const inputName = computed(() => { return props.name; });

// Stores generated data and user input
const captchaQuestion = ref('');
const expectedAnswer = ref(0);
const userInput = ref(null);

// Public helper for resetting captcha
const reset = () => {
  generateCaptcha();
}

// Generate a random math addition
const generateCaptcha = () => {
  const num1 = Math.floor(Math.random() * 10) + 1;
  const num2 = Math.floor(Math.random() * 10) + 1;
  captchaQuestion.value = `${num1} + ${num2} = ?`;
  expectedAnswer.value = num1 + num2;
  userInput.value = null;

  // Reset parent state on refresh
  emit('verify', false);
}

// Check input for answer on keypress
const checkAnswer = () => {
  if(userInput.value === expectedAnswer.value) {
    emit('verify', true);
  } else {
    emit('verify', false);
  }
}

// Initialize on load
onMounted(() => {
  generateCaptcha();
});

// Component public access
defineExpose({
  reset
});
</script>

<template>
    <div class="query d-flex align-items-center">
      <!-- User input -->
      <input
        type="number"
        class="answer-input form-control bg-grey border-secondary no-round font-monospace text-white"
        v-model.number="userInput"
        :id="inputId"
        :name = "inputName"
        :disabled="disabled"
        placeholder="Enter the solution..."
        @input="checkAnswer"
      >
      
      <!-- Captcha display -->
      <!-- Implements styling from main.css (glow-font-magenta) -->
      <div class="question-textbox bg-grey border-secondary no-round text-center font-monospace fw-bold glow-text-magenta">
        {{ captchaQuestion }}
      </div>

      <!-- Refresh button (clockwise circle icon character code) -->
      <div class="col-auto">
        <button
          type="button"
          class="btn btn-outline-secondary btn-sm"
          :disabled="disabled"
          @click="generateCaptcha"
          title="Refresh Captcha"
        >
          &#x21BB;
        </button>
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

.query {
  display: flex;
  gap: 1.0em;
}

.question-textbox {
  display: inline-block;
  width: 30%;
}

.answer-input {
  display: inline-block;
  width: 40%;
  resize: none;
  color: white;
}
.answer-input::placeholder { color: #ffffff66 !important; }
.answer-input:focus { border-color: var(--color-blue); outline: none; }

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
