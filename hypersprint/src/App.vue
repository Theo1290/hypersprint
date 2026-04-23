<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="app-container">
    <header>
      <nav class="navbar navbar-expand-lg navbar-dark y2k-nav">
        <div class="container-fluid">
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

    <main class="container py-4">
      <div class="y2k-page-wrapper">
        <RouterView />
      </div>
    </main>

    <footer class="y2k-footer text-center py-3">
      <p>&copy; 2026 HYPER SPRINT // SYSTEM STATUS: OPTIMAL</p>
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
  background: #000;
  border-bottom: 2px solid var(--y2k-cyan);
  box-shadow: 0 0 15px var(--y2k-cyan);
}

.y2k-logo {
  font-weight: 900;
  color: var(--y2k-cyan) !important;
  letter-spacing: 2px;
  text-shadow: 0 0 10px var(--y2k-cyan);
}

.nav-link {
  font-weight: 700;
  letter-spacing: 1px;
}

.nav-link:hover {
  color: var(--y2k-magenta) !important;
  text-shadow: 0 0 10px var(--y2k-magenta);
}

.y2k-user-icon {
  font-size: 1.2rem;
  vertical-align: middle;
}

.y2k-dropdown {
  background: #000;
  border: 1px solid var(--y2k-cyan);
}

.y2k-page-wrapper {
  background: var(--y2k-glass);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 5px;
  padding: 2rem;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
  min-height: 70vh;
}

.y2k-footer {
  margin-top: auto;
  border-top: 1px solid var(--y2k-magenta);
  color: var(--y2k-magenta);
  font-size: 0.8rem;
  letter-spacing: 2px;
}
</style>
