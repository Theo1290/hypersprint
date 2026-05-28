<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { callApi } from '@/utils/api'
import Pusher from 'pusher-js'

const router = useRouter()

// State
const status = ref('initializing') // initializing, searching, found
const raceData = ref(null)
const error = ref('')
const dots = ref('')

// Pusher
let pusher = null
let channel = null

const startDotsAnimation = () => {
  setInterval(() => {
    dots.value = dots.value.length >= 3 ? '' : dots.value + '.'
  }, 500)
}

const joinLobby = async () => {
  status.value = 'searching'
  try {
    const res = await callApi('/api/race_lobby.php', 'POST', { action: 'join' })
    
    if (res.success) {
      raceData.value = res
      
      // Initialize Pusher to listen for match
      pusher = new Pusher('5285b141d0e1f0c0cae6', { cluster: 'ap4' })
      channel = pusher.subscribe('race-' + res.race_uuid)
      
      // If already active (found a match immediately)
      if (res.status === 'active') {
        handleMatchFound(res)
      } else {
        // Wait for someone else to join
        channel.bind('match-found', (data) => {
          handleMatchFound(data)
        })
      }
    }
  } catch (err) {
    error.value = 'LOBBY ERROR: ' + err.message
    status.value = 'error'
  }
}

const handleMatchFound = (data) => {
  status.value = 'found'
  setTimeout(() => {
    router.push({
      name: 'race',
      params: { uuid: data.race_uuid }
    })
  }, 2000)
}

onMounted(() => {
  joinLobby()
  startDotsAnimation()
})

onUnmounted(() => {
  if (status.value === 'searching' && raceData.value?.race_uuid) {
    callApi('/api/race_lobby.php', 'POST', { 
        action: 'leave', 
        race_uuid: raceData.value.race_uuid 
    });
  }
  if (channel) channel.unbind_all()
  if (pusher) pusher.disconnect()
})
</script>

<template>
  <div class="y2k-page-wrapper">
    <div class="lobby-container y2k-center-content">
      <div class="y2k-panel p-5 text-center">
        <h1 class="glow-text-cyan mb-4">MATCHMAKING</h1>
        
        <div v-if="status === 'searching'" class="status-box">
          <div class="radar-ping mb-4"></div>
          <p class="fs-3 glow-text-lime">SEARCHING FOR OPPONENTS{{ dots }}</p>
          <p class="text-white-50 mt-2">Connecting to Hyper-Grid Pulse...</p>
        </div>

        <div v-if="status === 'found'" class="status-box">
          <i class="bi bi-check-circle-fill fs-huge glow-text-cyan mb-3 d-block"></i>
          <p class="fs-2 glow-text-magenta">OPPONENT DETECTED!</p>
          <p class="fs-4 text-white">Sprinting against: <b class="glow-text-cyan">{{ raceData?.opponent_name || 'SYNCED PLAYER' }}</b></p>
          <p class="mt-4 glow-text-lime">LOADING BATTLEGRID...</p>
        </div>

        <div v-if="status === 'error'" class="status-box">
          <i class="bi bi-exclamation-triangle-fill fs-huge text-danger mb-3 d-block"></i>
          <p class="fs-3 text-danger">{{ error }}</p>
          <button @click="joinLobby" class="btn btn-outline-info mt-4">RETRY CONNECTION</button>
        </div>

        <div class="mt-5 pt-3 border-top border-secondary">
          <RouterLink to="/challenge" class="btn-cancel">ABORT MISSION</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.lobby-container {
  min-height: 80vh;
}

.y2k-panel {
  width: 100%;
  max-width: 700px;
  box-shadow: 0 0 30px rgba(0, 255, 255, 0.2);
}

.fs-huge { font-size: 5rem; }

.radar-ping {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  border: 4px solid var(--y2k-lime);
  margin: 0 auto;
  animation: ping 1.5s infinite ease-out;
}

@keyframes ping {
  0% { transform: scale(0.5); opacity: 1; }
  100% { transform: scale(2); opacity: 0; }
}

.btn-cancel {
  color: var(--y2k-magenta);
  text-decoration: none;
  font-family: 'VT323', monospace;
  font-size: 1.5rem;
  letter-spacing: 2px;
  transition: 0.3s;
}

.btn-cancel:hover {
  text-shadow: 0 0 10px var(--y2k-magenta);
  letter-spacing: 4px;
}
</style>
