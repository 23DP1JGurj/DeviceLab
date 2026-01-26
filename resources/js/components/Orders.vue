<template>
  <div class="page">
    <!-- Header -->
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Orders</h1>
        <div class="subtitle">DeviceLab demo panel</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Home</RouterLink>
        <button class="btn btnSoft" type="button" @click="loadOrders" :disabled="listLoading">
          {{ listLoading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>
    </div>

    <!-- Create Order -->
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
              class="control"
              v-if="it.item_type === 'service'"
              v-model.number="it.service_id"
              type="number"
              min="1"
              placeholder="service_id"
            />
            <input
              class="control"
              v-else
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

    <!-- Filters -->
    <div class="card">
      <div class="cardHead">
        <div>
          <div class="cardTitle">Filters</div>
        </div>

        <div class="row">
          <button class="btn btnGhost" type="button" @click="resetFilters">Reset</button>
          <button class="btn btnSoft" type="button" @click="loadOrders">Refresh</button>
        </div>
      </div>

      <div class="gridFilters">
        <label class="field">
          <div class="label">Search (order number)</div>
          <input class="control" v-model.trim="filters.search" type="text" placeholder="DL-2026..." />
        </label>

        <label class="field">
          <div class="label">Status</div>
          <select class="control" v-model="filters.status">
            <option value="">All</option>
            <option value="new">new</option>
            <option value="confirmed">confirmed</option>
            <option value="in_progress">in_progress</option>
            <option value="waiting_parts">waiting_parts</option>
            <option value="done">done</option>
            <option value="cancelled">cancelled</option>
          </select>
        </label>

        <label class="field">
          <div class="label">Branch ID</div>
          <input class="control" v-model.number="filters.branch_id" type="number" min="1" placeholder="1" />
        </label>
      </div>
    </div>

    <!-- Orders list -->
    <div class="sectionHeader">
      <div class="sectionTitle">Orders list</div>
      <div class="muted">Total: {{ total }}</div>
    </div>

    <div v-if="listLoading" class="card">
      <div class="muted">Loading...</div>
    </div>

    <div v-else>
      <div v-if="orders.length === 0" class="card">
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

        <div class="divider"></div>

        <div class="editBlock">
          <div class="subTitle">Update</div>

          <div class="gridEdit">
            <label class="field">
              <div class="label">Status</div>
              <select class="control" v-model="edit[o.id].status">
                <option value="new">new</option>
                <option value="confirmed">confirmed</option>
                <option value="in_progress">in_progress</option>
                <option value="waiting_parts">waiting_parts</option>
                <option value="done">done</option>
                <option value="cancelled">cancelled</option>
              </select>
            </label>

            <label class="field">
              <div class="label">Diagnosis</div>
              <textarea class="control textarea" v-model="edit[o.id].diagnosis" rows="2" />
            </label>

            <label class="field">
              <div class="label">Work log</div>
              <textarea class="control textarea" v-model="edit[o.id].work_log" rows="2" />
            </label>
          </div>

          <div class="row mt12">
            <button
              class="btn btnPrimary"
              type="button"
              @click="saveOrder(o.id)"
              :disabled="savingId === o.id"
            >
              {{ savingId === o.id ? 'Saving...' : 'Save' }}
            </button>

            <button class="btn btnDanger" type="button" @click="deleteOrder(o.id)" :disabled="savingId === o.id">
              Delete
            </button>

            <div class="msg" v-if="saveError && saveErrorId === o.id">{{ saveError }}</div>
            <div class="msg ok" v-else-if="saveOkId === o.id">Saved ✅</div>
          </div>
        </div>
      </div>
    </div>

    <div class="spacer"></div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'

const orders = ref([])
const total = ref(0)
const listLoading = ref(false)

const creating = ref(false)
const createError = ref('')
const createSuccess = ref('')

const savingId = ref(null)
const saveError = ref('')
const saveErrorId = ref(null)
const saveOkId = ref(null)

const form = reactive({
  branch_id: 1,
  device_id: 1,
  problem_description: 'Neieslēdzas',
  items: [
    { item_type: 'service', service_id: 1, part_id: null, quantity: 1 },
    { item_type: 'part', service_id: null, part_id: 1, quantity: 1 },
  ],
})

const filters = reactive({
  search: '',
  status: '',
  branch_id: 1,
})

// edit state per order (status/diagnosis/work_log)
const edit = reactive({})

function ensureEdit(order) {
  if (!edit[order.id]) {
    edit[order.id] = {
      status: order.status || 'new',
      diagnosis: order.diagnosis || '',
      work_log: order.work_log || '',
    }
  } else {
    // keep in sync when list reloads
    edit[order.id].status = order.status || edit[order.id].status
    edit[order.id].diagnosis = order.diagnosis || ''
    edit[order.id].work_log = order.work_log || ''
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

function buildOrdersUrl() {
  const params = new URLSearchParams()

  if (filters.search) params.set('search', filters.search)
  if (filters.status) params.set('status', filters.status)
  if (filters.branch_id) params.set('branch_id', String(filters.branch_id))

  const qs = params.toString()
  return qs ? `/api/orders?${qs}` : '/api/orders'
}

async function loadOrders() {
  listLoading.value = true
  try {
    const res = await fetch(buildOrdersUrl())
    const json = await res.json()
    orders.value = json.data ?? []
    total.value = json.total ?? orders.value.length

    for (const o of orders.value) ensureEdit(o)
  } finally {
    listLoading.value = false
  }
}

function resetFilters() {
  filters.search = ''
  filters.status = ''
  filters.branch_id = 1
  loadOrders()
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
      items: form.items.map(i => ({
        item_type: i.item_type,
        service_id: i.item_type === 'service' ? i.service_id : null,
        part_id: i.item_type === 'part' ? i.part_id : null,
        quantity: i.quantity,
      })),
    }

    const res = await fetch('/api/orders', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })

    if (!res.ok) {
      const txt = await res.text()
      throw new Error(txt)
    }

    const created = await res.json()
    createSuccess.value = `Created: ${created.order_number}`
    await loadOrders()
  } catch (e) {
    createError.value = (e?.message || 'Error').slice(0, 260)
  } finally {
    creating.value = false
  }
}

async function saveOrder(id) {
  savingId.value = id
  saveError.value = ''
  saveErrorId.value = null
  saveOkId.value = null

  try {
    const payload = {
      status: edit[id].status,
      diagnosis: edit[id].diagnosis,
      work_log: edit[id].work_log,
    }

    const res = await fetch(`/api/orders/${id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })

    if (!res.ok) {
      const txt = await res.text()
      throw new Error(txt)
    }

    saveOkId.value = id
    await loadOrders()
    setTimeout(() => { if (saveOkId.value === id) saveOkId.value = null }, 1200)
  } catch (e) {
    saveError.value = (e?.message || 'Error').slice(0, 260)
    saveErrorId.value = id
  } finally {
    savingId.value = null
  }
}

async function deleteOrder(id) {
  if (!confirm(`Delete order #${id}?`)) return

  savingId.value = id
  saveError.value = ''
  saveErrorId.value = null
  saveOkId.value = null

  try {
    const res = await fetch(`/api/orders/${id}`, { method: 'DELETE' })
    if (!res.ok) {
      const txt = await res.text()
      throw new Error(txt)
    }
    await loadOrders()
  } catch (e) {
    saveError.value = (e?.message || 'Error').slice(0, 260)
    saveErrorId.value = id
  } finally {
    savingId.value = null
  }
}

onMounted(loadOrders)
</script>

<style scoped>
/* IMPORTANT: fixes "вылазит из бокса" в grid */
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
.cardHint {
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
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
.textarea { resize: vertical; }

.grid2 {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 12px;
}
.grid2 > * { min-width: 0; }

.gridFilters {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(180px, 240px) minmax(140px, 220px);
  gap: 12px;
}
.gridFilters > * { min-width: 0; }

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

.gridEdit {
  display: grid;
  grid-template-columns: minmax(160px, 220px) minmax(0, 1fr) minmax(0, 1fr);
  gap: 12px;
}
.gridEdit > * { min-width: 0; }

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
.btnPrimary:hover { filter: brightness(1.02); }

.btnSoft {
  background: rgba(15, 23, 42, 0.04);
}
.btnGhost {
  background: transparent;
}

.btnDanger {
  background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
  color: #fff;
  border-color: rgba(220, 38, 38, 0.60);
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

/* Responsive */
@media (max-width: 920px) {
  .topbar { align-items: flex-start; flex-direction: column; }
  .orderTop { grid-template-columns: 1fr; }
  .cost { text-align: left; }
  .grid2 { grid-template-columns: 1fr; }
  .gridFilters { grid-template-columns: 1fr; }
  .gridEdit { grid-template-columns: 1fr; }
  .itemRow { grid-template-columns: 1fr; }
}
</style>
