<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">My Orders</h1>
        <div class="subtitle">DeviceLab client panel</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Home</RouterLink>
        <button v-if="canAccessStaffPanel" class="btn btnSoft" type="button" @click="goToStaffPanel">Staff panel</button>
        <button class="btn btnSoft" type="button" @click="loadOrders" :disabled="listLoading">
          {{ listLoading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <div class="card">
      <div class="cardHead">
        <div>
          <div class="cardTitle">Create order</div>
        </div>

        <button class="btn btnSoft" type="button" @click="addItem">+ Add item</button>
      </div>

      <div class="grid2">
        <label class="field">
          <div class="label">Branch ID</div>
          <input class="control" v-model.number="form.branch_id" type="number" min="1" />
        </label>

        <label class="field">
          <div class="label">Device ID</div>
          <input class="control" v-model.number="form.device_id" type="number" min="1" />
        </label>
      </div>

      <label class="field mt12">
        <div class="label">Problem description</div>
        <input class="control" v-model="form.problem_description" type="text" />
      </label>

      <div class="mt16">
        <div class="sectionTitle">Items</div>

        <div class="items">
          <div class="itemRow" v-for="(it, idx) in form.items" :key="idx">
            <select class="control" v-model="it.item_type">
              <option value="service">service</option>
              <option value="part">part</option>
            </select>

            <input
              v-if="it.item_type === 'service'"
              class="control"
              v-model.number="it.service_id"
              type="number"
              min="1"
              placeholder="service_id"
            />
            <input
              v-else
              class="control"
              v-model.number="it.part_id"
              type="number"
              min="1"
              placeholder="part_id"
            />

            <input class="control" v-model.number="it.quantity" type="number" min="1" placeholder="qty" />

            <button class="btnIcon btnDangerSoft" type="button" @click="removeItem(idx)" title="Remove">
              ✕
            </button>
          </div>
        </div>

        <div class="mt14 row">
          <button class="btn btnPrimary" type="button" @click="createOrder" :disabled="creating">
            {{ creating ? 'Creating...' : 'Create order' }}
          </button>

          <div class="msg" v-if="createError">{{ createError }}</div>
          <div class="msg ok" v-else-if="createSuccess">{{ createSuccess }}</div>
        </div>
      </div>
    </div>

    <div class="sectionHeader">
      <div class="sectionTitle">My orders</div>
      <div class="muted">Total: {{ total }}</div>
    </div>

    <div v-if="listLoading" class="card">
      <div class="muted">Loading...</div>
    </div>

    <div v-else>
      <div v-if="listError" class="card">
        <div class="msg">{{ listError }}</div>
      </div>

      <div v-else-if="orders.length === 0" class="card">
        <div class="muted">No orders yet.</div>
      </div>

      <div class="card orderCard" v-for="o in orders" :key="o.id">
        <div class="orderTop">
          <div class="orderMain">
            <div class="orderLine">
              <div class="orderNum">{{ o.order_number }}</div>
              <span class="badge" :class="'st_' + o.status">{{ o.status }}</span>
            </div>

            <div class="muted">{{ o.problem_description || '—' }}</div>

            <div class="chips">
              <span class="chip">ID: {{ o.id }}</span>
              <span class="chip">Branch: {{ o.branch_id }}</span>
              <span class="chip">Device: {{ o.device_id }}</span>
              <span class="chip">Created: {{ formatDate(o.created_at) }}</span>
            </div>
          </div>

          <div class="cost">
            <div class="muted small">Final cost</div>
            <div class="costValue">{{ o.final_cost ?? '—' }}</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="itemsBlock">
          <div class="subTitle">Items</div>
          <ul class="itemsList">
            <li v-for="it in o.items" :key="it.id">
              <template v-if="it.item_type === 'service'">
                <b>service:</b> {{ it.service?.name || ('#' + it.service_id) }}
              </template>
              <template v-else>
                <b>part:</b> {{ it.part?.name || ('#' + it.part_id) }}
              </template>
              — {{ it.quantity }} × {{ it.unit_price }} = <b>{{ it.line_total }}</b>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="spacer"></div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { authFetch, currentUser, extractErrorMessage, hasAnyRole, initAuth } from '../auth'

const ORDER_DRAFT_STORAGE_KEY = 'devicelab:orderDraft:v1'

const router = useRouter()
const canAccessStaffPanel = hasAnyRole(currentUser.value, ['staff', 'admin'])

const orders = ref([])
const total = ref(0)
const listLoading = ref(false)
const listError = ref('')

const creating = ref(false)
const createError = ref('')
const createSuccess = ref('')

const form = reactive({
  branch_id: 1,
  device_id: 1,
  problem_description: 'Neieslēdzas',
  items: [
    { item_type: 'service', service_id: 1, part_id: null, quantity: 1 },
    { item_type: 'part', service_id: null, part_id: 1, quantity: 1 },
  ],
})

const OFFICE_TO_BRANCH_ID = {
  'riga-centrs': 1,
  'riga-center': 1,
  centrs: 1,
  center: 1,
  'riga-purvciems': 2,
  purvciems: 2,
  'riga-imanta': 2,
  imanta: 2,
}

const DEVICE_TYPE_TO_ID = {
  phone: 1,
}

function goToStaffPanel() {
  router.push('/staff/orders')
}

function normalizeDraftValue(value) {
  return String(value || '').trim().toLowerCase()
}

function resolveBranchIdFromOffice(office) {
  const normalized = normalizeDraftValue(office)
  return OFFICE_TO_BRANCH_ID[normalized] ?? null
}

function resolveDeviceIdFromDraft(deviceType) {
  const normalized = normalizeDraftValue(deviceType)
  return DEVICE_TYPE_TO_ID[normalized] ?? null
}

function applyDraft() {
  const raw = localStorage.getItem(ORDER_DRAFT_STORAGE_KEY)
  if (!raw) return

  try {
    const draft = JSON.parse(raw)

    if (typeof draft?.problem_description === 'string' && draft.problem_description.trim()) {
      form.problem_description = draft.problem_description.trim()
    }

    const branchId = resolveBranchIdFromOffice(draft?.office)
    if (branchId) {
      form.branch_id = branchId
    }

    if (draft?.device_id) {
      form.device_id = Number(draft.device_id)
    } else {
      const deviceId = resolveDeviceIdFromDraft(draft?.device_type)
      if (deviceId) form.device_id = deviceId
    }
  } catch {
    localStorage.removeItem(ORDER_DRAFT_STORAGE_KEY)
  }
}

function addItem() {
  form.items.push({ item_type: 'service', service_id: 1, part_id: null, quantity: 1 })
}

function removeItem(idx) {
  form.items.splice(idx, 1)
}

function formatDate(s) {
  try { return new Date(s).toLocaleString() } catch { return s }
}

async function loadOrders() {
  listLoading.value = true
  listError.value = ''
  try {
    const res = await authFetch('/api/client/orders')

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/orders' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Unable to load orders.'))
    }

    const json = await res.json()
    orders.value = json.data ?? []
    total.value = json.total ?? orders.value.length
  } catch (e) {
    orders.value = []
    total.value = 0
    listError.value = (e?.message || 'Unable to load orders.').slice(0, 260)
  } finally {
    listLoading.value = false
  }
}

async function createOrder() {
  creating.value = true
  createError.value = ''
  createSuccess.value = ''

  try {
    const payload = {
      branch_id: form.branch_id,
      device_id: form.device_id,
      problem_description: form.problem_description,
      items: form.items.map(item => ({
        item_type: item.item_type,
        service_id: item.item_type === 'service' ? item.service_id : null,
        part_id: item.item_type === 'part' ? item.part_id : null,
        quantity: item.quantity,
      })),
    }

    const res = await authFetch('/api/orders', {
      method: 'POST',
      json: payload,
    })

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/orders' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Unable to create order.'))
    }

    const created = await res.json()
    localStorage.removeItem(ORDER_DRAFT_STORAGE_KEY)
    createSuccess.value = `Created: ${created.order_number}`
    await loadOrders()
  } catch (e) {
    createError.value = (e?.message || 'Error').slice(0, 260)
  } finally {
    creating.value = false
  }
}

onMounted(async () => {
  await initAuth().catch(() => null)
  applyDraft()
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

.page {
  max-width: 980px;
  margin: 0 auto;
  padding: 22px 18px 34px;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 14px;
}

.titleBlock { min-width: 0; }
.h1 {
  margin: 0;
  font-size: 34px;
  letter-spacing: -0.02em;
}
.subtitle {
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
}

.topActions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.card {
  background: rgba(255, 255, 255, 0.92);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 16px;
  box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
  padding: 16px;
  margin-top: 14px;
}

.cardHead {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
}

.cardTitle {
  font-weight: 800;
  font-size: 16px;
}

.sectionHeader {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-top: 18px;
  padding: 0 2px;
}
.sectionTitle {
  font-size: 16px;
  font-weight: 800;
}

.field { min-width: 0; }
.label {
  font-size: 12px;
  color: #475569;
  margin-bottom: 6px;
}
.control {
  width: 100%;
  min-width: 0;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  outline: none;
}
.control:focus {
  border-color: rgba(37, 99, 235, 0.6);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.grid2 {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 12px;
}
.grid2 > * { min-width: 0; }

.items { display: grid; gap: 10px; margin-top: 10px; }
.itemRow {
  display: grid;
  grid-template-columns: minmax(120px, 160px) minmax(0, 1fr) minmax(90px, 120px) 44px;
  gap: 10px;
  align-items: center;
}
.itemRow > * { min-width: 0; }

.orderCard { padding: 16px; }
.orderTop {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(160px, 220px);
  gap: 14px;
  align-items: start;
}
.orderMain { min-width: 0; }
.orderLine {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}
.orderNum { font-weight: 900; font-size: 18px; }

.cost { text-align: right; }
.costValue { font-size: 22px; font-weight: 900; letter-spacing: -0.01em; }

.badge {
  font-size: 12px;
  padding: 5px 10px;
  border-radius: 999px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: rgba(15, 23, 42, 0.04);
}
.st_new { background: rgba(37, 99, 235, 0.10); border-color: rgba(37, 99, 235, 0.22); }
.st_confirmed { background: rgba(16, 185, 129, 0.10); border-color: rgba(16, 185, 129, 0.22); }
.st_in_progress { background: rgba(245, 158, 11, 0.10); border-color: rgba(245, 158, 11, 0.24); }
.st_waiting_parts { background: rgba(139, 92, 246, 0.10); border-color: rgba(139, 92, 246, 0.24); }
.st_done { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.24); }
.st_cancelled { background: rgba(239, 68, 68, 0.10); border-color: rgba(239, 68, 68, 0.24); }

.chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.chip {
  font-size: 12px;
  color: #334155;
  background: rgba(148, 163, 184, 0.18);
  border: 1px solid rgba(148, 163, 184, 0.30);
  padding: 5px 10px;
  border-radius: 999px;
}

.divider {
  height: 1px;
  background: rgba(15, 23, 42, 0.08);
  margin: 14px 0;
}

.subTitle { font-weight: 800; margin-bottom: 8px; }
.itemsList { margin: 0; padding-left: 18px; color: #0f172a; }
.itemsList li { margin-bottom: 4px; }

.btn {
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  color: #0f172a;
  padding: 10px 14px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 700;
}
.btn:disabled { opacity: 0.65; cursor: not-allowed; }

.btnPrimary {
  background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
  color: #fff;
  border-color: rgba(29, 78, 216, 0.60);
  box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22);
}

.btnSoft {
  background: rgba(15, 23, 42, 0.04);
}
.btnGhost {
  background: transparent;
}

.btnIcon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: rgba(15, 23, 42, 0.04);
  cursor: pointer;
  font-weight: 900;
}
.btnDangerSoft {
  background: rgba(239, 68, 68, 0.10);
  border-color: rgba(239, 68, 68, 0.22);
  color: #b91c1c;
}

.row {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.msg {
  font-size: 13px;
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.18);
  padding: 8px 10px;
  border-radius: 12px;
}
.msg.ok {
  color: #166534;
  background: rgba(34, 197, 94, 0.10);
  border-color: rgba(34, 197, 94, 0.22);
}

.muted { color: #64748b; }
.small { font-size: 12px; }

.mt12 { margin-top: 12px; }
.mt14 { margin-top: 14px; }
.mt16 { margin-top: 16px; }
.spacer { height: 10px; }

@media (max-width: 920px) {
  .topbar { align-items: flex-start; flex-direction: column; }
  .orderTop { grid-template-columns: 1fr; }
  .cost { text-align: left; }
  .grid2 { grid-template-columns: 1fr; }
  .itemRow { grid-template-columns: 1fr; }
}
</style>
