import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import Orders from '../components/Orders.vue'

const routes = [
  { path: '/', component: Home },
  { path: '/orders', component: Orders },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
