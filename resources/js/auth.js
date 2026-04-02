import { computed, ref } from 'vue'

const AUTH_USER_KEY = 'devicelab:authUser'

export const CLIENT_ROLES = ['client']
export const STAFF_ROLES = ['staff', 'admin']

function readStoredUser() {
  const raw = localStorage.getItem(AUTH_USER_KEY)
  if (!raw) return null

  try {
    return JSON.parse(raw)
  } catch {
    return null
  }
}

export const currentUser = ref(readStoredUser())
export const isLoggedIn = computed(() => Boolean(currentUser.value))

let authInitialized = false
let initRequest = null

export function setCurrentUser(user) {
  currentUser.value = user ?? null

  if (user) {
    localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user))
  } else {
    localStorage.removeItem(AUTH_USER_KEY)
  }
}

export function clearAuth() {
  setCurrentUser(null)
}

export function hasAnyRole(user, roles = []) {
  if (!user?.role || roles.length === 0) return false
  return roles.includes(user.role)
}

export function sanitizeRedirectPath(value) {
  if (typeof value !== 'string') return ''
  if (!value.startsWith('/') || value.startsWith('//')) return ''
  return value
}

export function defaultRouteForUser(user) {
  return hasAnyRole(user, STAFF_ROLES) ? '/staff/orders/new' : '/orders'
}

export function resolveRedirectPath(user, redirect) {
  if (!user?.role) {
    return '/login'
  }

  const safeRedirect = sanitizeRedirectPath(redirect)

  if (!safeRedirect) {
    return defaultRouteForUser(user)
  }

  if (safeRedirect.startsWith('/staff/orders') && !hasAnyRole(user, STAFF_ROLES)) {
    return defaultRouteForUser(user)
  }

  if (safeRedirect.startsWith('/orders') && !hasAnyRole(user, CLIENT_ROLES)) {
    return defaultRouteForUser(user)
  }

  return safeRedirect
}

export async function authFetch(url, options = {}) {
  const { headers: rawHeaders = {}, json, body, credentials, ...rest } = options
  const headers = new Headers(rawHeaders)

  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json')
  }

  let finalBody = body

  if (json !== undefined) {
    finalBody = JSON.stringify(json)
    if (!headers.has('Content-Type')) {
      headers.set('Content-Type', 'application/json')
    }
  }

  const response = await fetch(url, {
    ...rest,
    headers,
    body: finalBody,
    credentials: credentials ?? 'same-origin',
  })

  if (response.status === 401) {
    clearAuth()
  }

  return response
}

export async function extractErrorMessage(response, fallback = 'Request failed.') {
  const text = await response.text()

  if (!text) {
    return fallback
  }

  try {
    const payload = JSON.parse(text)

    if (typeof payload?.message === 'string' && payload.message.trim()) {
      return payload.message
    }

    const validationErrors = Object.values(payload?.errors || {}).flat()
    if (validationErrors.length > 0) {
      return String(validationErrors[0])
    }
  } catch {
    return text.slice(0, 260)
  }

  return text.slice(0, 260) || fallback
}

export async function extractErrorData(response, fallback = 'Request failed.') {
  const text = await response.text()

  if (!text) {
    return {
      message: fallback,
      fieldErrors: {},
      status: response.status,
    }
  }

  try {
    const payload = JSON.parse(text)
    const fieldErrors = Object.fromEntries(
      Object.entries(payload?.errors || {}).map(([key, value]) => [key, Array.isArray(value) ? value.map(String) : [String(value)]])
    )
    const firstFieldError = Object.values(fieldErrors).flat()[0]

    return {
      message: typeof payload?.message === 'string' && payload.message.trim()
        ? payload.message
        : String(firstFieldError || fallback),
      fieldErrors,
      status: response.status,
    }
  } catch {
    return {
      message: text.slice(0, 260) || fallback,
      fieldErrors: {},
      status: response.status,
    }
  }
}

function createApiError(data) {
  const error = new Error(data.message)
  error.fieldErrors = data.fieldErrors || {}
  error.status = data.status
  return error
}

export async function initAuth(force = false) {
  if (initRequest) return initRequest
  if (authInitialized && !force) return currentUser.value

  initRequest = (async () => {
    const response = await authFetch('/api/auth/me')

    if (response.status === 401) {
      authInitialized = true
      clearAuth()
      return null
    }

    if (!response.ok) {
      throw new Error(await extractErrorMessage(response, 'Unable to fetch current user.'))
    }

    const payload = await response.json()
    setCurrentUser(payload?.user ?? null)
    authInitialized = true
    return currentUser.value
  })()

  try {
    return await initRequest
  } finally {
    initRequest = null
  }
}

export async function login(email, password) {
  const response = await authFetch('/api/auth/login', {
    method: 'POST',
    json: { email, password },
  })

  if (!response.ok) {
    throw createApiError(await extractErrorData(response, 'Unable to login.'))
  }

  const payload = await response.json()
  setCurrentUser(payload?.user ?? null)
  authInitialized = true

  return currentUser.value
}

export async function register(payload) {
  const response = await authFetch('/api/auth/register', {
    method: 'POST',
    json: payload,
  })

  if (!response.ok) {
    throw createApiError(await extractErrorData(response, 'Unable to register.'))
  }

  const payloadJson = await response.json()
  setCurrentUser(payloadJson?.user ?? null)
  authInitialized = true

  return currentUser.value
}

export async function logout() {
  await authFetch('/api/auth/logout', {
    method: 'POST',
  }).catch(() => null)

  authInitialized = true
  clearAuth()
}

export async function updateProfile(payload) {
  const response = await authFetch('/api/auth/profile', {
    method: 'PATCH',
    json: payload,
  })

  if (!response.ok) {
    throw createApiError(await extractErrorData(response, 'Unable to update profile.'))
  }

  const json = await response.json()
  setCurrentUser(json?.user ?? null)
  authInitialized = true

  return currentUser.value
}
