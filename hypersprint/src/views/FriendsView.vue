<script setup>
import { ref, onMounted } from 'vue'
import { callApi } from '@/utils/api'
 
const activeTab = ref('friends')
const friends = ref([])
const friendRequests = ref([])
const sentRequests = ref([])
const searchQuery = ref('')
const searchResults = ref([])
const loading = ref(true)
const searching = ref(false)
const error = ref(null)
 
onMounted(async () => {
  try {
    const res = await callApi('/api/friends.php')
    friends.value = res.friends || []
    friendRequests.value = res.friend_requests || []
    sentRequests.value = res.sent_requests || []
  } catch (e) {
    error.value = 'Failed to load friends.'
  } finally {
    loading.value = false
  }
})
 
async function searchUsers() {
  if (!searchQuery.value.trim()) return
  searching.value = true
  try {
    const res = await callApi(`/api/friends.php?search=${encodeURIComponent(searchQuery.value)}`)
    searchResults.value = res.results || []
  } catch (e) {
    error.value = 'Search failed.'
  } finally {
    searching.value = false
  }
}
 
async function sendRequest(userId) {
  await callApi('/api/friends.php', 'POST', { action: 'send_request', to_user_id: userId })
  const user = searchResults.value.find(u => u.user_id === userId)
  if (user) user.request_sent = true
}
 
async function acceptRequest(userId) {
  await callApi('/api/friends.php', 'POST', { action: 'accept', from_user_id: userId })
  const req = friendRequests.value.find(r => r.from_user_id === userId)
  if (req) {
    friendRequests.value = friendRequests.value.filter(r => r.from_user_id !== userId)
    friends.value.push({ user_id: userId, username: req.username, profile_url: req.profile_url, level: null, highest_wpm: null })
  }
}
 
async function declineRequest(userId) {
  await callApi('/api/friends.php', 'POST', { action: 'decline', from_user_id: userId })
  friendRequests.value = friendRequests.value.filter(r => r.from_user_id !== userId)
}
 
async function removeFriend(userId) {
  await callApi('/api/friends.php', 'POST', { action: 'remove', friend_id: userId })
  friends.value = friends.value.filter(f => f.user_id !== userId)
}
 
function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('en-AU', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>
 
<template>
  <div class="friends-page">
    <h1 class="mb-4">FRIENDS</h1>
 
    <div v-if="loading" class="y2k-center-content py-5">
      <p class="glow-text-cyan">LOADING...</p>
    </div>
 
    <div v-else>
 
      <!-- Tabs -->
      <div class="y2k-tabs mb-4">
        <button
          class="y2k-tab"
          :class="{ active: activeTab === 'friends' }"
          @click="activeTab = 'friends'"
        >
          FRIENDS
          <span v-if="friends.length" class="tab-count">{{ friends.length }}</span>
        </button>
        <button
          class="y2k-tab"
          :class="{ active: activeTab === 'requests' }"
          @click="activeTab = 'requests'"
        >
          REQUESTS
          <span v-if="friendRequests.length" class="tab-count tab-count-magenta">{{ friendRequests.length }}</span>
        </button>
        <button
          class="y2k-tab"
          :class="{ active: activeTab === 'search' }"
          @click="activeTab = 'search'"
        >
          FIND PLAYERS
        </button>
      </div>
 
      <!-- Error -->
      <p v-if="error" class="glow-text-magenta mb-3">{{ error }}</p>
 
      <!-- FRIENDS TAB -->
      <div v-if="activeTab === 'friends'">
        <div v-if="friends.length === 0" class="y2k-panel text-center py-4">
          <p style="color: var(--y2k-text)">NO FRIENDS YET. USE FIND PLAYERS TO ADD SOME!</p>
        </div>
        <div v-else class="row g-3">
          <div v-for="friend in friends" :key="friend.user_id" class="col-12 col-md-6">
            <div class="friend-card d-flex align-items-center gap-3">
              <div class="mini-avatar y2k-center-content flex-shrink-0">
                <img v-if="friend.profile_url" :src="friend.profile_url" class="mini-avatar-img" />
                <i v-else class="bi bi-person-fill"></i>
              </div>
              <div class="flex-grow-1">
                <div class="friend-name">{{ friend.username }}</div>
                <div class="friend-meta">
                  <span v-if="friend.level">LVL {{ friend.level?.toFixed(1) }}</span>
                  <span v-if="friend.highest_wpm" class="ms-2 glow-text-cyan">{{ friend.highest_wpm?.toFixed(1) }} WPM</span>
                </div>
              </div>
              <button class="y2k-btn y2k-btn-magenta y2k-btn-sm" @click="removeFriend(friend.user_id)">
                REMOVE
              </button>
            </div>
          </div>
        </div>
      </div>
 
      <!-- REQUESTS TAB -->
      <div v-if="activeTab === 'requests'">
        <h2 class="panel-heading mb-3">INCOMING</h2>
        <div v-if="friendRequests.length === 0" class="y2k-panel text-center py-3 mb-4">
          <p style="color: var(--y2k-text)">NO PENDING REQUESTS</p>
        </div>
        <div v-else class="row g-3 mb-4">
          <div v-for="req in friendRequests" :key="req.from_user_id" class="col-12 col-md-6">
            <div class="friend-card d-flex align-items-center gap-3">
              <div class="mini-avatar y2k-center-content flex-shrink-0">
                <img v-if="req.profile_url" :src="req.profile_url" class="mini-avatar-img" />
                <i v-else class="bi bi-person-fill"></i>
              </div>
              <div class="flex-grow-1">
                <div class="friend-name">{{ req.username }}</div>
                <div class="friend-meta">{{ formatDate(req.requested_at) }}</div>
              </div>
              <div class="d-flex gap-2">
                <button class="y2k-btn y2k-btn-sm" @click="acceptRequest(req.from_user_id)">ACCEPT</button>
                <button class="y2k-btn y2k-btn-magenta y2k-btn-sm" @click="declineRequest(req.from_user_id)">DECLINE</button>
              </div>
            </div>
          </div>
        </div>
 
        <h2 class="panel-heading mb-3">SENT</h2>
        <div v-if="sentRequests.length === 0" class="y2k-panel text-center py-3">
          <p style="color: var(--y2k-text)">NO SENT REQUESTS</p>
        </div>
        <div v-else class="row g-3">
          <div v-for="req in sentRequests" :key="req.to_user_id" class="col-12 col-md-6">
            <div class="friend-card d-flex align-items-center gap-3">
              <div class="mini-avatar y2k-center-content flex-shrink-0">
                <i class="bi bi-person-fill"></i>
              </div>
              <div class="flex-grow-1">
                <div class="friend-name">{{ req.username }}</div>
                <div class="friend-meta">STATUS: {{ req.status?.toUpperCase() ?? 'PENDING' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
 
      <!-- SEARCH TAB -->
      <div v-if="activeTab === 'search'">
        <div class="d-flex gap-2 mb-4">
          <input
            v-model="searchQuery"
            class="y2k-input flex-grow-1"
            type="text"
            placeholder="SEARCH BY USERNAME..."
            @keyup.enter="searchUsers"
          />
          <button class="y2k-btn y2k-btn-cyan" @click="searchUsers" :disabled="searching">
            {{ searching ? 'SEARCHING...' : 'SEARCH' }}
          </button>
        </div>
 
        <div v-if="searchResults.length === 0 && !searching" class="y2k-panel text-center py-4">
          <p style="color: var(--y2k-text)">ENTER A USERNAME TO FIND PLAYERS</p>
        </div>
 
        <div v-else class="row g-3">
          <div v-for="player in searchResults" :key="player.user_id" class="col-12 col-md-6">
            <div class="friend-card d-flex align-items-center gap-3">
              <div class="mini-avatar y2k-center-content flex-shrink-0">
                <img v-if="player.profile_url" :src="player.profile_url" class="mini-avatar-img" />
                <i v-else class="bi bi-person-fill"></i>
              </div>
              <div class="flex-grow-1">
                <div class="friend-name">{{ player.username }}</div>
                <div class="friend-meta">
                  <span v-if="player.level">LVL {{ player.level?.toFixed(1) }}</span>
                </div>
              </div>
              <span v-if="player.is_friend" class="friend-badge">FRIEND</span>
              <button
                v-else
                class="y2k-btn y2k-btn-cyan y2k-btn-sm"
                :disabled="player.request_sent"
                @click="sendRequest(player.user_id)"
              >
                {{ player.request_sent ? 'SENT' : 'ADD' }}
              </button>
            </div>
          </div>
        </div>
      </div>
 
    </div>
  </div>
</template>
 
<style scoped>
.friends-page {
  width: 100%;
}

.tab-count {
  display: inline-block;
  background: var(--y2k-cyan);
  color: #000;
  font-size: 0.8rem;
  padding: 0 5px;
  margin-left: 6px;
  line-height: 1.4;
}
.tab-count-magenta {
  background: var(--y2k-magenta);
}
 
/* Friend card */
.friend-card {
  background: var(--y2k-glass);
  border: 2px solid rgba(0, 255, 255, 0.2);
  padding: 0.9rem 1rem;
  transition: 0.2s;
}
.friend-card:hover {
  border-color: rgba(0, 255, 255, 0.5);
}
.friend-name {
  font-family: 'VT323', monospace;
  font-size: 1.3rem;
  color: var(--y2k-cyan);
  letter-spacing: 1px;
}
.friend-meta {
  font-size: 0.9rem;
  color: var(--y2k-text);
}
.friend-badge {
  font-family: 'VT323', monospace;
  font-size: 1rem;
  color: var(--y2k-lime);
  border: 1px solid var(--y2k-lime);
  padding: 0.1rem 0.5rem;
  letter-spacing: 1px;
}
 
/* Mini avatar */
.mini-avatar {
  width: 48px;
  height: 48px;
  border: 2px solid var(--y2k-cyan);
  background: rgba(0,0,0,0.4);
  font-size: 1.6rem;
  color: var(--y2k-cyan);
  overflow: hidden;
}
.mini-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
 
.panel-heading {
  color: var(--y2k-lime);
  font-family: 'VT323', monospace;
  font-size: 1.4rem;
  letter-spacing: 2px;
  text-shadow: 2px 2px 0 var(--y2k-blue);
  text-transform: uppercase;
}
</style>