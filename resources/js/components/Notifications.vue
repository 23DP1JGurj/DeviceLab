<template>
  <div class="page">
    <DashboardTopbar title="Paziņojumi" subtitle="Aktuālie paziņojumi par pasūtījumiem, apmaksu un atsauksmēm." />

    <div class="toolbar">
      <div>
        <strong>{{ unreadCount }}</strong>
        <span class="muted"> nelasīti paziņojumi</span>
      </div>

      <button class="btn btnSoft" type="button" :disabled="markingAll || unreadCount === 0" @click="markAllRead">
        {{ markingAll ? 'Atzīmējam...' : 'Atzīmēt visu kā izlasītu' }}
      </button>
    </div>

    <div v-if="loading" class="card">
      <div class="muted">Ielādējam paziņojumus...</div>
    </div>

    <div v-else-if="error" class="card">
      <div class="msg">{{ error }}</div>
    </div>

    <div v-else-if="notifications.length === 0" class="card empty">
      <div class="emptyIcon">•</div>
      <h2>Paziņojumu vēl nav.</h2>
      <p class="muted">Šeit parādīsies ziņas par pasūtījuma statusu, apmaksu un atsauksmēm.</p>
    </div>

    <div v-else class="noticeStack">
      <article
        v-for="notification in notifications"
        :key="notification.id"
        :class="['card', 'noticeCard', { 'is-unread': !notification.read_at }]"
      >
        <div class="noticeTop">
          <div>
            <div class="noticeTitleRow">
              <h2>{{ notification.title }}</h2>
              <span v-if="!notification.read_at" class="badge">Jauns</span>
            </div>
            <p>{{ notification.message }}</p>
          </div>
          <time>{{ formatDate(notification.created_at) }}</time>
        </div>

        <div class="noticeActions">
          <button
            v-if="!notification.read_at"
            class="btn btnGhost"
            type="button"
            :disabled="markingId === notification.id"
            @click="markRead(notification)"
          >
            Atzīmēt kā izlasītu
          </button>

          <button
            v-if="notification.order_id"
            class="btn btnPrimary"
            type="button"
            @click="openOrder(notification)"
          >
            Atvērt pasūtījumu
          </button>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { authFetch, currentUser, extractErrorMessage } from '../auth'
import DashboardTopbar from './DashboardTopbar.vue'

const router = useRouter()

const notifications = ref([])
const loading = ref(false)
const error = ref('')
const markingAll = ref(false)
const markingId = ref(null)

const unreadCount = computed(() => notifications.value.filter(notification => !notification.read_at).length)

function notifyMenuUpdated() {
  window.dispatchEvent(new CustomEvent('devicelab:notifications-updated'))
}

function formatDate(value) {
  try {
    return new Date(value).toLocaleString('lv-LV')
  } catch {
    return value
  }
}

function orderPath(notification) {
  const orderId = notification.order_id

  if (!orderId) return ''
  if (currentUser.value?.role === 'admin') return `/admin/orders/${orderId}`
  if (['staff', 'admin'].includes(currentUser.value?.role)) return `/staff/orders/${orderId}`

  return `/orders/${orderId}`
}

async function loadNotifications() {
  loading.value = true
  error.value = ''

  try {
    const response = await authFetch('/api/notifications')

    if (!response.ok) {
      if (response.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/notifications' } })
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt paziņojumus.'))
    }

    notifications.value = await response.json()
    notifyMenuUpdated()
  } catch (err) {
    notifications.value = []
    error.value = (err?.message || 'Neizdevās ielādēt paziņojumus.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

async function markRead(notification) {
  if (notification.read_at) return notification

  markingId.value = notification.id

  try {
    const response = await authFetch(`/api/notifications/${notification.id}/read`, {
      method: 'PATCH',
    })

    if (!response.ok) {
      throw new Error(await extractErrorMessage(response, 'Neizdevās atzīmēt paziņojumu.'))
    }

    const updated = await response.json()
    const index = notifications.value.findIndex(item => item.id === notification.id)

    if (index !== -1) {
      notifications.value[index] = updated
    }

    notifyMenuUpdated()
    return updated
  } catch (err) {
    error.value = (err?.message || 'Neizdevās atzīmēt paziņojumu.').slice(0, 260)
    return notification
  } finally {
    markingId.value = null
  }
}

async function markAllRead() {
  markingAll.value = true
  error.value = ''

  try {
    const response = await authFetch('/api/notifications/read-all', {
      method: 'POST',
    })

    if (!response.ok) {
      throw new Error(await extractErrorMessage(response, 'Neizdevās atzīmēt paziņojumus.'))
    }

    const now = new Date().toISOString()
    notifications.value = notifications.value.map(notification => ({
      ...notification,
      read_at: notification.read_at || now,
    }))
    notifyMenuUpdated()
  } catch (err) {
    error.value = (err?.message || 'Neizdevās atzīmēt paziņojumus.').slice(0, 260)
  } finally {
    markingAll.value = false
  }
}

async function openOrder(notification) {
  await markRead(notification)

  const path = orderPath(notification)
  if (path) {
    await router.push(path)
  }
}

onMounted(loadNotifications)
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue"; background: radial-gradient(1200px 600px at 20% 0%, #eef6ff 0%, #f6f8fb 45%, #f7f7f7 100%); color: #0f172a; }
.page { max-width: 1080px; margin: 0 auto; padding: 22px 18px 34px; }
.topbar { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 16px; }
.titleBlock { min-width: 0; }
.h1 { margin: 0; font-size: 38px; letter-spacing: -0.03em; color: #061735; }
.subtitle, .muted { color: #64748b; font-size: 14px; }
.subtitle { margin-top: 6px; }
.topActions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.toolbar { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin: 18px 0; padding: 16px 18px; border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 18px; background: rgba(255, 255, 255, 0.72); box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06); }
.card { background: rgba(255, 255, 255, 0.96); border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 18px; box-shadow: 0 16px 38px rgba(15, 23, 42, 0.07); padding: 18px; }
.noticeStack { display: grid; gap: 14px; }
.noticeCard { position: relative; transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease; }
.noticeCard:hover { transform: translateY(-1px); border-color: rgba(37, 99, 235, 0.18); box-shadow: 0 20px 42px rgba(15, 23, 42, 0.10); }
.noticeCard.is-unread { border-color: rgba(37, 99, 235, 0.22); background: linear-gradient(135deg, rgba(239, 246, 255, 0.98), rgba(255, 255, 255, 0.98)); }
.noticeTop { display: flex; justify-content: space-between; gap: 16px; }
.noticeTitleRow { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
.noticeTitleRow h2 { margin: 0; font-size: 18px; color: #061735; }
.noticeTop p { margin: 8px 0 0; color: #475569; line-height: 1.55; }
.noticeTop time { color: #64748b; font-size: 13px; white-space: nowrap; }
.noticeActions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
.badge { display: inline-flex; align-items: center; min-height: 24px; padding: 0 9px; border-radius: 999px; color: #1d4ed8; background: rgba(37, 99, 235, 0.10); border: 1px solid rgba(37, 99, 235, 0.18); font-size: 12px; font-weight: 900; }
.btn { border: 1px solid rgba(15, 23, 42, 0.14); background: #fff; color: #0f172a; padding: 10px 14px; border-radius: 12px; cursor: pointer; font-weight: 850; text-decoration: none; transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease; }
.btn:hover:not(:disabled) { transform: translateY(-1px); border-color: rgba(37, 99, 235, 0.24); }
.btn:disabled { opacity: 0.58; cursor: not-allowed; }
.btnPrimary { color: #fff; background: linear-gradient(135deg, #2f7cff, #1d4ed8); border-color: transparent; box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22); }
.btnSoft { background: rgba(37, 99, 235, 0.08); border-color: rgba(37, 99, 235, 0.16); color: #1d4ed8; }
.btnGhost { background: rgba(255, 255, 255, 0.70); }
.empty { text-align: center; padding: 34px 18px; }
.empty h2 { margin: 8px 0 6px; font-size: 22px; }
.emptyIcon { width: 42px; height: 42px; margin: 0 auto; display: grid; place-items: center; border-radius: 999px; color: #2563eb; background: rgba(37, 99, 235, 0.10); font-size: 30px; line-height: 1; }
.msg { color: #b91c1c; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.18); padding: 10px 12px; border-radius: 12px; font-size: 13px; }
@media (max-width: 760px) {
  .topbar, .toolbar, .noticeTop { align-items: flex-start; flex-direction: column; }
  .h1 { font-size: 32px; }
  .noticeTop time { white-space: normal; }
}
</style>
