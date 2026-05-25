import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const isAuthenticated = ref(false)
  const user = ref(null)

  function setAuth(userData) {
    isAuthenticated.value = true
    user.value = userData
  }

  function clearAuth() {
    isAuthenticated.value = false
    user.value = null
  }

  return { isAuthenticated, user, setAuth, clearAuth }
})
