import { createApp } from 'vue'
import App from './components/App.vue'
import { initAuth } from './auth'
import router from './router'

void initAuth().catch(() => null)

createApp(App).use(router).mount('#app')
