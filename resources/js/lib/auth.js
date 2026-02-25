export const AUTH_TOKEN_KEY = 'devicelab:authToken'
export const AUTH_USER_KEY = 'devicelab:authUser'

export const ORDER_ROLES = ['client', 'staff', 'admin']
export const STAFF_ROLES = ['staff', 'admin']

export function getAuthToken() {
  return localStorage.getItem(AUTH_TOKEN_KEY) || ''
}

export function getAuthUser() {
  const raw = localStorage.getItem(AUTH_USER_KEY)
  if (!raw) return null

  try {
    return JSON.parse(raw)
  } catch {
    return null
  }
}

export function getAuthRole() {
  return getAuthUser()?.role || ''
}

export function setAuthSession(payload = {}) {
  if (payload.token !== undefined) {
    if (payload.token) {
      localStorage.setItem(AUTH_TOKEN_KEY, payload.token)
    } else {
      localStorage.removeItem(AUTH_TOKEN_KEY)
    }
  }

  if (payload.user !== undefined) {
    if (payload.user) {
      localStorage.setItem(AUTH_USER_KEY, JSON.stringify(payload.user))
    } else {
      localStorage.removeItem(AUTH_USER_KEY)
    }
  }
}

export function clearAuthSession() {
  localStorage.removeItem(AUTH_TOKEN_KEY)
  localStorage.removeItem(AUTH_USER_KEY)
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
  return hasAnyRole(user, STAFF_ROLES) ? '/staff/orders' : '/orders'
}

export function resolveRedirectPath(user, redirect) {
  const safeRedirect = sanitizeRedirectPath(redirect)

  if (!safeRedirect) {
    return defaultRouteForUser(user)
  }

  if (safeRedirect.startsWith('/staff/orders') && !hasAnyRole(user, STAFF_ROLES)) {
    return defaultRouteForUser(user)
  }

  if (safeRedirect.startsWith('/orders') && !hasAnyRole(user, ORDER_ROLES)) {
    return '/login'
  }

  return safeRedirect
}

export async function apiFetch(url, options = {}) {
  const { headers: rawHeaders = {}, json, body, ...rest } = options
  const headers = new Headers(rawHeaders)
  const token = getAuthToken()

  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json')
  }

  if (token && !headers.has('Authorization')) {
    headers.set('Authorization', `Bearer ${token}`)
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
  })

  if (response.status === 401) {
    clearAuthSession()
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

export async function fetchMe() {
  const response = await apiFetch('/api/auth/me')

  if (!response.ok) {
    throw new Error(await extractErrorMessage(response, 'Unable to fetch current user.'))
  }

  const payload = await response.json()
  return payload?.user ?? null
}

export async function syncAuthUser() {
  const user = await fetchMe()
  setAuthSession({ user })
  return user
}
