<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { callApi } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'
import SpeedTextbox from '@/components/input/SpeedTextbox.vue'
import CaptchaQuery from '@/components/input/CaptchaQuery.vue'

// Router and authentication references
const router = useRouter();
const auth = useAuthStore();

// Interface state
const is_authenticated = ref(false);
const loading = ref(false);
const error = ref('');

// Captcha mechanic
const captchaQuery = ref(null);
const isCaptchaValid = ref(false);
const isSubmitting = ref(false);

// Keep track of the validation status
const handleCaptchaVerify = (isValid) => {
  isCaptchaValid.value = isValid;
}

// Reference to the child SpeedTextbox component
const usernameSpeedTextbox = ref(null);
const passwordSpeedTextbox = ref(null);
const confirmSpeedTextbox = ref(null);

// Retrieve data from SpeedTextbox and process it
const handleSubmit = async (e) => {
  e.preventDefault();
  loading.value = true;
  error.value = '';

  // Data input must include four valid input values
  if(!captchaQuery.value || !isCaptchaValid.value) {
    // Captcha check
    error.value = 'Your captcha answer was incorrect!';
    loading.value = false;
    return;
  } else if(!usernameSpeedTextbox.value || !passwordSpeedTextbox.value || !confirmSpeedTextbox.value) {
    // Non-empty username and password check
    error.value = 'Please fill all input fields!';
    loading.value = false;
    return;
  }

  // Process data inside components
  const dataUsername = usernameSpeedTextbox.value.submit();
  const dataPassword = passwordSpeedTextbox.value.submit();
  const dataConfirm = confirmSpeedTextbox.value.submit();

  // Reset the captcha query
  captchaQuery.value.reset();

  // Check for early escape errors (regex)
  // No need to query the API for invalid entries
  // But tell users what a valid input requires
  if(!dataUsername.valid) {
    // Username check
    error.value = dataUsername.text;
    loading.value = false;
    return;
  } else if(!dataPassword.valid) {
    // Password check
    error.value = dataPassword.text;
    loading.value = false;
    return;
  } else if(dataPassword.text !== dataConfirm.text) {
    // Ensure passwords match
    error.value = 'Your passwords do not match!'
    loading.value = false;
    return;
  }

  try {
    // Submit username and password to backend via API call
    const result = await callApi('/api/signup.php', 'POST', {
      username: dataUsername.text,
      password: dataPassword.text,
      confirm: dataConfirm.text
    });

    if(result.success) {
      // Authorize the user on success
      auth.setAuth(result.user);
      is_authenticated.value = true;
      
      // Redirect to profile after a short delay
      setTimeout(() => { router.push('/profile') }, 1500);
    }
  } catch (drop) {
    error.value = drop.message || 'Signup failed! Please try again...';
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="container text-start mt-4 text-start">
    <h1>REGISTER</h1>
    
    <div v-if="is_authenticated">
      <div class="alert alert-success bg-success text-white border-0 no-round mb-4">
        <h4 class="alert-heading fw-bold mb-0">SUCCESSFULLY REGISTERED!</h4>
        <p class="mb-0">Redirecting to system profile...</p>
      </div>
      <RouterLink to="/profile" class="btn y2k-btn me-3" style="color:#0ff;border-color:#0ff">PROFILE</RouterLink>
      <RouterLink to="/logout" class="btn y2k-btn" style="color:#f0f;border-color:#f0f">LOGOUT</RouterLink>
    </div>

    <div v-else>
      <p>Ready to start sprinting?</p>

      <div v-if="error" class="alert alert-danger bg-danger text-white border-0 no-round mb-4">
        {{ error }}
      </div>

      <form @submit="handleSubmit" style="margin-left: 1.2em;" aria-label="Signup form">
        <div>
          <!-- Username input (uses SpeedTextbox component) -->
          <!-- Usernames start with a letter and are 3-16 characters (numbers and underscores allowed) -->
          <label for="username" class="y2k-note">Username</label>
          <SpeedTextbox ref="usernameSpeedTextbox"
            id="username" name="username" type="text"
            placeholder="Enter username..."
            regex="^[a-zA-Z][a-zA-Z0-9_]{2,15}$"
            error="Usernames start with a letter and are 3-16 characters (numbers and underscores allowed)"
            aria-label="Username input"
            autocomplete="username" required
          />

          <!-- Password input (uses SpeedTextbox component) -->
          <!-- Passwords must be 8-16 characters long (one uppercase, one lowercase, one number) -->
          <label for="password" class="y2k-note">Password</label>
          <SpeedTextbox ref="passwordSpeedTextbox"
            id="password" name="password" type="password"
            placeholder="Enter password..."
            regex="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%^&*()_+=\-[\]{}|;:',./?]{8,16}$"
            error="Passwords must be 8-16 characters long (one uppercase, one lowercase, one number)"
            aria-label="Password input"
            autocomplete="current-password" required
          />

          <!-- Password input (uses SpeedTextbox component) -->
          <!-- Passwords must be 8-16 characters long (one uppercase, one lowercase, one number) -->
          <label for="confirm" class="y2k-note">Confirm Password</label>
          <SpeedTextbox ref="confirmSpeedTextbox"
            id="confirm" name="confirm" type="password"
            placeholder="Confirm password..."
            regex="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%^&*()_+=\-[\]{}|;:',./?]{8,16}$"
            error="Passwords must be 8-16 characters long (one uppercase, one lowercase, one number)"
            aria-label="Confirm password input"
            autocomplete="current-password" required
          />

          <!-- Captcha display (uses CaptchaQuery component) -->
          <label for="captcha" class="y2k-note">Captcha</label>
          <CaptchaQuery ref="captchaQuery"
            id="captcha" name="captcha"
            aria-label="CAPTCHA verification panel"
            :disabled="isSubmitting"
            @verify="handleCaptchaVerify"            
          />

          <!-- Submission button -->
          <button 
            type="submit"
            class="y2k-btn w-80 mt-3"
            aria-label="Submit signup"
            :disabled="loading"
          >
            {{ loading ? 'AUTHENTICATING...' : 'REGISTER' }}
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
