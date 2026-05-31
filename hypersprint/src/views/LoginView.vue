<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { callApi } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'
import SpeedTextbox from '@/components/input/SpeedTextbox.vue'

// Router and authentication references
const router = useRouter();
const auth = useAuthStore();

// Interface state
const is_authenticated = ref(false);
const loading = ref(false);
const error = ref('');

// Reference to the child SpeedTextbox component
const usernameSpeedTextbox = ref(null);
const passwordSpeedTextbox = ref(null);

// Retrieve data from SpeedTextbox and process it
const handleSubmit = async (e) => {
  e.preventDefault();
  loading.value = true;
  error.value = '';

  // Text input must both have values
  if(!usernameSpeedTextbox.value || !passwordSpeedTextbox.value) {
    error.value = 'Please enter your username and password!';
    loading.value = false;
    return;
  }

  // Process data inside components
  const dataUsername = usernameSpeedTextbox.value.submit();
  const dataPassword = passwordSpeedTextbox.value.submit();

  // Check for early escape errors (regex)
  // No need to query the API for invalid entries
  // And no need to give unauthorized users hints
  if(!dataUsername.valid) {
    error.value = dataUsername.text;
    loading.value = false;
    return;
  } else if(!dataPassword.valid) {
    error.value = dataPassword.text;
    loading.value = false;
    return;
  }

  try {
    // Submit username and password to backend via API call
    const result = await callApi('/api/login.php', 'POST', {
      username: dataUsername.text,
      password: dataPassword.text
    });

    if(result.success) {
      // Authorize the user on success
      auth.setAuth(result.user);
      is_authenticated.value = true;
      
      // Redirect to profile after a short delay
      setTimeout(() => { router.push('/profile') }, 1500);
    }
  } catch(drop) {
    error.value = drop.message || 'Login failed! Please try again...';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="container text-start mt-4 text-start">
    <h1>LOGIN</h1>
    
    <div v-if="is_authenticated">
      <div class="alert alert-success bg-success text-white border-0 no-round mb-4">
        <h4 class="alert-heading fw-bold mb-0">SUCCESSFULLY LOGGED IN!</h4>
        <p class="mb-0">Redirecting to system profile...</p>
      </div>
      <RouterLink to="/profile" class="y2k-btn y2k-btn-cyan me-3">PROFILE</RouterLink>
      <RouterLink to="/logout" class="y2k-btn y2k-btn-magenta">LOGOUT</RouterLink>
    </div>

    <div v-else>
      <p>How fast can you login?</p>

      <div v-if="error" class="alert alert-danger bg-danger text-white border-0 no-round mb-4">
        {{ error }}
      </div>

      <form @submit="handleSubmit" style="margin-left: 1.2em;" aria-label="Login form">
        <div>
          <!-- Username input (uses SpeedTextbox component) -->
          <!-- Usernames must be 3-16 characters (letters, numbers, underscores) -->
          <label for="username" class="y2k-note">Username</label>
          <SpeedTextbox ref="usernameSpeedTextbox"
            id="username" name="username" type="text"
            placeholder="Enter username..."
            regex="^[a-zA-Z][a-zA-Z0-9_]{3,16}$"
            error="Your username was incorrect!"
            aria-label="Username input"
            autocomplete="username" required
          />

          <!-- Password input (uses SpeedTextbox component) -->
          <!-- Passwords must be 8-16 characters (one uppercase, one lowercase, one number) -->
          <label for="password" class="y2k-note">Password</label>
          <SpeedTextbox ref="passwordSpeedTextbox"
            id="password" name="password" type="password"
            placeholder="Enter password..."
            regex="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%^&*()_+=\-[\]{}|;:',./?]{8,16}$"
            error="Your password was incorrect!"
            aria-label="Password input"
            autocomplete="current-password" required
          />
          
          <!-- Submission button -->
          <button 
            type="submit"
            class="y2k-btn w-80 mt-3"
            aria-label="Submit login"
            :disabled="loading"
          >
            {{ loading ? 'AUTHENTICATING...' : 'LOGIN' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.w-80 {
  width: 80%;
}
</style>
