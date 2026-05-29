<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { callApi } from '@/utils/api';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore()

const posts = ref([]) // array of feed items from API
const loading = ref(true) // "LOADING FEED.." shows immediately
const error = ref(null)
const currentPage = ref(1)
const totalPages = ref(1) // both ^ manage pagaination
let pollingInterval = null // holds interval ID

onMounted(() => { // runs once component first appears
  loadFeed() // populate page
  pollingInterval = setInterval(loadFeed, 60000) //every 60 seconds
})

onUnmounted(() => { // when user naviages away from page
  clearInterval(pollingInterval) 
})

// calls API with current page number .
async function loadFeed() {
  try {
    const res = await callApi(`/api/feed.php?page=${currentPage.value}`)
    posts.value = res.posts || []
    totalPages.value = res.pages || 1 // || 1 , are fallbacks incase the API return null
  } catch (e) {
    error.value = 'Failed to load feed.'
  } finally { // awlays run
    loading.value = false
  }
}

// updates current page
async function changePage(page) {
  currentPage.value = page
  loading.value = true
  await loadFeed()
}

// assume success, revert on failure
async function toggleLike(post) {
  if (!auth.isAuthenticated) return // if not logged in. Guard clause

  // Optimistic update -> before the API call, immediate flip
  post.liked_by_me = !post.liked_by_me
  post.like_count += post.liked_by_me ? 1 : -1

  try {
    await callApi('/api/feed.php', 'POST', { post_id: post.post_id })
  } catch (e) {
    // Revert on failure
    post.liked_by_me = !post.liked_by_me
    post.like_count += post.liked_by_me ? 1 : -1
  }
}

// substracting two Data objects gives milliseconds, divind my 1000 gives seconds. 
// converts the value into readable string for user
function timeAgo(dateString) {
  const seconds = Math.floor((new Date() - new Date(dateString)) / 1000)
  if (seconds < 60) return 'just now'
  if (seconds < 3600) return Math.floor(seconds / 60) + ' mins ago'
  if (seconds < 86400) return Math.floor(seconds / 3600) + ' hrs ago'
  return Math.floor(seconds / 86400) + ' days ago'
}
</script>

<template>
  <div class="feed-page">
    <h1 class="mb-2">ACTIVITY FEED</h1>
    <p class="feed-subtitle mb-4">Personal bests from the community</p>

    <div v-if="loading" class="y2k-center-content py-5">
      <p class="glow-text-cyan">LOADING FEED...</p>
    </div>

    <div v-else-if="error" class="y2k-center-content py-5">
      <p class="glow-text-magenta">{{ error }}</p>
    </div>

    <div v-else-if="posts.length === 0" class="y2k-panel text-center py-5">
      <p style="color: var(--y2k-text)">NO ACTIVITY YET. COMPLETE A CHALLENGE TO BE THE FIRST!</p>
    </div>

    <div v-else>
      <!-- Feed Cards -->
      <div class="feed-grid mb-4">
        <div v-for="post in posts" :key="post.post_id" class="feed-card">

          <!-- Card Header -->
          <div class="feed-card-header d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="feed-avatar y2k-center-content">
                <img v-if="post.profile_url" :src="post.profile_url" class="feed-avatar-img" />
                <i v-else class="bi bi-person-fill"></i>
              </div>
              <div>
                <div class="feed-username">{{ post.username }}</div>
                <div class="feed-time">{{ timeAgo(post.created_at) }}</div>
              </div>
            </div>
          </div>

          <!-- New Record Card -->
          <div v-if="post.post_data?.type === 'new_record'" class="feed-card-body">
            <div class="feed-badge mb-2"><i class="bi bi-trophy-fill me-1"></i>NEW PERSONAL BEST</div>
            <div class="feed-challenge-name mb-3">{{ post.post_data.challenge_title.replace('dynamic-time-', '').replace('dynamic-words-', '') + (post.post_data.challenge_title.includes('time') ? 's Sprint' : ' Words') }}</div>
            <div class="d-flex align-items-center gap-4 mb-3">
              <div class="text-center">
                <div class="feed-wpm glow-text-cyan">{{ post.post_data.new_wpm }}</div>
                <div class="feed-wpm-label">WPM</div>
              </div>
              <div class="text-center">
                <div class="feed-improvement glow-text-lime">+{{ post.post_data.improvement }}</div>
                <div class="feed-wpm-label">IMPROVEMENT</div>
              </div>
              <div class="text-center">
                <div class="feed-prev glow-text-magenta">{{ post.post_data.previous_wpm }}</div>
                <div class="feed-wpm-label">PREVIOUS</div>
              </div>
              <div class="text-center">
                <div class="feed-accuracy" style="color: var(--y2k-text)">{{ post.post_data.accuracy }}%</div>
                <div class="feed-wpm-label">ACCURACY</div>
              </div>
            </div>
          </div>

          <!-- Achievement Card -->
          <div v-else-if="post.post_data?.type === 'achievement'" class="feed-card-body">
            <div class="feed-badge feed-badge-achievement mb-2"><i class="bi bi-lightning-fill me-1"></i>ACHIEVEMENT UNLOCKED</div>
            <div class="feed-challenge-name mb-1">{{ post.post_data.achievement_title }}</div>
            <div class="feed-achievement-desc">{{ post.post_data.achievement_description }}</div>
          </div>

          <!-- Fallback for unknown post types -->
          <div v-else class="feed-card-body">
            <p style="color: var(--y2k-text)">{{ post.post_text }}</p>
          </div>

          <!-- Like Button -->
          <div class="feed-card-footer d-flex align-items-center gap-2 mt-3">
            <button
              v-if="auth.isAuthenticated"
              class="btn-like"
              :class="{ liked: post.liked_by_me }"
              @click="toggleLike(post)"
            >
              <i :class="post.liked_by_me ? 'bi bi-heart-fill' : 'bi bi-heart'"></i> {{ post.like_count }}
            </button>
            <span v-else class="like-guest">
              <RouterLink to="/login">LOGIN TO LIKE</RouterLink>
            </span>
          </div>

        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="d-flex gap-2 justify-content-center">
        <button
          v-for="page in totalPages"
          :key="page"
          class="btn-y2k btn-page"
          :class="{ active: page === currentPage }"
          @click="changePage(page)"
        >
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.feed-page { width: 100%; }

.feed-subtitle { color: var(--y2k-text); font-size: 1rem; }

.feed-avatar {
  width: 44px;
  height: 44px;
  border: 2px solid var(--y2k-cyan);
  background: rgba(0,0,0,0.4);
  font-size: 1.4rem;
  color: var(--y2k-cyan);
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.feed-avatar-img { width: 100%; height: 100%; object-fit: cover; }

.feed-username { font-family: 'VT323', monospace; font-size: 1.3rem; color: var(--y2k-cyan); letter-spacing: 1px; }
.feed-time { font-size: 0.8rem; color: var(--y2k-text); opacity: 0.7; }

.feed-badge {
  display: inline-block;
  background: rgba(0,255,255,0.1);
  border: 1px solid var(--y2k-cyan);
  color: var(--y2k-cyan);
  font-family: 'VT323', monospace;
  font-size: 1rem;
  padding: 0.2rem 0.8rem;
  letter-spacing: 1px;
}
.feed-badge-achievement { background: rgba(255,255,0,0.1); border-color: var(--y2k-lime); color: var(--y2k-lime); }

.feed-challenge-name { font-family: 'VT323', monospace; font-size: 1.4rem; color: var(--y2k-text); letter-spacing: 1px; }
.feed-achievement-desc { color: var(--y2k-text); font-size: 0.9rem; opacity: 0.8; }

.feed-wpm { font-family: 'VT323', monospace; font-size: 2.8rem; line-height: 1; }
.feed-improvement { font-family: 'VT323', monospace; font-size: 2.8rem; line-height: 1; }
.feed-prev { font-family: 'VT323', monospace; font-size: 2rem; line-height: 1; }
.feed-accuracy { font-family: 'VT323', monospace; font-size: 2rem; line-height: 1; }
.feed-wpm-label { font-size: 0.75rem; color: var(--y2k-text); letter-spacing: 1px; opacity: 0.7; margin-top: 0.2rem; }

.btn-like {
  background: transparent;
  border: 2px solid rgba(255,255,255,0.2);
  color: var(--y2k-text);
  font-family: 'VT323', monospace;
  font-size: 1.2rem;
  padding: 0.3rem 1rem;
  cursor: pointer;
  transition: 0.2s;
  letter-spacing: 1px;
}
.btn-like:hover { border-color: var(--y2k-magenta); }
.btn-like.liked { border-color: var(--y2k-magenta); color: var(--y2k-magenta); text-shadow: 0 0 6px var(--y2k-magenta); }

.like-guest { font-size: 0.9rem; opacity: 0.6; }

.btn-page { padding: 0.3rem 0.9rem; }
.btn-page.active { background: var(--y2k-cyan); color: #000; box-shadow: 0 0 10px var(--y2k-cyan); }

.feed-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.feed-card {
  background: var(--y2k-glass);
  border: 2px solid rgba(255, 255, 255, 0.6);
  padding: 1.5rem;
  transition: 0.2s;
}

.feed-card:hover {
  border-color: white;
  box-shadow: 0 0 12px rgba(255, 255, 255, 0.3);
}
@media (max-width: 768px) {
  .feed-grid {
    grid-template-columns: 1fr;
  }
}
</style>
