import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import ClientNewOrder from '../components/ClientNewOrder.vue'
import ClientOrderHistory from '../components/ClientOrderHistory.vue'
import Login from '../components/Login.vue'
import MyDevices from '../components/MyDevices.vue'
import Profile from '../components/Profile.vue'
import Register from '../components/Register.vue'
import StaffAllOrders from '../components/StaffAllOrders.vue'
import StaffOrderHistory from '../components/StaffOrderHistory.vue'
import StaffMyOrders from '../components/StaffMyOrders.vue'
import StaffNewOrders from '../components/StaffNewOrders.vue'
import StaffOrders from '../components/StaffOrders.vue'
import {
  CLIENT_ROLES,
  STAFF_ROLES,
  currentUser,
  initAuth,
  resolveRedirectPath,
} from '../auth'

const routes = [
  { path: '/', component: Home },
  { path: '/login', component: Login, meta: { guestOnly: true } },
  { path: '/register', component: Register, meta: { guestOnly: true } },
  { path: '/devices', component: MyDevices, meta: { requiresAuth: true, roles: ['client'] } },
  { path: '/orders', redirect: '/orders/new' },
  { path: '/orders/new', component: ClientNewOrder, meta: { requiresAuth: true, roles: CLIENT_ROLES } },
  { path: '/orders/history', component: ClientOrderHistory, meta: { requiresAuth: true, roles: CLIENT_ROLES } },
  { path: '/profile', component: Profile, meta: { requiresAuth: true } },
  { path: '/staff/orders', component: StaffOrders, meta: { requiresAuth: true, roles: STAFF_ROLES } },
  { path: '/staff/orders/new', component: StaffNewOrders, meta: { requiresAuth: true, roles: STAFF_ROLES } },
  { path: '/staff/orders/my', component: StaffMyOrders, meta: { requiresAuth: true, roles: STAFF_ROLES } },
  { path: '/staff/orders/history', component: StaffOrderHistory, meta: { requiresAuth: true, roles: STAFF_ROLES } },
  { path: '/staff/orders/all', component: StaffAllOrders, meta: { requiresAuth: true, roles: ['admin'] } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const requiresAuth = to.matched.some(record => record.meta?.requiresAuth)
  const guestOnly = to.matched.some(record => record.meta?.guestOnly)
  const allowedRoles = to.matched.flatMap(record => record.meta?.roles || [])
  let user = currentUser.value

  if (requiresAuth || guestOnly || !user) {
    try {
      user = await initAuth()
    } catch {
      user = currentUser.value
    }
  }

  if (guestOnly && user) {
    return resolveRedirectPath(user, to.query.redirect)
  }

  if (requiresAuth && !user) {
    return {
      path: '/login',
      query: { redirect: to.fullPath },
    }
  }

  if (allowedRoles.length > 0 && !allowedRoles.includes(user?.role)) {
    return resolveRedirectPath(user, '/orders')
  }

  return true
})

export default router
