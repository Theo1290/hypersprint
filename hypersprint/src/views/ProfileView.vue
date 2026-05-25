<script setup>
import { ref, onMounted } from 'vue'
import { callApi } from '@/utils/api'

const user = ref(null)
const recentResults = ref([])
const loading = ref(true)
const error = ref(null)
const editMode = ref(false)
const editUsername = ref('')
const editEmail = ref('')
 
onMounted(async () => {
  try {
    const res = await callApi('/cos30043/s103982457/Project/api/profile.php')
    user.value = res.user
    recentResults.value = res.recent_results || []
    editUsername.value = res.user.username
    editEmail.value = res.user.email
  } catch (e) {
    error.value = 'Failed to load profile.'
  } finally {
    loading.value = false
  }
})
 
function toggleEdit() {
  editMode.value = !editMode.value
}
 
async function saveProfile() {
  try {
    await callApi('/cos30043/s103982457/Project/api/profile.php', 'POST', {
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
          <button class="btn-y2k" @click="toggleEdit">
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
            <button class="btn-y2k btn-y2k-lime" @click="saveProfile">SAVE CHANGES</button>
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
.profile-page {
  width: 100%;
}
 
/* Avatar */
.avatar-wrapper {
  width: 100px;
  height: 100px;
}
.avatar-img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border: 3px solid var(--y2k-cyan);
  box-shadow: 0 0 12px var(--y2k-cyan);
}
.avatar-placeholder {
  width: 100px;
  height: 100px;
  border: 3px solid var(--y2k-cyan);
  box-shadow: 0 0 12px var(--y2k-cyan);
  background: var(--y2k-glass);
  font-size: 3rem;
  color: var(--y2k-cyan);
}
 
/* Profile text */
.profile-sub {
  color: var(--y2k-text);
  font-size: 1.1rem;
  letter-spacing: 1px;
}
.profile-email {
  color: var(--y2k-cyan);
  font-size: 1rem;
}
 
/* Panel */
.y2k-panel {
  background: var(--y2k-glass);
  border: 2px solid rgba(0, 255, 255, 0.25);
  padding: 1.5rem;
}
.panel-heading {
  color: var(--y2k-lime);
  font-family: 'VT323', monospace;
  font-size: 1.6rem;
  letter-spacing: 2px;
  text-shadow: 2px 2px 0 var(--y2k-blue);
  text-transform: uppercase;
  margin-bottom: 0;
}
 
/* Stat cards */
.stat-card {
  background: var(--y2k-glass);
  border: 2px solid rgba(0, 255, 255, 0.2);
  padding: 1.2rem 1rem;
  text-align: center;
}
.stat-value {
  font-size: 2.4rem;
  font-family: 'VT323', monospace;
  line-height: 1;
}
.stat-label {
  font-size: 0.85rem;
  color: var(--y2k-text);
  letter-spacing: 1px;
  margin-top: 0.25rem;
}
 
/* Table */
.y2k-table {
  border-collapse: collapse;
  font-family: 'VT323', monospace;
  font-size: 1.2rem;
}
.y2k-table th {
  background: rgba(0, 255, 255, 0.1);
  color: var(--y2k-cyan);
  padding: 0.6rem 1rem;
  text-align: left;
  letter-spacing: 1px;
  border-bottom: 2px solid rgba(0, 255, 255, 0.3);
}
.y2k-table td {
  padding: 0.5rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  color: var(--y2k-text);
}
.y2k-table tbody tr:hover {
  background: rgba(0, 255, 255, 0.05);
}
 
/* Inputs */
.y2k-label {
  display: block;
  color: var(--y2k-cyan);
  font-size: 0.9rem;
  letter-spacing: 1px;
  margin-bottom: 0.3rem;
}
.y2k-input {
  background: rgba(0, 0, 0, 0.4);
  border: 2px solid var(--y2k-cyan);
  color: var(--y2k-text);
  font-family: 'VT323', monospace;
  font-size: 1.3rem;
  padding: 0.4rem 0.8rem;
  outline: none;
}
.y2k-input:focus {
  border-color: var(--y2k-magenta);
  box-shadow: 0 0 8px var(--y2k-magenta);
}
 
/* Buttons */
.btn-y2k {
  background: transparent;
  border: 2px solid var(--y2k-cyan);
  color: var(--y2k-cyan);
  font-family: 'VT323', monospace;
  font-size: 1.2rem;
  letter-spacing: 1px;
  padding: 0.4rem 1.2rem;
  cursor: pointer;
  text-transform: uppercase;
  transition: 0.2s;
}
.btn-y2k:hover {
  background: var(--y2k-cyan);
  color: #000;
  box-shadow: 0 0 10px var(--y2k-cyan);
}
.btn-y2k-lime {
  border-color: var(--y2k-lime);
  color: var(--y2k-lime);
}
.btn-y2k-lime:hover {
  background: var(--y2k-lime);
  color: #000;
  box-shadow: 0 0 10px var(--y2k-lime);
}
 
/* Glow helpers */
.glow-text-cyan  { color: var(--y2k-cyan);    text-shadow: 0 0 6px var(--y2k-cyan); }
.glow-text-lime  { color: var(--y2k-lime);    text-shadow: 0 0 6px var(--y2k-lime); }
.glow-text-magenta { color: var(--y2k-magenta); text-shadow: 0 0 6px var(--y2k-magenta); }
</style>
