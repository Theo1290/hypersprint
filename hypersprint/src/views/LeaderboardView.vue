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

const leaderboard = ref([])
const loading = ref(false)
const error = ref('')

const fetchLeaderboard = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await fetch('/api/leaderboard.php', {
      method: 'GET',
      credentials: 'include',
    })

    const text = await response.text()
    let data

    try {
      data = text ? JSON.parse(text) : {}
    } catch {
      throw new Error('Invalid API response')
    }

    if (!response.ok) {
      throw new Error(data.error || 'Unable to load leaderboard')
    }

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

.leaderboard-table,
.leaderboard-table * {
  background: transparent !important;
  color: white !important;
}

.leaderboard-card {
  width: 100%;
  border: 2px solid var(--y2k-cyan);
  box-shadow: 0 0 20px var(--y2k-cyan);
  background: rgba(10, 0, 40, 0.95);
  backdrop-filter: blur(6px);
  overflow: hidden;
}

.leaderboard-table {
  width: 100%;
  border-collapse: collapse;
}

.leaderboard-table thead {
  background: rgba(0,255,255,0.1) !important;
}

.leaderboard-table th {
  padding: 16px;
  text-align: left;
  color: var(--y2k-cyan) !important;
  border-bottom: 2px solid var(--y2k-cyan);
  letter-spacing: 2px;
}

.leaderboard-table td {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.leaderboard-table tbody tr:nth-child(even) td {
  background: rgba(255,255,255,0.04) !important;
}

.leaderboard-table tbody tr:hover td {
  background: rgba(0,255,255,0.1) !important;
}

.rank {
  font-weight: bold;
}

.rank-1 td {
  color: #FFD700 !important;
  text-shadow: 0 0 6px #FFD700;
}

.rank-2 td {
  color: #C0C0C0 !important;
  text-shadow: 0 0 6px #C0C0C0;
}

.rank-3 td {
  color: #CD7F32 !important;
  text-shadow: 0 0 6px #CD7F32;
}

</style>