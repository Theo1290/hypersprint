<script setup>
import { ref } from 'vue'
import SpeedTextbox from '@/components/input/SpeedTextbox.vue'

// Boolean state for non-sensitive interfacing
const is_authenticated = ref(false);

// Reference to the child SpeedTextbox component
const usernameSpeedTextbox = ref(null);
const passwordSpeedTextbox = ref(null);

// Stores final submitted typing results
const data = ref({
  username: '',
  password: ''
})

// Retrieve data from SpeedTextbox and process it
const handleLogin = () => {
  if(!usernameSpeedTextbox.value || !passwordSpeedTextbox.value) return;

  var dataUsername = usernameSpeedTextbox.value.submit();
  var dataPassword = passwordSpeedTextbox.value.submit();

  data.value.username = dataUsername.text;
  data.value.password = dataPassword.text;

  // Testing logs
  //console.log('Username:', dataUsername.characters, 'chars in', dataUsername.duration, 'secs =', dataUsername.cpm, 'CPM');
  //console.log('Password:', dataPassword.characters, 'chars in', dataPassword.duration, 'secs =', dataPassword.cpm, 'CPM');
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
      <form style="margin-left: 1.2em;">
        <div>
        <!-- Username input (uses SpeedTextbox component) -->
        <label for="username" class="y2k-note">Username</label>
        <SpeedTextbox ref="usernameSpeedTextbox"
          id="username" name="username" type="text"
          placeholder="Enter username..."
          autocomplete="username" required
        />

        <!-- Password input (uses SpeedTextbox component) -->
        <label for="password" class="y2k-note">Password</label>
        <SpeedTextbox ref="passwordSpeedTextbox"
          id="password" name="password" type="password"
          placeholder="Enter password..."
          autocomplete="current-password" required
        />
        
        <!-- Submission button -->
        <button type="submit" class="btn y2k-btn btn-primary mt-3" @click="handleLogin">Submit</button>
        </div>

      </form>

      <!-- Output testing display
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
      -->
    </div>
  </div>
</template>

<style scoped>
.y2k-btn { border-radius: 0; border-width: 2px; font-weight: bold; }
.y2k-btn:hover { background: rgba(255,255,255,0.1); }
.y2k-btn.btn-primary { width: 80%; }
.y2k-card { background: var(--y2k-glass); border: 2px solid var(--y2k-cyan); }
.y2k-stat { background: var(--y2k-glass); border: 1px solid var(--y2k-magenta); padding: 1rem 2rem; }
.y2k-note { background: var(--y2k-glass); line-height: 1.5rem; font-size: medium; }
</style>
