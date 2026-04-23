import { createApp } from 'vue'
import App from './components/App.vue'
import { initAuth } from './auth'
import router from './router'

initAuth()
  .catch(() => null)
  .finally(() => {
    createApp(App).use(router).mount('#app')
  })
