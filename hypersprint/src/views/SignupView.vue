<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { callApi } from '@/utils/api'
import SpeedTextbox from '@/components/input/SpeedTextbox.vue'
import CaptchaQuery from '@/components/input/CaptchaQuery.vue'

const router = useRouter()

// Interface state
const is_authenticated = ref(false)
const loading = ref(false)
const error = ref('')

// Captcha mechanic
const isCaptchaValid = ref(false)
const isSubmitting = ref(false)

// Keep track of the validation status
const handleCaptchaVerify = (isValid) => {
  isCaptchaValid.value = isValid
}

// Reference to the child SpeedTextbox component
const usernameSpeedTextbox = ref(null)
const passwordSpeedTextbox = ref(null)
const confirmSpeedTextbox = ref(null)

// Retrieve data from SpeedTextbox and process it
const handleSubmit = async (e) => {
  e.preventDefault()
  if (!usernameSpeedTextbox.value || !passwordSpeedTextbox.value || !confirmSpeedTextbox.value || !isCaptchaValid.value) {
    error.value = 'Please fill all input fields!'
    return
  }
  
  const dataUsername = usernameSpeedTextbox.value.submit()
  const dataPassword = passwordSpeedTextbox.value.submit()
  const dataConfirm = confirmSpeedTextbox.value.submit()

  if (dataPassword.text !== dataConfirm.text) {
    error.value = 'Your passwords do not match!'
    return
  }

  error.value = ''
  loading.value = true

  try {
    const res = await callApi('/api/login.php', 'POST', {
      username: dataUsername.text,
      password: dataPassword.text,
      confirm: dataConfirm.text
    })

    if (res.success) {
      is_authenticated.value = true
      // Redirect to profile after a short delay
      setTimeout(() => {
        router.push('/profile')
      }, 1500)
    }
  } catch (err) {
    error.value = err.message || 'Login failed! Please try again.'
  } finally {
    loading.value = false
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
            autocomplete="username" required
          />

          <!-- Password input (uses SpeedTextbox component) -->
          <label for="password" class="y2k-note">Password</label>
          <SpeedTextbox ref="passwordSpeedTextbox"
            id="password" name="password" type="password"
            placeholder="Enter password..."
            autocomplete="current-password" required
          />

          <!-- Password input (uses SpeedTextbox component) -->
          <label for="confirm" class="y2k-note">Confirm Password</label>
          <SpeedTextbox ref="confirmSpeedTextbox"
            id="confirm" name="confirm" type="password"
            placeholder="Confirm password..."
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
            class="btn y2k-btn btn-primary mt-3 @click=verifyCaptcha"
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
.y2k-btn { border-radius: 0; border-width: 2px; font-weight: bold; }
.y2k-btn:hover { background: rgba(255,255,255,0.1); }
.y2k-btn.btn-primary { width: 80%; }
.y2k-card { background: var(--y2k-glass); border: 2px solid var(--y2k-cyan); }
.y2k-stat { background: var(--y2k-glass); border: 1px solid var(--y2k-magenta); padding: 1rem 2rem; }
.y2k-note { background: var(--y2k-glass); line-height: 1.5rem; font-size: medium; }
</style>
