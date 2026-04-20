<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Pasūtījumu vēsture</h1>
        <div class="subtitle">Pabeigtie un atceltie pasūtījumi, ar kuriem strādāji.</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="sectionHeader">
      <div>
        <div class="sectionTitle">Vēsture</div>
        <div class="muted">Pārskati pabeigtos darbus, apmaksu un klientu atsauksmes.</div>
      </div>
      <div class="countPill">{{ total }}</div>
    </div>

    <div class="filterBar">
      <input class="control" v-model.trim="filters.search" type="search" placeholder="Meklēt pēc numura, klienta vai ierīces" />
      <select class="control" v-model="filters.status">
        <option value="">Visi statusi</option>
        <option value="done">Pabeigts</option>
        <option value="cancelled">Atcelts</option>
      </select>
      <select class="control" v-model="filters.payment_status">
        <option value="">Visa apmaksa</option>
        <option value="paid">Apmaksāts</option>
        <option value="unpaid">Gaida apmaksu</option>
      </select>
      <select class="control" v-model="filters.has_review">
        <option value="">Visas atsauksmes</option>
        <option value="1">Ar atsauksmi</option>
        <option value="0">Bez atsauksmes</option>
      </select>
      <select class="control" v-model="filters.sort">
        <option value="newest">Jaunākie</option>
        <option value="oldest">Vecākie</option>
      </select>
      <button class="btn btnSoft" type="button" @click="resetFilters">Atiestatīt</button>
    </div>

    <div v-if="loading" class="card stateCard">
      <div class="loadingBox">Ielādējam pasūtījumu vēsturi...</div>
    </div>

    <div v-else-if="listError" class="card stateCard">
      <div class="msg">{{ listError }}</div>
    </div>

    <div v-else-if="orders.length === 0" class="card stateCard">
      <div class="emptyTitle">{{ hasActiveFilters ? 'Pēc filtriem pasūtījumi netika atrasti.' : 'Vēsturē vēl nav pabeigtu pasūtījumu.' }}</div>
      <div class="muted" v-if="!hasActiveFilters">Kad pasūtījums būs pabeigts vai atcelts, tas parādīsies šeit.</div>
    </div>

    <div v-else class="orderStack">
      <article class="card orderCard" v-for="order in orders" :key="order.id">
        <div class="orderTop">
          <div class="orderMain">
            <div class="orderLine">
              <div class="orderNum">{{ order.order_number }}</div>
              <span class="badge" :class="'st_' + order.status">{{ statusLabel(order.status) }}</span>
              <span :class="['paymentBadge', isPaid(order) ? 'paid' : 'pending']">
                {{ isPaid(order) ? 'Apmaksāts' : 'Nav apmaksāts' }}
              </span>
            </div>
            <div class="problemText">{{ order.problem_description || 'Apraksts nav norādīts.' }}</div>
          </div>

          <div class="cost">
            <div class="muted small">Galīgā summa</div>
            <div class="costValue">{{ formatMoney(order.final_cost) }}</div>
          </div>
        </div>

        <div class="chips">
          <span class="chip">Klients: {{ order.user?.name || order.user_id }}</span>
          <span class="chip">Ierīce: {{ orderDeviceLabel(order.device) }}</span>
          <span class="chip">Filiāle: {{ order.branch?.name || order.branch_id }}</span>
          <span class="chip">Darbinieks: {{ order.assigned_staff?.name || 'nav piešķirts' }}</span>
          <span class="chip">Atjaunots: {{ formatDate(order.updated_at) }}</span>
        </div>

        <div v-if="order.review" class="reviewBox">
          <div>
            <div class="reviewTitle">Klienta atsauksme</div>
            <div class="stars">{{ starText(order.review.rating) }}</div>
          </div>
          <p v-if="order.review.comment">{{ order.review.comment }}</p>
        </div>

        <div class="actions">
          <RouterLink class="btn btnPrimary" :to="`/staff/orders/${order.id}`">Atvērt</RouterLink>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
import { authFetch, extractErrorMessage } from '../auth'
import { formatDevice } from '../deviceFormat'
import { statusLabel } from '../orderStatus'

const router = useRouter()
const orders = ref([])
const total = ref(0)
const loading = ref(false)
const listError = ref('')
const filters = reactive({
  search: '',
  status: '',
  payment_status: '',
  has_review: '',
  sort: 'newest',
})

const hasActiveFilters = computed(() => Boolean(
  filters.search || filters.status || filters.payment_status || filters.has_review || filters.sort !== 'newest',
))

function formatMoney(value) {
  return `${Number(value || 0).toFixed(2)} €`
}

function formatDate(value) {
  try {
    return new Date(value).toLocaleString()
  } catch {
    return value || '—'
  }
}

function orderDeviceLabel(device) {
  return formatDevice(device)
}

function isPaid(order) {
  return order.payment?.status === 'paid'
}

function starText(rating) {
  const value = Math.max(0, Math.min(5, Number(rating || 0)))
  return '★'.repeat(value) + '☆'.repeat(5 - value)
}

async function loadOrders() {
  loading.value = true
  listError.value = ''

  try {
    const params = new URLSearchParams()
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== '') params.set(key, value)
    })

    const query = params.toString()
    const response = await authFetch(`/api/staff/orders/history${query ? `?${query}` : ''}`)

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await router.push({ path: '/login', query: { redirect: '/staff/orders/history' } })
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt pasūtījumu vēsturi.'))
    }

    const json = await response.json()
    orders.value = json.data ?? []
    total.value = json.total ?? orders.value.length
  } catch (error) {
    orders.value = []
    total.value = 0
    listError.value = (error?.message || 'Neizdevās ielādēt pasūtījumu vēsturi.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

onMounted(loadOrders)

function resetFilters() {
  filters.search = ''
  filters.status = ''
  filters.payment_status = ''
  filters.has_review = ''
  filters.sort = 'newest'
}

let filterTimer = null
watch(filters, () => {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    loadOrders()
  }, 300)
}, { deep: true })
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue";
  background: radial-gradient(1200px 600px at 20% 0%, #eef6ff 0%, #f6f8fb 45%, #f7f7f7 100%);
  color: #0f172a;
}

.page { max-width: 1120px; margin: 0 auto; padding: 22px 18px 34px; }
.topbar, .sectionHeader, .orderTop { display: flex; justify-content: space-between; gap: 16px; }
.topbar { align-items: flex-end; margin-bottom: 16px; }
.h1 { margin: 0; font-size: 34px; letter-spacing: -0.02em; }
.subtitle { margin-top: 6px; color: #64748b; font-size: 14px; }
.topActions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.sectionHeader { align-items: flex-end; margin: 22px 0 12px; padding: 0 2px; }
.sectionTitle { font-size: 20px; font-weight: 900; }
.muted { color: #64748b; font-size: 14px; }
.small { font-size: 12px; }
.filterBar { display: grid; grid-template-columns: minmax(220px, 1fr) 150px 150px 160px 130px auto; gap: 10px; align-items: center; margin: 0 0 14px; }
.control { width: 100%; min-width: 0; padding: 11px 12px; border-radius: 14px; border: 1px solid rgba(15, 23, 42, 0.14); background: #fff; outline: none; font: inherit; }
.control:focus { border-color: rgba(37, 99, 235, 0.6); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12); }
.card { background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 18px; box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07); padding: 18px; }
.stateCard { padding: 22px; }
.emptyTitle { color: #071833; font-size: 18px; font-weight: 900; margin-bottom: 6px; }
.loadingBox { color: #64748b; }
.orderStack { display: grid; gap: 14px; }
.orderTop { align-items: flex-start; }
.orderMain { min-width: 0; }
.orderLine { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.orderNum { color: #071833; font-size: 21px; font-weight: 950; letter-spacing: -0.01em; }
.problemText { margin-top: 8px; color: #475569; line-height: 1.5; }
.cost { text-align: right; }
.costValue { color: #071833; font-size: 26px; font-weight: 950; }
.badge, .chip, .countPill, .paymentBadge { border-radius: 999px; border: 1px solid rgba(15, 23, 42, 0.12); }
.badge { padding: 5px 10px; font-size: 12px; font-weight: 900; background: rgba(15, 23, 42, 0.04); }
.paymentBadge { display: inline-flex; align-items: center; padding: 5px 10px; font-size: 12px; font-weight: 900; }
.paymentBadge.paid { color: #166534; background: #dcfce7; border-color: #bbf7d0; }
.paymentBadge.pending { color: #92400e; background: #fef3c7; border-color: #fde68a; }
.countPill { min-width: 34px; padding: 6px 12px; text-align: center; font-weight: 900; background: #fff; }
.chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.chip { padding: 5px 10px; font-size: 12px; color: #334155; background: rgba(148, 163, 184, 0.18); border-color: rgba(148, 163, 184, 0.30); }
.reviewBox { display: grid; gap: 10px; margin-top: 16px; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; }
.reviewTitle { color: #071833; font-weight: 900; }
.stars { margin-top: 4px; color: #f59e0b; font-size: 20px; letter-spacing: 1px; }
.reviewBox p { margin: 0; color: #334155; line-height: 1.55; }
.actions { display: flex; justify-content: flex-end; margin-top: 16px; }
.btn { border: 1px solid rgba(15, 23, 42, 0.14); background: #fff; color: #0f172a; padding: 10px 14px; border-radius: 12px; cursor: pointer; font-weight: 800; text-decoration: none; }
.btnPrimary { background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border-color: rgba(29, 78, 216, 0.60); box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22); }
.btnSoft { background: #f8fafc; }
.btnGhost { background: transparent; }
.msg { margin-top: 10px; font-size: 13px; color: #b91c1c; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.18); padding: 9px 11px; border-radius: 12px; }
.st_new { background: rgba(37, 99, 235, 0.10); border-color: rgba(37, 99, 235, 0.22); }
.st_confirmed { background: rgba(16, 185, 129, 0.10); border-color: rgba(16, 185, 129, 0.22); }
.st_diagnostics { background: rgba(14, 165, 233, 0.10); border-color: rgba(14, 165, 233, 0.24); }
.st_in_progress { background: rgba(245, 158, 11, 0.10); border-color: rgba(245, 158, 11, 0.24); }
.st_waiting_parts { background: rgba(139, 92, 246, 0.10); border-color: rgba(139, 92, 246, 0.24); }
.st_ready { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.24); }
.st_done { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.24); }
.st_cancelled { background: rgba(239, 68, 68, 0.10); border-color: rgba(239, 68, 68, 0.24); }

@media (max-width: 820px) {
  .topbar, .sectionHeader { align-items: flex-start; flex-direction: column; }
  .filterBar { grid-template-columns: 1fr; }
  .orderTop { display: grid; grid-template-columns: 1fr; }
  .cost { text-align: left; }
  .actions { justify-content: flex-start; }
}
</style>
