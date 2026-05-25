<script setup>
import { ref, onMounted } from 'vue'
import { callApi } from '../utils/api'

const is_authenticated = ref(false)
const user_stats = ref({ highest_wpm: 0, average_wpm: 0, challenge_count: 0 })
const featured_challenges = ref([])
const leaderboard = ref([])
const recent_results = ref([])

const loadHomeData = async () => {
  try {
    const data = await callApi('/api/home.php')
    is_authenticated.value = data.is_authenticated
    if (data.user_stats) user_stats.value = data.user_stats
    if (data.featured_challenges) featured_challenges.value = data.featured_challenges
    if (data.leaderboard_preview) leaderboard.value = data.leaderboard_preview
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
      <h1 class="glow-text-cyan mb-3">HYPER SPRINT</h1>
      
      <div v-if="!is_authenticated">
        <p class="mb-4">Test your speed. Climb the ranks. Join the grid.</p>
        <RouterLink to="/login" class="btn y2k-btn me-3" style="color:#0ff;border-color:#0ff">LOGIN</RouterLink>
        <RouterLink to="/signup" class="btn y2k-btn" style="color:#f0f;border-color:#f0f">SIGN UP</RouterLink>
      </div>
      
      <div v-else class="d-flex justify-content-center gap-4 flex-wrap">
        <div class="y2k-stat">Best WPM <br><b class="glow-text-cyan fs-1">{{ user_stats.highest_wpm }}</b></div>
        <div class="y2k-stat">Avg WPM <br><b class="glow-text-magenta fs-1">{{ user_stats.average_wpm }}</b></div>
        <div class="y2k-stat">Sprints <br><b class="glow-text-lime fs-1">{{ user_stats.challenge_count }}</b></div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <h2 class="mb-3 glow-text-magenta">FEATURED SPRINTS</h2>
        <div class="row g-3">
          <div class="col-md-6" v-for="c in featured_challenges" :key="c.title">
            <div class="y2k-card p-3 d-flex flex-column h-100">
              <h3 class="glow-text-cyan fs-4">{{ c.title }}</h3>
              <p class="mb-3">Lvl: {{ c.level }} | {{ c.topic }} <br>Mode: {{ c.gamemode }}</p>
              <RouterLink to="/challenge" class="btn y2k-btn mt-auto" style="color:#0f0;border-color:#0f0" :aria-label="'Start ' + c.title">START</RouterLink>
            </div>
          </div>
        </div>

        <h2 class="mt-5 mb-3 glow-text-lime">RECENT RESULTS</h2>
        <div class="y2k-card p-3">
          <div v-for="(r, index) in recent_results" :key="index" class="d-flex justify-content-between border-bottom mb-2 pb-2">
            <span><b>{{ r.username }}</b> on <em>{{ r.challenge_title }}</em></span>
            <span><span class="glow-text-cyan">{{ r.wpm }} WPM</span></span>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <h2 class="mb-3 glow-text-cyan">LEADERBOARD</h2>
        <div class="y2k-card p-3">
          <ol class="ps-3 mb-0">
            <li v-for="l in leaderboard" :key="l.username" class="mb-3">
              <b>{{ l.username }}</b> <span class="badge bg-secondary">Lvl {{ l.level }}</span>
              <div class="glow-text-magenta">{{ l.wpm }} WPM</div>
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.y2k-btn { border-radius: 0; border-width: 2px; font-weight: bold; }
.y2k-btn:hover { background: rgba(255,255,255,0.1); }
.y2k-card { background: var(--y2k-glass); border: 2px solid rgba(0, 255, 255, 0.25); }
.y2k-stat { background: var(--y2k-glass); border: 2px solid rgba(0, 255, 255, 0.25); padding: 1rem 2rem; }
</style>
