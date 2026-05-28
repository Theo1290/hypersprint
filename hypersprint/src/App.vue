<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { callApi } from '@/utils/api'

const route = useRoute()
const auth = useAuthStore()

const isPlaying = ref(false)
const audioRef = ref(null)

const toggleAudio = () => {
  if (isPlaying.value) {
    audioRef.value.pause()
  } else {
    audioRef.value.play()
  }
  isPlaying.value = !isPlaying.value
}

onMounted(async () => {
  // Check if user is already logged in on Mercury
  try {
    const res = await callApi('/api/profile.php')
    if (res.user) {
      auth.setAuth(res.user)
    }
  } catch (e) {
    // Not logged in or error, keep isAuthenticated = false
    auth.clearAuth()
  }
})
</script>

<template>
  <div class="app-container py-4">
    <header class="y2k-container mb-4">
      <nav class="navbar navbar-expand-lg navbar-dark y2k-nav px-4">
        <div class="container-fluid px-0">
          <RouterLink class="navbar-brand y2k-logo" to="/">HYPERSPRINT</RouterLink>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <RouterLink class="nav-link" to="/">HOME</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/challenge">CHALLENGE</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/leaderboard">LEADERBOARD</RouterLink>
              </li>
              <li class="nav-item" v-if="auth.isAuthenticated">
                <RouterLink class="nav-link" to="/stats">STATS</RouterLink>
              </li>
              <li class="nav-item" v-if="auth.isAuthenticated">
                <RouterLink class="nav-link" to="/social-feed">FEED</RouterLink>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-person-circle y2k-user-icon"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end y2k-dropdown" aria-labelledby="navbarDropdown">
                  <template v-if="auth.isAuthenticated">
                    <li><RouterLink class="dropdown-item" to="/profile">PROFILE</RouterLink></li>
                    <li><RouterLink class="dropdown-item" to="/achievements">ACHIEVEMENTS</RouterLink></li>
                    <li><RouterLink class="dropdown-item" to="/friends">FRIENDS</RouterLink></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><RouterLink class="dropdown-item" to="/logout">LOGOUT</RouterLink></li>
                  </template>
                  <template v-else>
                    <li><RouterLink class="dropdown-item" to="/login">LOGIN</RouterLink></li>
                    <li><RouterLink class="dropdown-item" to="/signup">SIGNUP</RouterLink></li>
                  </template>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="y2k-container flex-grow-1 d-flex flex-column mb-4">
      <!-- Default Wrapper Logic -->
      <div v-if="!route.meta.customLayout" class="y2k-page-wrapper d-flex flex-column flex-grow-1">
        <RouterView />
      </div>
      <!-- Override for custom layouts (like Challenge) -->
      <RouterView v-else class="flex-grow-1" />
    </main>

    <footer class="y2k-container mt-auto">
      <div class="y2k-footer d-flex justify-content-between align-items-center py-3 px-4">
        
        <audio ref="audioRef" src="synth.mp3" loop></audio>
        
        <button @click="toggleAudio" class="btn btn-outline-info rounded-0 fw-bold border-2 btn-sm">
          <i :class="isPlaying ? 'bi bi-volume-up-fill' : 'bi bi-volume-mute-fill'"></i>
          RADIO OF THE FUTURE: {{ isPlaying ? 'ON' : 'OFF' }}
        </button>

        <p class="mb-0 y2k-footer-text text-center flex-grow-1">
          <span class="glow-text-magenta">&copy; 2026 HYPER SPRINT</span> 
          <span class="mx-3">//</span> 
          <span class="glow-text-cyan">SYSTEM STATUS: OPTIMAL</span>
          <span class="mx-3">//</span>
          <span class="glow-text-lime">V.1.0.4-BETA</span>
        </p>
      </div>
    </footer>
  </div>
</template>

<style>
/* App-wide base layout styles moved to main.css */
</style>
