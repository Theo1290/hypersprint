<script setup>
import { ref } from 'vue'

const is_authenticated = ref(false)
const user_stats = ref({ highest_wpm: 120, average_wpm: 85, challenge_count: 42 })
const featured_challenges = ref([
  { title: 'test title 1', level: 'Hard', topic: 'games', mode: 'gamemode 1' },
  { title: 'test title 2', level: 'Easy', topic: 'music lyrics', mode: 'gamemode 2' }
])
const leaderboard = ref([
  { username: 'testuser1', wpm: 155, lvl: 100 },
  { username: 'testuser2', wpm: 148, lvl: 1 }
])
const recent_results = ref([
  { user: 'testuser3', wpm: 95, title: 'challenge24385', time: '2 mins' }
])
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
              <p class="mb-3">Lvl: {{ c.level }} | {{ c.topic }} <br>Mode: {{ c.mode }}</p>
              <RouterLink to="/challenge" class="btn y2k-btn mt-auto" style="color:#0f0;border-color:#0f0" :aria-label="'Start ' + c.title">START</RouterLink>
            </div>
          </div>
        </div>

        <h2 class="mt-5 mb-3 glow-text-lime">RECENT RESULTS</h2>
        <div class="y2k-card p-3">
          <div v-for="r in recent_results" :key="r.user" class="d-flex justify-content-between border-bottom mb-2 pb-2">
            <span><b>{{ r.user }}</b> on <em>{{ r.title }}</em></span>
            <span><span class="glow-text-cyan">{{ r.wpm }} WPM</span> ({{ r.time }})</span>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <h2 class="mb-3 glow-text-cyan">LEADERBOARD</h2>
        <div class="y2k-card p-3">
          <ol class="ps-3 mb-0">
            <li v-for="l in leaderboard" :key="l.username" class="mb-3">
              <b>{{ l.username }}</b> <span class="badge bg-secondary">Lvl {{ l.lvl }}</span>
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
.y2k-card { background: var(--y2k-glass); border: 2px solid var(--y2k-cyan); }
.y2k-stat { background: var(--y2k-glass); border: 1px solid var(--y2k-magenta); padding: 1rem 2rem; }
</style>
