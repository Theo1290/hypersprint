import './assets/main.css'

// 1. Import Bootstrap CSS and JS (Required for Grid and UI components)
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router) // Required for managing 10+ interconnected pages [cite: 14, 26]

app.mount('#app')
