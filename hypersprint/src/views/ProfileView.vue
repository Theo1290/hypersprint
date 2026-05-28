<script setup>
import { ref, onMounted } from 'vue'
import { callApi } from '@/utils/api'
import AuthRequired from '@/components/AuthRequired.vue'

const user = ref(null)
const recentResults = ref([])
const loading = ref(true)
const error = ref(null)
const authError = ref(false)
const editMode = ref(false)
const editUsername = ref('')
const editEmail = ref('')
 
onMounted(async () => {
  try {
    const res = await callApi('/api/profile.php')
    user.value = res.user
    recentResults.value = res.recent_results || []
    editUsername.value = res.user.username
    editEmail.value = res.user.email
  } catch (e) {
    if (e.message.includes('Authentication required')) {
      authError.value = true
    } else {
      error.value = 'Failed to load profile.'
    }
  } finally {
    loading.value = false
  }
})
 
function toggleEdit() {
  editMode.value = !editMode.value
}
 
async function saveProfile() {
  try {
    await callApi('/api/profile.php', 'POST', {
      username: editUsername.value,
      email: editEmail.value
    })
    user.value.username = editUsername.value
    user.value.email = editEmail.value
    editMode.value = false
  } catch (e) {
    error.value = 'Failed to save profile.'
  }
}
 
function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('en-AU', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>
 
<template>
  <div class="profile-page">
 
    <div v-if="loading" class="y2k-center-content py-5">
      <p class="glow-text-cyan">LOADING PROFILE...</p>
    </div>
 
    <div v-else-if="error" class="y2k-center-content py-5">
      <p class="glow-text-magenta">{{ error }}</p>
    </div>
 
    <div v-else>
 
      <!-- Header Row -->
      <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-auto mb-3 mb-md-0">
          <div class="avatar-wrapper">
            <img
              v-if="user.profile_url"
              :src="user.profile_url"
              class="avatar-img"
              alt="Profile picture"
            />
            <div v-else class="avatar-placeholder y2k-center-content">
              <i class="bi bi-person-fill"></i>
            </div>
          </div>
        </div>
        <div class="col">
          <h1 class="mb-0">{{ user.username }}</h1>
          <p class="profile-sub mb-1">LVL {{ user.level?.toFixed(1) ?? '—' }} &nbsp;·&nbsp; JOINED {{ formatDate(user.joined) }}</p>
          <p class="profile-email">{{ user.email }}</p>
        </div>
        <div class="col-12 col-md-auto mt-3 mt-md-0">
          <button class="y2k-btn y2k-btn-cyan" @click="toggleEdit">
            {{ editMode ? 'CANCEL' : 'EDIT PROFILE' }}
          </button>
        </div>
      </div>
 
      <!-- Edit Form -->
      <div v-if="editMode" class="y2k-panel mb-4">
        <h2 class="panel-heading mb-3">EDIT PROFILE</h2>
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="y2k-label">USERNAME</label>
            <input v-model="editUsername" class="y2k-input w-100" type="text" maxlength="32" />
          </div>
          <div class="col-12 col-md-6">
            <label class="y2k-label">EMAIL</label>
            <input v-model="editEmail" class="y2k-input w-100" type="email" />
          </div>
          <div class="col-12">
            <button class="y2k-btn" @click="saveProfile">SAVE CHANGES</button>
          </div>
        </div>
      </div>
 
      <!-- Stats Row -->
      <h2 class="panel-heading mb-3">STATS</h2>
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-value glow-text-cyan">{{ user.highest_wpm?.toFixed(1) ?? '—' }}</div>
            <div class="stat-label">BEST WPM</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-value glow-text-lime">{{ user.average_wpm?.toFixed(1) ?? '—' }}</div>
            <div class="stat-label">AVG WPM</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-value glow-text-magenta">{{ user.average_accuracy?.toFixed(1) ?? '—' }}%</div>
            <div class="stat-label">AVG ACCURACY</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card">
            <div class="stat-value glow-text-cyan">{{ user.challenge_count ?? '—' }}</div>
            <div class="stat-label">CHALLENGES</div>
          </div>
        </div>
      </div>
 
      <!-- Recent Results -->
      <h2 class="panel-heading mb-3">RECENT RESULTS</h2>
      <div v-if="recentResults.length === 0" class="y2k-panel text-center py-3">
        <p class="mb-0" style="color: var(--y2k-text)">NO RESULTS YET. START A CHALLENGE!</p>
      </div>
      <div v-else class="y2k-panel p-0">
        <table class="y2k-table w-100">
          <thead>
            <tr>
              <th>CHALLENGE</th>
              <th>WPM</th>
              <th>ACCURACY</th>
              <th>DURATION</th>
              <th>DATE</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="result in recentResults" :key="result.result_id">
              <td>{{ result.challenge_title ?? result.challenge_id }}</td>
              <td class="glow-text-cyan">{{ result.wpm?.toFixed(1) }}</td>
              <td class="glow-text-lime">{{ result.accuracy?.toFixed(1) }}%</td>
              <td>{{ result.duration }}s</td>
              <td>{{ formatDate(result.completed) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
 
    </div>
  </div>
</template>
 
<style scoped>
/* Local styles moved to main.css */
</style>
