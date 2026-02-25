import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import ClientOrders from '../components/ClientOrders.vue'
import Login from '../components/Login.vue'
import Register from '../components/Register.vue'
import StaffOrders from '../components/StaffOrders.vue'
import {
  ORDER_ROLES,
  STAFF_ROLES,
  clearAuthSession,
  getAuthToken,
  getAuthUser,
  resolveRedirectPath,
  syncAuthUser,
} from '../lib/auth'

const routes = [
  { path: '/', component: Home },
  { path: '/login', component: Login, meta: { guestOnly: true } },
  { path: '/register', component: Register, meta: { guestOnly: true } },
  { path: '/orders', component: ClientOrders, meta: { requiresAuth: true, roles: ORDER_ROLES } },
  { path: '/staff/orders', component: StaffOrders, meta: { requiresAuth: true, roles: STAFF_ROLES } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const requiresAuth = to.matched.some(record => record.meta?.requiresAuth)
  const guestOnly = to.matched.some(record => record.meta?.guestOnly)
  const allowedRoles = to.matched.flatMap(record => record.meta?.roles || [])

  const token = getAuthToken()
  let user = getAuthUser()

  if (token && !user) {
    try {
      user = await syncAuthUser()
    } catch {
      clearAuthSession()
      user = null
    }
  }

  if (guestOnly && token && user) {
    return resolveRedirectPath(user, to.query.redirect)
  }

  if (requiresAuth && (!token || !user)) {
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
