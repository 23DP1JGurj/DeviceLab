import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import ClientOrders from '../components/ClientOrders.vue'
import Login from '../components/Login.vue'
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
  { path: '/orders', component: ClientOrders, meta: { requiresAuth: true, roles: CLIENT_ROLES } },
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
