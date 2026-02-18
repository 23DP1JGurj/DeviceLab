export const AUTH_TOKEN_KEY = 'devicelab:authToken'
export const AUTH_USER_KEY = 'devicelab:authUser'

const DEMO_CREDENTIALS = {
  client: { email: 'demo@devicelab.local', password: 'password' },
  staff: { email: 'staff@devicelab.local', password: 'password' },
  admin: { email: 'admin@devicelab.local', password: 'password' },
}

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

export function setAuthSession(payload) {
  if (payload?.token) localStorage.setItem(AUTH_TOKEN_KEY, payload.token)
  if (payload?.user) localStorage.setItem(AUTH_USER_KEY, JSON.stringify(payload.user))
}

export function clearAuthSession() {
  localStorage.removeItem(AUTH_TOKEN_KEY)
  localStorage.removeItem(AUTH_USER_KEY)
}

export function authHeaders(extraHeaders = {}) {
  const token = getAuthToken()
  const headers = {
    Accept: 'application/json',
    ...extraHeaders,
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  return headers
}

export async function loginRole(role) {
  const credentials = DEMO_CREDENTIALS[role]

  if (!credentials) {
    throw new Error(`Unsupported role: ${role}`)
  }

  const res = await fetch('/api/auth/login', {
    method: 'POST',
    headers: authHeaders({ 'Content-Type': 'application/json' }),
    body: JSON.stringify(credentials),
  })

  if (!res.ok) {
    const txt = await res.text()
    throw new Error(txt || 'Unable to login.')
  }

  const json = await res.json()
  setAuthSession(json)

  return json
}

export async function ensureRoleSession(role) {
  const token = getAuthToken()
  const user = getAuthUser()

  if (token && user?.role === role) {
    return { token, user }
  }

  clearAuthSession()

  return loginRole(role)
}
