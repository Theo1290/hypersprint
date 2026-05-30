<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { callApi } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'
import SpeedTextbox from '@/components/input/SpeedTextbox.vue'
import CaptchaQuery from '@/components/input/CaptchaQuery.vue'

const router = useRouter();
const auth = useAuthStore();

// Interface state
const is_authenticated = ref(false);
const loading = ref(false);
const error = ref('');

// Captcha mechanic
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

  // Data input must include all four input values
  if(!usernameSpeedTextbox.value || !passwordSpeedTextbox.value || !confirmSpeedTextbox.value || !isCaptchaValid.value) {
    error.value = 'Please fill all input fields!';
    loading.value = false;
    return;
  }

  // Process data inside components
  const dataUsername = usernameSpeedTextbox.value.submit();
  const dataPassword = passwordSpeedTextbox.value.submit();
  const dataConfirm = confirmSpeedTextbox.value.submit();

  // Check for early escape errors (regex)
  // No need to query the API for invalid entries
  // But tell users what a valid input requires
  if(!dataUsername.valid) {
    error.value = dataUsername.text;
    loading.value = false;
    return;
  } else if(!dataPassword.valid) {
    error.value = dataPassword.text;
    loading.value = false;
    return;
  }

  // Ensure passwords match
  if(dataPassword.text !== dataConfirm.text) {
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

      <form @submit="handleSubmit" style="margin-left: 1.2em;">
        <div>
          <!-- Username input (uses SpeedTextbox component) -->
          <label for="username" class="y2k-note">Username</label>
          <SpeedTextbox ref="usernameSpeedTextbox"
            id="username" name="username" type="text"
            placeholder="Enter username..."
            regex="^[a-zA-Z][a-zA-Z0-9_]{3,16}$"
            error="Usernames must be 3-16 characters (letters, numbers, underscores)"
            autocomplete="username" required
          />

          <!-- Password input (uses SpeedTextbox component) -->
          <label for="password" class="y2k-note">Password</label>
          <SpeedTextbox ref="passwordSpeedTextbox"
            id="password" name="password" type="password"
            placeholder="Enter password..."
            regex="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%^&*()_+=\-[\]{}|;:',./?]{8,16}$"
            error="Passwords must be 8-16 characters (one uppercase, one lowercase, one number)"
            autocomplete="current-password" required
          />

          <!-- Password input (uses SpeedTextbox component) -->
          <label for="confirm" class="y2k-note">Confirm Password</label>
          <SpeedTextbox ref="confirmSpeedTextbox"
            id="confirm" name="confirm" type="password"
            placeholder="Confirm password..."
            regex="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%^&*()_+=\-[\]{}|;:',./?]{8,16}$"
            error="Passwords must be 8-16 characters (one uppercase, one lowercase, one number)"
            autocomplete="current-password" required
          />

          <!-- Captcha display (uses CaptchaQuery component) -->
          <label for="captcha" class="y2k-note">Captcha</label>
          <CaptchaQuery ref="captchaQuery"
            id="captcha" name="captcha"
            :disabled="isSubmitting"
            @verify="handleCaptchaVerify"
          />

          <!-- Submission button -->
          <button 
            type="submit" 
            class="y2k-btn w-80 mt-3" 
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
