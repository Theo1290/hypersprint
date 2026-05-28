<template>
  <div class="auth-required-container y2k-center-content py-5">
    <div class="y2k-panel y2k-panel-magenta text-center p-5">
      <h1 class="glow-text-magenta mb-4">ACCESS DENIED</h1>
      <i class="bi bi-shield-lock fs-1 glow-text-cyan mb-4 d-block"></i>
      <p class="mb-5 fs-4">Your current session is not authenticated. Please log in to access this system module.</p>
      
      <div class="d-flex justify-content-center gap-3">
        <RouterLink to="/login" class="y2k-btn y2k-btn-cyan">LOGIN</RouterLink>
        <RouterLink to="/signup" class="y2k-btn">SIGN UP</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.y2k-panel {
  max-width: 600px;
  box-shadow: 0 0 20px rgba(255, 0, 255, 0.2);
}
</style>
