import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import './style.css'

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)
app.use(router)

// Initialize auth store on app start
const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')
