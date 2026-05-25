<template>
  <div class="auth-required-container y2k-center-content py-5">
    <div class="y2k-panel text-center p-5">
      <h1 class="glow-text-magenta mb-4">ACCESS DENIED</h1>
      <i class="bi bi-shield-lock fs-1 glow-text-cyan mb-4 d-block"></i>
      <p class="mb-5 fs-4">Your current session is not authenticated. Please log in to access this system module.</p>
      
      <div class="d-flex justify-content-center gap-3">
        <RouterLink to="/login" class="btn-y2k btn-y2k-cyan">LOGIN</RouterLink>
        <RouterLink to="/signup" class="btn-y2k">SIGN UP</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.y2k-panel {
  background: var(--y2k-glass);
  border: 3px solid var(--y2k-magenta);
  max-width: 600px;
  box-shadow: 0 0 20px rgba(255, 0, 255, 0.2);
}

.btn-y2k {
  background: transparent;
  border: 2px solid var(--y2k-lime);
  color: var(--y2k-lime);
  font-family: 'VT323', monospace;
  font-size: 1.5rem;
  padding: 0.5rem 2rem;
  text-decoration: none;
  transition: 0.3s;
}

.btn-y2k:hover {
  background: var(--y2k-lime);
  color: #000;
  box-shadow: 0 0 10px var(--y2k-lime);
}

.btn-y2k-cyan {
  border-color: var(--y2k-cyan);
  color: var(--y2k-cyan);
}

.btn-y2k-cyan:hover {
  background: var(--y2k-cyan);
}

.glow-text-magenta { color: var(--y2k-magenta); text-shadow: 0 0 10px var(--y2k-magenta); }
.glow-text-cyan { color: var(--y2k-cyan); text-shadow: 0 0 10px var(--y2k-cyan); }
</style>
