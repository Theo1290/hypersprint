<script setup>
import { ref, onMounted } from 'vue'
import { callApi } from '../utils/api'

const is_authenticated = ref(false)
const user_stats = ref({ highest_wpm: 0, average_wpm: 0, challenge_count: 0 })
const recent_results = ref([])

const loadHomeData = async () => {
  try {
    const data = await callApi('/api/home.php')
    is_authenticated.value = data.is_authenticated
    if (data.user_stats) user_stats.value = data.user_stats
    if (data.recent_results) recent_results.value = data.recent_results
  } catch (e) {
    console.error(e)
  }
}

onMounted(loadHomeData)
</script>

<template>
  <div class="container text-start">
    <div class="text-center mb-5">
      <h1 class="glow-text-cyan mb-3">WELCOME TO HYPERSPRINT</h1>
      
      <div v-if="!is_authenticated">
        <p class="mb-4">Test your speed. Climb the ranks. Join the grid.</p>
        <RouterLink to="/login" class="y2k-btn y2k-btn-cyan me-3">LOGIN</RouterLink>
        <RouterLink to="/signup" class="y2k-btn y2k-btn-magenta">SIGN UP</RouterLink>
      </div>
      
      <div v-else class="d-flex justify-content-center gap-4 flex-wrap">
        <div class="y2k-stat">Best WPM <br><b class="glow-text-cyan fs-1">{{ user_stats.highest_wpm }}</b></div>
        <div class="y2k-stat">Avg WPM <br><b class="glow-text-magenta fs-1">{{ user_stats.average_wpm }}</b></div>
        <div class="y2k-stat">Sprints <br><b class="glow-text-lime fs-1">{{ user_stats.challenge_count }}</b></div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Left Column: Singleplayer and Multiplayer Boxes -->
      <div class="col-lg-8">
        <h2 class="mb-3 glow-text-lime">FEATURED CHALLENGES</h2>
        <div class="row g-3">
          <!-- Singleplayer Card -->
          <div class="col-md-6">
            <div class="y2k-card y2k-card-cyan p-4 d-flex flex-column h-100">
              <h3 class="glow-text-cyan fs-3 mb-2"><i class="bi bi-person-fill me-2"></i>SINGLEPLAYER</h3>
              <p class="mb-4 text-white-50">Practice your typing speed, complete custom sprints, earn experience points (XP), and level up your grid rating.</p>
              <RouterLink to="/singleplayer" class="y2k-btn y2k-btn-cyan mt-auto">START SPRINT</RouterLink>
            </div>
          </div>
          
          <!-- Multiplayer Card -->
          <div class="col-md-6">
            <div class="y2k-card y2k-card-magenta p-4 d-flex flex-column h-100">
              <h3 class="glow-text-magenta fs-3 mb-2"><i class="bi bi-people-fill me-2"></i>MULTIPLAYER</h3>
              <p class="mb-4 text-white-50">Matchmake with other players online. Test your limits in a live 1v1 battle on the battlegrid.</p>
              <template v-if="is_authenticated">
                <RouterLink to="/multiplayer" class="y2k-btn y2k-btn-magenta mt-auto">JOIN GRID</RouterLink>
              </template>
              <template v-else>
                <RouterLink to="/login" class="y2k-btn y2k-btn-magenta mt-auto">LOGIN TO PLAY</RouterLink>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Recent Activity (formerly Leaderboard column) -->
      <div class="col-lg-4">
        <h2 class="mb-3 glow-text-cyan">RECENT ACTIVITY</h2>
        <div class="y2k-card p-3" style="max-height: 400px; overflow-y: auto;">
          <div v-if="recent_results.length === 0" class="text-center py-5 text-muted">
            No recent activity. Be the first to sprint!
          </div>
          <div v-else>
            <div v-for="(r, index) in recent_results" :key="index" class="d-flex justify-content-between border-bottom border-secondary mb-2 pb-2" style="border-bottom-style: dashed !important;">
              <span>
                <b class="text-info">{{ r.username }}</b>
                <span class="text-white-50"> on </span>
                <em class="glow-text-lime">{{ r.challenge_title || 'Sprint' }}</em>
              </span>
              <span class="glow-text-magenta fw-bold">{{ r.wpm }} WPM</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Redundant styles removed, now using main.css */
</style>
