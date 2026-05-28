<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { callApi } from '@/utils/api'

const router = useRouter()
const auth = useAuthStore()

onMounted(async () => {
  try {
    await callApi('/api/logout.php', 'POST', {})
  } catch (e) {
    console.error('Logout API failed', e)
  } finally {
    auth.clearAuth()
    setTimeout(() => {
      router.push('/')
    }, 2000)
  }
})
</script>

<template>
  <div class="container text-start mt-4">
    <div class="text-center mb-5">
      <h1 class="glow-text-cyan mb-3">LOGOUT</h1>
      <p class="mb-4">Logging out? System shutdown initiated... Have a great day!</p>
      <div class="spinner-border text-info" role="status"></div>
    </div>
  </div>
</template>

<style scoped>
/* Redundant styles removed, now using main.css */
</style>
