<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { callApi } from '@/utils/api'
import { useAuthStore } from '@/stores/auth'
import SpeedTextbox from '@/components/input/SpeedTextbox.vue'

const router = useRouter()
const auth = useAuthStore()

// UI state
const is_authenticated = ref(false)
const loading = ref(false)
const error = ref('')

// Reference to the child SpeedTextbox component
const usernameSpeedTextbox = ref(null)
const passwordSpeedTextbox = ref(null)

// Retrieve data from SpeedTextbox and process it
const handleSubmit = async (e) => {
  e.preventDefault()
  if (!usernameSpeedTextbox.value || !passwordSpeedTextbox.value) return

  error.value = ''
  loading.value = true

  const dataUsername = usernameSpeedTextbox.value.submit()
  const dataPassword = passwordSpeedTextbox.value.submit()

  try {
    const res = await callApi('/api/login.php', 'POST', {
      username: dataUsername.text,
      password: dataPassword.text
    })

    if (res.success) {
      auth.setAuth(res.user)
      is_authenticated.value = true
      // Optional: Redirect to profile after a short delay
      setTimeout(() => {
        router.push('/profile')
      }, 1500)
    }
  } catch (err) {
    error.value = err.message || 'Login failed. Please try again.'
  } finally {
    loading.value = false
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
          
          <!-- Submission button -->
          <button 
            type="submit" 
            class="y2k-btn w-80 mt-3" 
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
