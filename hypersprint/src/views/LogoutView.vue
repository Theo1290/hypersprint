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
.y2k-btn { border-radius: 0; border-width: 2px; font-weight: bold; }
.y2k-btn:hover { background: rgba(255,255,255,0.1); }
.y2k-card { background: var(--y2k-glass); border: 2px solid var(--y2k-cyan); }
.y2k-stat { background: var(--y2k-glass); border: 1px solid var(--y2k-magenta); padding: 1rem 2rem; }
</style>
