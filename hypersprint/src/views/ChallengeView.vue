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
.h1-large {
  font-size: 4rem !important;
  letter-spacing: 5px;
  text-shadow: 4px 4px 0 var(--y2k-blue);
}

.mode-card {
  position: relative;
  height: 400px;
  background: var(--y2k-glass);
  border: 4px solid rgba(255, 255, 255, 0.1);
  cursor: pointer;
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.mode-card:hover {
  transform: scale(1.05) translateY(-10px);
  z-index: 10;
}

.single-card:hover {
  border-color: var(--y2k-cyan);
  box-shadow: 0 0 30px rgba(0, 255, 255, 0.3);
}

.multi-card:hover {
  border-color: var(--y2k-magenta);
  box-shadow: 0 0 30px rgba(255, 0, 255, 0.3);
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, rgba(0,0,0,0.8), transparent);
  z-index: 1;
}

.card-content {
  position: relative;
  z-index: 2;
}

.fs-huge {
  font-size: 6rem;
  color: white;
  opacity: 0.8;
}

.btn-select {
  font-family: 'VT323', monospace;
  font-size: 1.8rem;
  padding: 0.5rem 2rem;
  border: 2px solid white;
  display: inline-block;
  color: white;
  transition: 0.3s;
}

.single-card:hover .btn-select {
  background: var(--y2k-cyan);
  color: black;
  border-color: var(--y2k-cyan);
}

.multi-card:hover .btn-select {
  background: var(--y2k-magenta);
  color: black;
  border-color: var(--y2k-magenta);
}

.glow-text-cyan { color: var(--y2k-cyan); text-shadow: 0 0 10px var(--y2k-cyan); }
.glow-text-magenta { color: var(--y2k-magenta); text-shadow: 0 0 10px var(--y2k-magenta); }
.glow-text-lime { color: var(--y2k-lime); text-shadow: 0 0 10px var(--y2k-lime); }
</style>
