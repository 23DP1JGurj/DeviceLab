<template>
  <div class="page notificationsPage">
    <DashboardTopbar title="Paziņojumi" subtitle="Svarīgākās ziņas par pasūtījumiem." />

    <div class="toolbar">
      <div class="filterShell" role="tablist" aria-label="Paziņojumu filtrs">
        <button :class="['filterButton', { active: scope === 'unread' }]" type="button" @click="setScope('unread')">
          Nelasītie
        </button>
        <button :class="['filterButton', { active: scope === 'all' }]" type="button" @click="setScope('all')">
          Visi
        </button>
      </div>

      <div class="toolbarRight">
        <div class="unreadText">
          <strong>{{ unreadCount }}</strong>
          <span> nelasīti paziņojumi</span>
        </div>
        <button
          v-if="scope === 'unread'"
          class="btn btnSoft"
          type="button"
          :disabled="markingAll || unreadCount === 0"
          @click="markAllRead"
        >
          {{ markingAll ? 'Atzīmējam...' : 'Atzīmēt visu kā izlasītu' }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="card stateCard">
      <div class="muted">Ielādējam paziņojumus...</div>
    </div>

    <div v-else-if="error" class="card stateCard">
      <div class="msg">{{ error }}</div>
    </div>

    <div v-else-if="groups.length === 0" class="card empty">
      Paziņojumu vēl nav.
    </div>

    <div v-else class="inboxStack">
      <article class="card inboxCard" v-for="group in groups" :key="group.order_id || group.order_number">
        <div class="orderSummary">
          <div class="orderIdentity">
            <div class="orderLine">
              <h2>{{ group.order_number }}</h2>
              <span v-if="group.unread_count > 0" class="badge">{{ group.unread_count }} jauni</span>
            </div>
            <div class="muted smallText">{{ group.last_items?.length || 0 }} pēdējie paziņojumi</div>
          </div>

          <div class="messageList">
            <div
              v-for="item in group.last_items"
              :key="item.id"
              :class="['messageItem', { unread: !item.read_at }]"
            >
              <span class="messageDot" aria-hidden="true"></span>
              <div class="messageCopy">
                <div class="messageTitle">{{ item.title }}</div>
                <p>{{ shortMessage(item.message) }}</p>
              </div>
              <time>{{ formatShortDate(item.created_at) }}</time>
            </div>
          </div>

          <button
            v-if="group.order_id"
            class="btn btnPrimary openButton"
            type="button"
            @click="openOrder(group)"
          >
            Atvērt pasūtījumu
          </button>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { authFetch, currentUser, extractErrorMessage } from '../auth'
import DashboardTopbar from './DashboardTopbar.vue'

const router = useRouter()

const groups = ref([])
const scope = ref('unread')
const unreadCount = ref(0)
const loading = ref(false)
const error = ref('')
const markingAll = ref(false)

function notifyMenuUpdated() {
  window.dispatchEvent(new CustomEvent('devicelab:notifications-updated'))
}

function formatDate(value) {
  try {
    return new Date(value).toLocaleString('lv-LV')
  } catch {
    return value || ''
  }
}

function formatShortDate(value) {
  try {
    return new Date(value).toLocaleString('lv-LV', {
      day: '2-digit',
      month: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return value || ''
  }
}

function shortMessage(value) {
  const text = String(value || '').trim()
  return text.length > 120 ? `${text.slice(0, 117)}...` : text
}

function orderPath(group) {
  const orderId = group.order_id

  if (!orderId) return ''
  if (currentUser.value?.role === 'admin') return `/admin/orders/${orderId}`
  if (['staff', 'admin'].includes(currentUser.value?.role)) return `/staff/orders/${orderId}`

  return `/orders/${orderId}`
}

async function loadNotifications() {
  loading.value = true
  error.value = ''

  try {
    const params = new URLSearchParams({
      scope: scope.value,
      group: 'order',
    })
    const response = await authFetch(`/api/notifications?${params}`)

    if (!response.ok) {
      if (response.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/notifications' } })
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt paziņojumus.'))
    }

    const json = await response.json()
    groups.value = json.data ?? []
    unreadCount.value = Number(json.unread_count || 0)
    notifyMenuUpdated()
  } catch (err) {
    groups.value = []
    error.value = (err?.message || 'Neizdevās ielādēt paziņojumus.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

async function setScope(value) {
  if (scope.value === value) return
  scope.value = value
  await loadNotifications()
}

async function markNotificationRead(notification) {
  if (notification.read_at) return

  await authFetch(`/api/notifications/${notification.id}/read`, {
    method: 'PATCH',
  }).catch(() => null)
}

async function openOrder(group) {
  await Promise.all((group.last_items || []).map(markNotificationRead))
  notifyMenuUpdated()

  const path = orderPath(group)
  if (path) {
    await router.push(path)
  }
}

async function markAllRead() {
  if (scope.value !== 'unread' || unreadCount.value === 0) return

  markingAll.value = true
  error.value = ''

  try {
    const response = await authFetch('/api/notifications/mark-all-read', {
      method: 'PATCH',
    })

    if (!response.ok) {
      throw new Error(await extractErrorMessage(response, 'Neizdevās atzīmēt paziņojumus.'))
    }

    await loadNotifications()
    notifyMenuUpdated()
  } catch (err) {
    error.value = (err?.message || 'Neizdevās atzīmēt paziņojumus.').slice(0, 260)
  } finally {
    markingAll.value = false
  }
}

onMounted(loadNotifications)
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue";
  background: radial-gradient(1200px 600px at 20% 0%, #eef6ff 0%, #f6f8fb 45%, #f7f7f7 100%);
  color: #0f172a;
}

.page {
  max-width: 1080px;
  margin: 0 auto;
  padding: 22px 18px 34px;
}

.notificationsPage .toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  margin: 18px 0;
  padding: 14px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.78);
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
}

.notificationsPage .filterShell {
  display: inline-flex;
  gap: 6px;
  padding: 5px;
  border-radius: 999px;
  background: #eef4fb;
  border: 1px solid rgba(148, 163, 184, 0.24);
}

.notificationsPage .filterButton {
  appearance: none;
  min-height: 38px;
  padding: 0 16px;
  border: 0;
  border-radius: 999px;
  background: transparent;
  color: #475569;
  cursor: pointer;
  font: inherit;
  font-weight: 850;
  line-height: 1;
  transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
}

.notificationsPage .filterButton:hover {
  color: #1d4ed8;
}

.notificationsPage .filterButton.active {
  color: #0f172a;
  background: #fff;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.notificationsPage .toolbarRight {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  flex-wrap: wrap;
}

.notificationsPage .unreadText {
  color: #64748b;
  font-size: 14px;
}

.notificationsPage .unreadText strong {
  color: #071833;
}

.card {
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 20px;
  box-shadow: 0 16px 38px rgba(15, 23, 42, 0.07);
  padding: 18px;
}

.stateCard,
.empty {
  color: #64748b;
}

.empty {
  padding: 28px;
  text-align: center;
  font-weight: 800;
}

.inboxStack {
  display: grid;
  gap: 14px;
}

.notificationsPage .inboxCard {
  padding: 20px 22px;
}

.notificationsPage .orderSummary {
  display: grid;
  grid-template-columns: minmax(210px, 0.8fr) minmax(0, 1.6fr) auto;
  align-items: center;
  gap: 22px;
}

.notificationsPage .orderIdentity {
  min-width: 0;
}

.notificationsPage .orderLine {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.notificationsPage .orderLine h2 {
  margin: 0;
  color: #071833;
  font-size: 21px;
  font-weight: 950;
  letter-spacing: -0.02em;
}

.notificationsPage .smallText {
  margin-top: 6px;
  font-size: 13px;
}

.notificationsPage .badge {
  display: inline-flex;
  align-items: center;
  min-height: 26px;
  padding: 0 10px;
  border-radius: 999px;
  color: #1d4ed8;
  background: rgba(37, 99, 235, 0.10);
  border: 1px solid rgba(37, 99, 235, 0.18);
  font-size: 12px;
  font-weight: 900;
}

.notificationsPage .messageList {
  display: grid;
  gap: 7px;
  min-width: 0;
}

.notificationsPage .messageItem {
  display: grid;
  grid-template-columns: 8px minmax(0, 1fr) auto;
  align-items: center;
  gap: 10px;
  min-width: 0;
  padding: 9px 11px;
  border-radius: 14px;
  background: transparent;
  border: 1px solid transparent;
}

.notificationsPage .messageItem.unread {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.notificationsPage .messageDot {
  width: 7px;
  height: 7px;
  border-radius: 999px;
  background: #cbd5e1;
}

.notificationsPage .messageItem.unread .messageDot {
  background: #2f7cff;
}

.notificationsPage .messageCopy {
  min-width: 0;
}

.notificationsPage .messageTitle {
  color: #071833;
  font-weight: 900;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.notificationsPage .messageItem p {
  margin: 2px 0 0;
  color: #475569;
  line-height: 1.35;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.notificationsPage .messageItem time {
  flex: 0 0 auto;
  color: #64748b;
  font-size: 12px;
  white-space: nowrap;
}

.notificationsPage .openButton {
  min-width: 154px;
  justify-content: center;
  text-align: center;
}

.muted { color: #64748b; }
.msg { color: #b91c1c; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.18); padding: 10px 12px; border-radius: 12px; font-size: 13px; }

.btn {
  display: inline-flex;
  align-items: center;
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  color: #0f172a;
  padding: 10px 14px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 850;
  text-decoration: none;
}

.btn:disabled { opacity: 0.58; cursor: not-allowed; }
.btnPrimary { color: #fff; background: linear-gradient(135deg, #2f7cff, #1d4ed8); border-color: transparent; box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22); }
.btnSoft { background: rgba(37, 99, 235, 0.08); border-color: rgba(37, 99, 235, 0.16); color: #1d4ed8; }

@media (max-width: 760px) {
  .notificationsPage .toolbar,
  .notificationsPage .orderSummary {
    align-items: stretch;
    grid-template-columns: 1fr;
  }

  .notificationsPage .toolbarRight,
  .notificationsPage .filterShell,
  .notificationsPage .btn {
    width: 100%;
  }

  .notificationsPage .filterButton {
    flex: 1;
  }

  .notificationsPage .messageItem {
    grid-template-columns: 8px minmax(0, 1fr);
  }

  .notificationsPage .messageItem time {
    grid-column: 2;
    white-space: normal;
  }
}
</style>
