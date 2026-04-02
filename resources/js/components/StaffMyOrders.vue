<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Mani pasūtījumi</h1>
        <div class="subtitle">Pasūtījumi, kurus esi pieņēmis darbā.</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/staff/orders/new">Jaunie pasūtījumi</RouterLink>
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="sectionHeader">
      <div>
        <div class="sectionTitle">Darba uzdevumi</div>
        <div class="muted">Atjauno statusu, diagnozi un darba piezīmes.</div>
      </div>
      <div class="countPill">{{ total }}</div>
    </div>

    <div v-if="loading" class="card">
      <div class="muted">Ielādējam tavus pasūtījumus...</div>
    </div>

    <div v-else-if="listError" class="card">
      <div class="msg">{{ listError }}</div>
    </div>

    <div v-else-if="orders.length === 0" class="card">
      <div class="muted">Tev vēl nav piešķirtu pasūtījumu.</div>
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
          <span class="chip chipAssigned">Piešķirts: {{ order.assigned_staff?.name || '—' }}</span>
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

        <div class="workPanel">
          <div class="workHead">
            <div>
              <div class="subTitle">Darba statuss</div>
              <div class="muted">Saglabā apstrādes progresu un klientam redzamo statusu.</div>
            </div>
          </div>

          <div class="editGrid">
            <label class="field">
              <div class="label">Statuss</div>
              <select class="control" v-model="edit[order.id].status">
                <option value="new">new</option>
                <option value="confirmed">confirmed</option>
                <option value="in_progress">in_progress</option>
                <option value="waiting_parts">waiting_parts</option>
                <option value="done">done</option>
                <option value="cancelled">cancelled</option>
              </select>
            </label>

            <label class="field">
              <div class="label">Diagnoze</div>
              <textarea class="control textarea" v-model="edit[order.id].diagnosis" rows="3" />
            </label>

            <label class="field">
              <div class="label">Darba piezīmes</div>
              <textarea class="control textarea" v-model="edit[order.id].work_log" rows="3" />
            </label>
          </div>

          <div class="actions">
            <button class="btn btnPrimary" type="button" @click="saveOrder(order.id)" :disabled="savingId === order.id">
              {{ savingId === order.id ? 'Saglabā...' : 'Saglabāt' }}
            </button>

            <button class="btn btnDanger" type="button" @click="deleteOrder(order.id)" :disabled="savingId === order.id">
              Dzēst
            </button>

            <div class="msg" v-if="saveError && saveErrorId === order.id">{{ saveError }}</div>
            <div class="msg ok" v-else-if="saveOkId === order.id">Izmaiņas saglabātas</div>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
import { authFetch, currentUser, extractErrorMessage, hasAnyRole, initAuth } from '../auth'
import { formatDevice } from '../deviceFormat'

const router = useRouter()
const orders = ref([])
const total = ref(0)
const loading = ref(false)
const listError = ref('')
const savingId = ref(null)
const saveError = ref('')
const saveErrorId = ref(null)
const saveOkId = ref(null)
const edit = reactive({})

function ensureEdit(order) {
  if (!edit[order.id]) {
    edit[order.id] = {
      status: order.status || 'confirmed',
      diagnosis: order.diagnosis || '',
      work_log: order.work_log || '',
    }
    return
  }

  edit[order.id].status = order.status || edit[order.id].status
  edit[order.id].diagnosis = order.diagnosis || ''
  edit[order.id].work_log = order.work_log || ''
}

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
  await router.push({ path: '/login', query: { redirect: '/staff/orders/my' } })
}

async function loadOrders() {
  loading.value = true
  listError.value = ''

  try {
    const response = await authFetch('/api/staff/orders/my')

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectToLogin()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt tavus pasūtījumus.'))
    }

    const json = await response.json()
    orders.value = json.data ?? []
    total.value = json.total ?? orders.value.length
    orders.value.forEach(ensureEdit)
  } catch (error) {
    orders.value = []
    total.value = 0
    listError.value = (error?.message || 'Neizdevās ielādēt tavus pasūtījumus.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

async function saveOrder(id) {
  savingId.value = id
  saveError.value = ''
  saveErrorId.value = null
  saveOkId.value = null

  try {
    const response = await authFetch(`/api/staff/orders/${id}`, {
      method: 'PATCH',
      json: {
        status: edit[id].status,
        diagnosis: edit[id].diagnosis,
        work_log: edit[id].work_log,
      },
    })

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectToLogin()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās saglabāt pasūtījumu.'))
    }

    saveOkId.value = id
    await loadOrders()
    setTimeout(() => {
      if (saveOkId.value === id) saveOkId.value = null
    }, 1600)
  } catch (error) {
    saveError.value = (error?.message || 'Kļūda').slice(0, 260)
    saveErrorId.value = id
  } finally {
    savingId.value = null
  }
}

async function deleteOrder(id) {
  if (!confirm(`Dzēst pasūtījumu #${id}?`)) return

  savingId.value = id
  saveError.value = ''
  saveErrorId.value = null
  saveOkId.value = null

  try {
    const response = await authFetch(`/api/staff/orders/${id}`, {
      method: 'DELETE',
    })

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectToLogin()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās dzēst pasūtījumu.'))
    }

    await loadOrders()
  } catch (error) {
    saveError.value = (error?.message || 'Kļūda').slice(0, 260)
    saveErrorId.value = id
  } finally {
    savingId.value = null
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
.chipAssigned { background: #ecfdf5; border-color: #bbf7d0; color: #166534; }
.itemsBlock { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(15, 23, 42, 0.08); }
.subTitle { font-weight: 900; }
.itemList { display: grid; gap: 8px; margin-top: 10px; }
.itemLine { display: grid; grid-template-columns: auto minmax(0, 1fr) auto auto; gap: 12px; align-items: center; padding: 10px 12px; border-radius: 14px; border: 1px solid rgba(148, 163, 184, 0.22); background: rgba(248, 250, 252, 0.9); }
.itemKind { color: #2563eb; font-size: 12px; font-weight: 900; }
.itemName { min-width: 0; font-weight: 800; }
.itemPrice { color: #64748b; }
.workPanel { margin-top: 18px; padding: 18px; border: 1px solid rgba(148, 163, 184, 0.22); border-radius: 18px; background: #f8fafc; }
.workHead { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.editGrid { display: grid; grid-template-columns: minmax(160px, 220px) minmax(0, 1fr) minmax(0, 1fr); gap: 12px; }
.field { min-width: 0; }
.label { margin-bottom: 6px; color: #475569; font-size: 12px; font-weight: 800; }
.control { width: 100%; min-width: 0; padding: 11px 12px; border-radius: 14px; border: 1px solid rgba(15, 23, 42, 0.14); background: #fff; outline: none; font: inherit; }
.control:focus { border-color: rgba(37, 99, 235, 0.6); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12); }
.textarea { resize: vertical; min-height: 112px; line-height: 1.5; }
.actions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
.btn { border: 1px solid rgba(15, 23, 42, 0.14); background: #fff; color: #0f172a; padding: 10px 14px; border-radius: 12px; cursor: pointer; font-weight: 800; text-decoration: none; }
.btn:disabled { opacity: 0.65; cursor: not-allowed; }
.btnPrimary { background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border-color: rgba(29, 78, 216, 0.60); box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22); }
.btnGhost { background: transparent; }
.btnDanger { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
.msg { margin-top: 10px; font-size: 13px; color: #b91c1c; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.18); padding: 9px 11px; border-radius: 12px; }
.msg.ok { color: #166534; background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.22); }

@media (max-width: 920px) {
  .topbar { align-items: flex-start; flex-direction: column; }
  .orderTop, .itemLine { grid-template-columns: 1fr; display: grid; }
  .cost { text-align: left; }
  .editGrid { grid-template-columns: 1fr; }
}
</style>
