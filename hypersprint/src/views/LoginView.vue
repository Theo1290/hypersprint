<script setup>
import { ref } from 'vue'
import SpeedTextbox from '@/components/input/SpeedTextbox.vue'

// Boolean state for non-sensitive interfacing
const is_authenticated = ref(false);

// Reference to the child SpeedTextbox component
const usernameSpeedTextbox = ref(null);
const passwordSpeedTextbox = ref(null);

// Stores final submitted typing results
const usernameResults = ref(null);
const passwordResults = ref(null);

// Retrieve data from SpeedTextbox and process it
const handleLogin = () => {
  if(!usernameSpeedTextbox.value || !passwordSpeedTextbox.value) return;

  usernameResults.value = usernameSpeedTextbox.value.submit();
  passwordResults.value = passwordSpeedTextbox.value.submit();
  console.log('Username Results:', usernameResults.value);
  console.log('Password Results:', usernameResults.value);
};
</script>

<template>
  <div class="container text-start mt-4 text-start">
    <h1>LOGIN</h1>
    <div v-if="is_authenticated">
      <p class="mb-4">You have already logged in!</p>
      <RouterLink to="/profile" class="btn y2k-btn me-3" style="color:#0ff;border-color:#0ff">PROFILE</RouterLink>
      <RouterLink to="/logout" class="btn y2k-btn" style="color:#f0f;border-color:#f0f">LOGOUT</RouterLink>
    </div>
    <div v-else>
      <p>How fast can you login?</p>
      <SpeedTextbox ref="usernameSpeedTextbox" placeholder="Enter your username..." />
      <SpeedTextbox ref="passwordSpeedTextbox" placeholder="Enter your password..." />
      
      <button class="btn btn-primary mt-3" @click="handleLogin">Submit</button>

      <div v-if="usernameResults" class="alert alert-success mt-3">
        <div><strong>CPM:</strong> {{ usernameResults.cpm }}</div>
        <div><strong>Duration:</strong> {{ usernameResults.duration }}s</div>
        <div><strong>Characters:</strong> {{ usernameResults.characters }}</div>
        <div><strong>Input:</strong> {{ usernameResults.text }}</div>
      </div>
      <div v-if="passwordResults" class="alert alert-success mt-3">
        <div><strong>CPM:</strong> {{ passwordResults.cpm }}</div>
        <div><strong>Duration:</strong> {{ passwordResults.duration }}s</div>
        <div><strong>Characters:</strong> {{ passwordResults.characters }}</div>
        <div><strong>Input:</strong> {{ passwordResults.text }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.y2k-btn { border-radius: 0; border-width: 2px; font-weight: bold; }
.y2k-btn:hover { background: rgba(255,255,255,0.1); }
.y2k-card { background: var(--y2k-glass); border: 2px solid var(--y2k-cyan); }
.y2k-stat { background: var(--y2k-glass); border: 1px solid var(--y2k-magenta); padding: 1rem 2rem; }
</style>
