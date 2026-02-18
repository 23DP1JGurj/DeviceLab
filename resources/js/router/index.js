import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import ClientOrders from '../components/ClientOrders.vue'
import StaffOrders from '../components/StaffOrders.vue'

const routes = [
  { path: '/', component: Home },
  { path: '/orders', component: ClientOrders },
  { path: '/staff/orders', component: StaffOrders },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
