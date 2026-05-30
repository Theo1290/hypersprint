<template>
  <div class="container leaderboard-page override-center">

    <div class="leaderboard-header">
      <h1 class="leaderboard-title">LEADERBOARD</h1>
      <p class="leaderboard-subtext">
        See how you rank against other sprinters.
      </p>
    </div>

    <div v-if="loading" class="state-block">
      <div class="spinner-border text-info" role="status"></div>
      <p class="mt-3">Loading leaderboard...</p>
    </div>

    <div v-else-if="error" class="alert neon-alert">
      {{ error }}
    </div>

    <div v-else-if="leaderboard.length === 0" class="alert neon-alert">
      No leaderboard entries yet — be the first to dominate ⚡
    </div>

    <div v-else class="leaderboard-card">
      <table class="leaderboard-table">
        <thead>
          <tr>
            <th>#</th>
            <th>PLAYER</th>
            <th>SCORE</th>
            <th>COMPLETED</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(row, index) in leaderboard"
            :key="row.uuid || index"
            :class="getRankClass(index)"
          >
            <td class="rank">{{ getRankLabel(index) }}</td>
            <td>{{ row.username || 'Anonymous' }}</td>
            <td>{{ row.total_score ?? 0 }}</td>
            <td>{{ row.completed_count ?? '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { callApi } from '@/utils/api'

const leaderboard = ref([])
const loading = ref(false)
const error = ref('')

const fetchLeaderboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const data = await callApi('/api/leaderboard.php')
    leaderboard.value = data.leaderboard || []
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const getRankLabel = (index) => {
  if (index === 0) return '🥇'
  if (index === 1) return '🥈'
  if (index === 2) return '🥉'
  return index + 1
}

const getRankClass = (index) => {
  if (index === 0) return 'rank-1'
  if (index === 1) return 'rank-2'
  if (index === 2) return 'rank-3'
  return ''
}

onMounted(fetchLeaderboard)
</script>

<style>
/* Local styles moved to main.css */
</style>