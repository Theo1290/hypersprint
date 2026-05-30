<script setup>
import { ref, onMounted, computed } from 'vue'
import { callApi } from '../utils/api'

const stats = ref(null)
const history = ref([])
const error = ref('')

const levelInfo = computed(() => {
  if (!stats.value) return { level: 1, progress: 0, xp: 0, nextXp: 100 }
  
  const xp = stats.value.total_experience || 0
  const currentLevel = Math.floor(Math.sqrt(xp / 100)) + 1
  const currentLevelXp = Math.pow(currentLevel - 1, 2) * 100
  const nextLevelXp = Math.pow(currentLevel, 2) * 100
  const progress = ((xp - currentLevelXp) / (nextLevelXp - currentLevelXp)) * 100

  return {
    level: currentLevel,
    progress: Math.min(Math.max(progress, 0), 100),
    xp: xp,
    nextXp: nextLevelXp
  }
})

const loadStats = async () => {
  try {
    const data = await callApi('/api/stats.php')
    stats.value = data.stats
    history.value = data.history || []
  } catch (e) {
    error.value = e.message || 'Failed to load stats'
  }
}

onMounted(loadStats)
</script>

<template>
  <div class="container text-start">
    <h1 class="glow-text-cyan mb-4">PERFORMANCE STATS</h1>

    <div v-if="error" class="alert alert-danger no-round">{{ error }}</div>

    <div v-if="stats" class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="y2k-card p-4 h-100 text-center">
          <h2 class="glow-text-lime mb-2">{{ stats.username }}</h2>
          <h3 class="text-white mb-4">Level {{ levelInfo.level }}</h3>
          
          <div class="progress no-round bg-dark mb-2" style="height: 25px; border: 1px solid var(--y2k-cyan);">
            <div class="progress-bar bg-info" :style="{ width: levelInfo.progress + '%' }"></div>
          </div>
          <p class="fw-bold mt-2" style="color: rgba(255,255,255,0.7);">{{ levelInfo.xp }} / {{ levelInfo.nextXp }} XP</p>
        </div>
      </div>

      <div class="col-md-8">
        <div class="y2k-card p-4 h-100">
          <div class="row text-center g-3">
            <div class="col-6 col-md-4">
              <div class="y2k-stat p-3">
                <div class="small">Highest WPM</div>
                <div class="fs-2 fw-bold glow-text-cyan">{{ stats.highest_wpm }}</div>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="y2k-stat p-3">
                <div class="small">Average WPM</div>
                <div class="fs-2 fw-bold glow-text-magenta">{{ stats.average_wpm }}</div>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="y2k-stat p-3">
                <div class="small">Accuracy</div>
                <div class="fs-2 fw-bold glow-text-lime">{{ stats.average_accuracy }}%</div>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="y2k-stat p-3">
                <div class="small">Total Sprints</div>
                <div class="fs-2 fw-bold text-white">{{ stats.challenge_count }}</div>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="y2k-stat p-3">
                <div class="small">Global Rank</div>
                <div class="fs-2 fw-bold text-warning">#{{ stats.rank_global }}</div>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="y2k-stat p-3">
                <div class="small">Achievements</div>
                <div class="fs-2 fw-bold text-white">{{ stats.achievements_count }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <h2 v-if="history.length" class="glow-text-magenta mb-3">SPRINT HISTORY</h2>
    <div v-if="history.length" class="table-responsive y2k-card p-3">
      <table class="table table-dark table-hover mb-0">
        <thead>
          <tr>
            <th class="text-info">Challenge</th>
            <th class="text-info">WPM</th>
            <th class="text-info">Accuracy</th>
            <th class="text-info">Duration</th>
            <th class="text-info">Date</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="res in history" :key="res.result_id">
            <td>{{ res.challenge_title }}</td>
            <td class="glow-text-cyan fw-bold">{{ res.wpm }}</td>
            <td class="glow-text-lime">{{ res.accuracy }}%</td>
            <td>{{ res.duration }}s</td>
            <td>{{ res.completed }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.no-round { border-radius: 0 !important; }
.table { background: transparent; }
.table td, .table th { border-color: rgba(255,255,255,0.1); background: transparent; color: #fff; }
</style>
