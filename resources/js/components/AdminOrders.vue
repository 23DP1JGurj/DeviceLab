<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Visi pasūtījumi</h1>
        <div class="subtitle">Pilns pārskats par klientu pieteikumiem.</div>
      </div>
      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/admin">← Admin panelis</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="sectionHeader">
      <div class="sectionTitle">Pasūtījumi</div>
      <div class="muted">Kopā: {{ total }}</div>
    </div>

    <div v-if="loading" class="card muted">Ielādējam pasūtījumus...</div>
    <div v-else-if="error" class="card"><div class="msg">{{ error }}</div></div>
    <div v-else-if="orders.length === 0" class="card muted">Pasūtījumu vēl nav.</div>

    <div v-else class="stack">
      <article class="card" v-for="order in orders" :key="order.id">
        <div class="rowTop">
          <div class="mainText">
            <div class="titleLine">
              <div class="itemTitle">{{ order.order_number }}</div>
              <span class="badge">{{ statusLabel(order.status) }}</span>
              <span :class="['badge', order.payment?.status === 'paid' ? 'badgePaid' : 'badgePending']">
                {{ order.payment?.status === 'paid' ? 'Apmaksāts' : 'Nav apmaksāts' }}
              </span>
            </div>
            <div class="description">{{ order.problem_description || 'Apraksts nav norādīts.' }}</div>
          </div>
          <div class="rightValue">{{ formatMoney(order.final_cost) }}</div>
        </div>

        <div class="chips">
          <span class="chip">Klients: {{ order.user?.name || order.user_id }}</span>
          <span class="chip">Ierīce: {{ formatDevice(order.device) }}</span>
          <span class="chip">Filiāle: {{ order.branch?.name || order.branch_id }}</span>
          <span class="chip">Darbinieks: {{ order.assigned_staff?.name || 'nav piešķirts' }}</span>
          <span v-if="order.review" class="chip">Atsauksme: {{ starText(order.review.rating) }}</span>
        </div>

        <div class="chips">
          <RouterLink class="btn" :to="`/admin/orders/${order.id}`">Atvērt</RouterLink>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { authFetch, extractErrorMessage } from '../auth'
import { formatDevice } from '../deviceFormat'
import { statusLabel } from '../orderStatus'
import AccountMenu from './AccountMenu.vue'

const router = useRouter()
const orders = ref([])
const total = ref(0)
const loading = ref(false)
const error = ref('')

function formatMoney(value) {
  return `${Number(value || 0).toFixed(2)} €`
}

function starText(rating) {
  const value = Math.max(0, Math.min(5, Number(rating || 0)))
  return '★'.repeat(value)
}

async function loadOrders() {
  loading.value = true
  error.value = ''
  try {
    const response = await authFetch('/api/admin/orders')
    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await router.push({ path: '/login', query: { redirect: '/admin/orders' } })
        return
      }
      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt pasūtījumus.'))
    }
    const json = await response.json()
    orders.value = json.data ?? []
    total.value = json.total ?? orders.value.length
  } catch (e) {
    error.value = (e?.message || 'Neizdevās ielādēt pasūtījumus.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

onMounted(loadOrders)
</script>

<style scoped src="./adminPanel.css"></style>
