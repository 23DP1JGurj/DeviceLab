<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Jaunie pasūtījumi</h1>
        <div class="subtitle">Brīvie pasūtījumi, kurus vēl nav pieņēmis neviens darbinieks.</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/staff/orders/my">Mani pasūtījumi</RouterLink>
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div v-if="claimMessage" class="msg ok">{{ claimMessage }}</div>
    <div v-if="claimError" class="msg">{{ claimError }}</div>

    <div class="sectionHeader">
      <div>
        <div class="sectionTitle">Pieejamie pasūtījumi</div>
        <div class="muted">Pēc pieņemšanas pasūtījums pāries uz “Mani pasūtījumi”.</div>
      </div>
      <div class="countPill">{{ total }}</div>
    </div>

    <div v-if="loading" class="card">
      <div class="muted">Ielādējam pasūtījumus...</div>
    </div>

    <div v-else-if="listError" class="card">
      <div class="msg">{{ listError }}</div>
    </div>

    <div v-else-if="orders.length === 0" class="card">
      <div class="muted">Jaunu nepiešķirtu pasūtījumu nav.</div>
    </div>

    <div v-else class="orderStack">
      <article class="card orderCard" v-for="order in orders" :key="order.id">
        <div class="orderTop">
          <div class="orderMain">
            <div class="orderLine">
              <div class="orderNum">{{ order.order_number }}</div>
              <span class="badge" :class="'st_' + order.status">{{ order.status }}</span>
            </div>
            <div class="problemText">{{ order.problem_description || '—' }}</div>
          </div>

          <div class="cost">
            <div class="muted small">Galīgā summa</div>
            <div class="costValue">{{ order.final_cost != null ? formatMoney(order.final_cost) : '—' }}</div>
          </div>
        </div>

        <div class="chips">
          <span class="chip">Klients: {{ order.user?.name || order.user_id }}</span>
          <span class="chip">Filiāle: {{ order.branch?.name || order.branch_id }}</span>
          <span class="chip">Ierīce: {{ orderDeviceLabel(order.device) }}</span>
          <span class="chip chipUnassigned">Nav piešķirts</span>
        </div>

        <div class="itemsBlock">
          <div class="subTitle">Pozīcijas</div>
          <div class="itemList">
            <div class="itemLine" v-for="item in order.items" :key="item.id">
              <span class="itemKind">{{ item.item_type === 'service' ? 'pakalpojums' : 'detaļa' }}</span>
              <span class="itemName">{{ itemName(item) }}</span>
              <span class="itemPrice">{{ item.quantity }} × {{ formatMoney(item.unit_price) }}</span>
              <strong>{{ formatMoney(item.line_total) }}</strong>
            </div>
          </div>
        </div>

        <div class="actions">
          <button class="btn btnPrimary" type="button" @click="claimOrder(order.id)" :disabled="claimingId === order.id">
            {{ claimingId === order.id ? 'Pieņem...' : 'Pieņemt pasūtījumu' }}
          </button>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
import { authFetch, currentUser, extractErrorMessage, hasAnyRole, initAuth } from '../auth'
import { formatDevice } from '../deviceFormat'

const router = useRouter()
const orders = ref([])
const total = ref(0)
const loading = ref(false)
const listError = ref('')
const claimingId = ref(null)
const claimMessage = ref('')
const claimError = ref('')

function formatMoney(value) {
  const amount = Number(value || 0)
  return `${amount.toFixed(2)} €`
}

function orderDeviceLabel(device) {
  return formatDevice(device)
}

function itemName(item) {
  return item.item_type === 'service'
    ? (item.service?.name || `#${item.service_id}`)
    : (item.part?.name || `#${item.part_id}`)
}

async function redirectToLogin() {
  await router.push({ path: '/login', query: { redirect: '/staff/orders/new' } })
}

async function loadOrders() {
  loading.value = true
  listError.value = ''

  try {
    const response = await authFetch('/api/staff/orders/unassigned')

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectToLogin()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt jaunos pasūtījumus.'))
    }

    const json = await response.json()
    orders.value = json.data ?? []
    total.value = json.total ?? orders.value.length
  } catch (error) {
    orders.value = []
    total.value = 0
    listError.value = (error?.message || 'Neizdevās ielādēt jaunos pasūtījumus.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

async function claimOrder(id) {
  claimingId.value = id
  claimMessage.value = ''
  claimError.value = ''

  try {
    const response = await authFetch(`/api/staff/orders/${id}/claim`, {
      method: 'POST',
    })

    if (!response.ok) {
      if (response.status === 409) {
        throw new Error('Šo pasūtījumu jau pieņēma cits darbinieks.')
      }

      if (response.status === 401 || response.status === 403) {
        await redirectToLogin()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās pieņemt pasūtījumu.'))
    }

    const order = await response.json()
    claimMessage.value = `Pasūtījums ${order.order_number} pieņemts darbā.`
    await loadOrders()
  } catch (error) {
    claimError.value = (error?.message || 'Neizdevās pieņemt pasūtījumu.').slice(0, 260)
  } finally {
    claimingId.value = null
  }
}

onMounted(async () => {
  await initAuth().catch(() => null)

  if (!hasAnyRole(currentUser.value, ['staff', 'admin'])) {
    await redirectToLogin()
    return
  }

  await loadOrders()
})
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
.h1 { margin: 0; font-size: 34px; letter-spacing: 0; }
.subtitle { margin-top: 6px; color: #64748b; font-size: 14px; }
.topActions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.sectionHeader { align-items: flex-end; margin: 22px 0 12px; padding: 0 2px; }
.sectionTitle { font-size: 20px; font-weight: 900; }
.muted { color: #64748b; font-size: 14px; }
.small { font-size: 12px; }
.card { background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 18px; box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07); padding: 18px; }
.orderStack { display: grid; gap: 14px; }
.orderTop { align-items: flex-start; }
.orderMain { min-width: 0; }
.orderLine { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.orderNum { font-size: 20px; font-weight: 900; }
.problemText { margin-top: 8px; color: #475569; line-height: 1.5; }
.cost { text-align: right; }
.costValue { font-size: 24px; font-weight: 900; }
.badge, .chip, .countPill { border-radius: 999px; border: 1px solid rgba(15, 23, 42, 0.12); }
.badge { padding: 5px 10px; font-size: 12px; background: rgba(15, 23, 42, 0.04); }
.countPill { min-width: 34px; padding: 6px 12px; text-align: center; font-weight: 900; background: #fff; }
.st_new { background: rgba(37, 99, 235, 0.10); border-color: rgba(37, 99, 235, 0.22); }
.st_confirmed { background: rgba(16, 185, 129, 0.10); border-color: rgba(16, 185, 129, 0.22); }
.st_in_progress { background: rgba(245, 158, 11, 0.10); border-color: rgba(245, 158, 11, 0.24); }
.st_waiting_parts { background: rgba(139, 92, 246, 0.10); border-color: rgba(139, 92, 246, 0.24); }
.st_done { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.24); }
.st_cancelled { background: rgba(239, 68, 68, 0.10); border-color: rgba(239, 68, 68, 0.24); }
.chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.chip { padding: 5px 10px; font-size: 12px; color: #334155; background: rgba(148, 163, 184, 0.18); border-color: rgba(148, 163, 184, 0.30); }
.chipUnassigned { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
.itemsBlock { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(15, 23, 42, 0.08); }
.subTitle { font-weight: 900; }
.itemList { display: grid; gap: 8px; margin-top: 10px; }
.itemLine { display: grid; grid-template-columns: auto minmax(0, 1fr) auto auto; gap: 12px; align-items: center; padding: 10px 12px; border-radius: 14px; border: 1px solid rgba(148, 163, 184, 0.22); background: rgba(248, 250, 252, 0.9); }
.itemKind { color: #2563eb; font-size: 12px; font-weight: 900; }
.itemName { min-width: 0; font-weight: 800; }
.itemPrice { color: #64748b; }
.actions { display: flex; justify-content: flex-end; margin-top: 16px; }
.btn { border: 1px solid rgba(15, 23, 42, 0.14); background: #fff; color: #0f172a; padding: 10px 14px; border-radius: 12px; cursor: pointer; font-weight: 800; text-decoration: none; }
.btn:disabled { opacity: 0.65; cursor: not-allowed; }
.btnPrimary { background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border-color: rgba(29, 78, 216, 0.60); box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22); }
.btnGhost { background: transparent; }
.msg { margin-top: 10px; font-size: 13px; color: #b91c1c; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.18); padding: 9px 11px; border-radius: 12px; }
.msg.ok { color: #166534; background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.22); }

@media (max-width: 820px) {
  .topbar { align-items: flex-start; flex-direction: column; }
  .orderTop, .itemLine { grid-template-columns: 1fr; display: grid; }
  .cost { text-align: left; }
  .actions { justify-content: flex-start; }
}
</style>
