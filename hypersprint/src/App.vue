<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="app-container py-4">
    <header class="container mb-4">
      <nav class="navbar navbar-expand-lg navbar-dark y2k-nav px-4">
        <div class="container-fluid px-0">
          <RouterLink class="navbar-brand y2k-logo" to="/">HYPER SPRINT</RouterLink>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <RouterLink class="nav-link" to="/">HOME</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/challenge">CHALLENGE</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/leaderboard">LEADERBOARD</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/stats">STATS</RouterLink>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link" to="/social-feed">FEED</RouterLink>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-person-circle y2k-user-icon"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end y2k-dropdown" aria-labelledby="navbarDropdown">
                  <li><RouterLink class="dropdown-item" to="/profile">PROFILE</RouterLink></li>
                  <li><RouterLink class="dropdown-item" to="/achievements">ACHIEVEMENTS</RouterLink></li>
                  <li><RouterLink class="dropdown-item" to="/friends">FRIENDS</RouterLink></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><RouterLink class="dropdown-item" to="/login">LOGIN</RouterLink></li>
                  <li><RouterLink class="dropdown-item" to="/signup">SIGNUP</RouterLink></li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <main class="container mb-4">
      <div class="y2k-page-wrapper y2k-center-content">
        <RouterView />
      </div>
    </main>

    <footer class="container mt-auto">
      <div class="y2k-footer text-center py-4">
        <p class="mb-0 y2k-footer-text">
          <span class="glow-text-magenta">&copy; 2026 HYPER SPRINT</span> 
          <span class="mx-3">//</span> 
          <span class="glow-text-cyan">SYSTEM STATUS: OPTIMAL</span>
          <span class="mx-3">//</span>
          <span class="glow-text-lime">V.1.0.4-BETA</span>
        </p>
      </div>
    </footer>
  </div>
</template>

<style>
.app-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.y2k-nav {
  background: var(--y2k-bg); /* Solid Dark Purple */
  border: 3px solid var(--y2k-cyan);
  box-shadow: 0 0 20px var(--y2k-cyan);
  z-index: 1000;
  border-radius: 0 !important;
}

.y2k-logo {
  font-weight: 900;
  color: var(--y2k-cyan) !important;
  letter-spacing: 3px;
  text-shadow: 3px 3px 0px var(--y2k-magenta);
}

.nav-link {
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  font-size: 1.2rem;
}

.nav-link:hover {
  color: var(--y2k-magenta) !important;
  text-shadow: 0 0 10px var(--y2k-magenta);
}

.y2k-user-icon {
  font-size: 1.6rem;
  vertical-align: middle;
}

.y2k-dropdown {
  background: var(--y2k-bg);
  border: 2px solid var(--y2k-cyan);
  box-shadow: 0 0 15px var(--y2k-cyan);
  border-radius: 0 !important;
}

.dropdown-item {
  font-family: 'VT323', monospace;
  font-size: 1.2rem;
}

.dropdown-item:hover {
  background: var(--y2k-cyan);
  color: #000;
}

.y2k-page-wrapper {
  background: var(--y2k-bg); /* Solid Dark Purple */
  width: 100%;
  min-height: 60vh;
  padding: 3rem;
  border: 3px solid rgba(255, 255, 255, 0.2);
  border-radius: 0 !important;
}

.y2k-footer {
  background: var(--y2k-bg); /* Solid Dark Purple */
  border: 3px solid var(--y2k-magenta);
  box-shadow: 0 0 20px var(--y2k-magenta);
  z-index: 1000;
  position: relative;
  border-radius: 0 !important;
}

.y2k-footer-text {
  font-weight: bold;
  letter-spacing: 2px;
  font-size: 0.9rem;
}

.glow-text-magenta {
  color: var(--y2k-magenta);
  text-shadow: 0 0 5px var(--y2k-magenta);
}

.glow-text-cyan {
  color: var(--y2k-cyan);
  text-shadow: 0 0 5px var(--y2k-cyan);
}

.glow-text-lime {
  color: var(--y2k-lime);
  text-shadow: 0 0 5px var(--y2k-lime);
}
</style>
