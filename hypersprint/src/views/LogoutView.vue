<script setup>
import { ref } from 'vue'

const is_authenticated = ref(false);
const user_stats = ref({ highest_wpm: 120, average_wpm: 85, challenge_count: 42 });
</script>

<template>
  <div class="container text-start mt-4">
    <div class="text-center mb-5">
      <h1 class="glow-text-cyan mb-3">LOGOUT</h1>
      <div v-if="is_authenticated">
        <p class="mb-4">You're already logged out!</p>
        <RouterLink to="/login" class="btn y2k-btn me-3" style="color:#0ff;border-color:#0ff">LOGIN</RouterLink>
        <RouterLink to="/home" class="btn y2k-btn" style="color:#f0f;border-color:#f0f">HOME</RouterLink>
      </div>
      <div v-else>
        <p class="mb-4">Logging out? Have a great day!</p>
        <RouterLink to="/profile" class="btn y2k-btn me-3" style="color:#0ff;border-color:#0ff">LOGOUT</RouterLink>
        <RouterLink to="/achievements" class="btn y2k-btn" style="color:#f0f;border-color:#f0f">ACHIEVEMENTS</RouterLink>
        <div class="d-flex justify-content-center gap-4 flex-wrap">
          <div class="y2k-stat">Best WPM <br><b class="glow-text-cyan fs-1">{{ user_stats.highest_wpm }}</b></div>
          <div class="y2k-stat">Avg WPM <br><b class="glow-text-magenta fs-1">{{ user_stats.average_wpm }}</b></div>
          <div class="y2k-stat">Sprints <br><b class="glow-text-lime fs-1">{{ user_stats.challenge_count }}</b></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.y2k-btn { border-radius: 0; border-width: 2px; font-weight: bold; }
.y2k-btn:hover { background: rgba(255,255,255,0.1); }
.y2k-card { background: var(--y2k-glass); border: 2px solid var(--y2k-cyan); }
.y2k-stat { background: var(--y2k-glass); border: 1px solid var(--y2k-magenta); padding: 1rem 2rem; }
</style>
