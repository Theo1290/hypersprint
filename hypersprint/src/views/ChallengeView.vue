<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const selectMode = (mode) => {
  if (mode === 'single') {
    router.push('/singleplayer')
  } else {
    router.push('/multiplayer')
  }
}
</script>

<template>
  <div class="container py-5 text-center">
    <h1 class="glow-text-lime mb-5 h1-large">CHALLENGE SELECT</h1>
    
    <div class="row g-5 justify-content-center mt-2">
      <!-- Singleplayer Option -->
      <div class="col-md-5">
        <div class="mode-card single-card" @click="selectMode('single')">
          <div class="card-overlay"></div>
          <div class="card-content">
            <i class="bi bi-person-fill fs-huge"></i>
            <h2 class="glow-text-cyan mt-3">SINGLEPLAYER</h2>
            <p class="mt-2 text-white">Classic sprint. Test your personal limits and set new high scores.</p>
            <div class="btn-select mt-4">INITIALIZE</div>
          </div>
        </div>
      </div>

      <!-- Multiplayer Option -->
      <div class="col-md-5">
        <div class="mode-card multi-card" @click="selectMode('multi')">
          <div class="card-overlay"></div>
          <div class="card-content">
            <i class="bi bi-people-fill fs-huge"></i>
            <h2 class="glow-text-magenta mt-3">MULTIPLAYER</h2>
            <p class="mt-2 text-white">1v1 Real-time race. Battle live opponents on the grid.</p>
            <div class="btn-select mt-4">READY UP</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Local styles moved to main.css */
</style>
