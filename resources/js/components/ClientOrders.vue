<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Mani pasūtījumi</h1>
        <div class="subtitle">DeviceLab klienta panelis</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="card">
      <div class="cardHead">
        <div>
          <div class="cardTitle">Jauns pieteikums</div>
          <div class="cardSubtitle">Izvēlies filiāli, ierīci un pievieno pozīcijas bez manuālas ID ievades.</div>
        </div>

        <button class="btn btnSoft" type="button" @click="addItem">+ Pievienot pozīciju</button>
      </div>

      <div v-if="metaLoading" class="loadingBox">Ielādējam filiāles, ierīces un cenu katalogu...</div>

      <div class="grid2">
        <label class="field">
          <div class="label">Filiāle</div>
          <select class="control" v-model.number="form.branch_id" :disabled="metaLoading || branches.length === 0">
            <option value="" disabled hidden>Izvēlies filiāli</option>
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
              {{ branch.name }}{{ branch.address ? ` — ${branch.address}` : '' }}
            </option>
          </select>
        </label>

        <label class="field">
          <div class="label">Ierīce</div>
          <select class="control" v-model="deviceSelectValue" :disabled="metaLoading">
            <option value="" disabled hidden>Izvēlies ierīci</option>
            <option v-for="device in devices" :key="device.id" :value="device.id">
              {{ formatDeviceLabel(device) }}
            </option>
            <option :value="ADD_DEVICE_OPTION">+ Pievienot ierīci</option>
          </select>
        </label>
      </div>

      <div v-if="metaError" class="msg mt12">{{ metaError }}</div>

      <label class="field mt12">
        <div class="label">Problēmas apraksts</div>
        <textarea class="control textarea" v-model="form.problem_description" rows="3"></textarea>
      </label>

      <div class="mt18">
        <div class="sectionTitle">Pozīcijas</div>

        <div v-if="form.items.length === 0" class="emptyHint mt12">
          Pievieno vismaz vienu pakalpojumu vai detaļu.
        </div>

        <div class="items">
          <div class="itemRow" v-for="(item, index) in form.items" :key="index">
            <select class="control" v-model="item.item_type" @change="syncItemSelection(item)">
              <option value="service">Pakalpojums</option>
              <option value="part">Detaļa</option>
            </select>

            <select
              v-if="item.item_type === 'service'"
              class="control"
              v-model.number="item.service_id"
              :disabled="services.length === 0"
            >
              <option value="" disabled hidden>Izvēlies pakalpojumu</option>
              <option v-for="service in services" :key="service.id" :value="service.id">
                {{ service.name }} — {{ formatMoney(service.base_price) }}
              </option>
            </select>

            <select
              v-else
              class="control"
              v-model.number="item.part_id"
              :disabled="parts.length === 0"
            >
              <option value="" disabled hidden>Izvēlies detaļu</option>
              <option v-for="part in parts" :key="part.id" :value="part.id">
                {{ part.name }} — {{ formatMoney(part.unit_price) }}
              </option>
            </select>

            <input class="control qtyControl" v-model.number="item.quantity" type="number" min="1" placeholder="Daudzums" />

            <button class="btnIcon btnDangerSoft" type="button" @click="removeItem(index)" title="Dzēst">×</button>
          </div>
        </div>

        <div class="summaryRow mt14">
          <div class="muted">Aptuvenā summa: <b>{{ formatMoney(draftTotal) }}</b></div>
        </div>

        <div class="row mt14">
          <button
            class="btn btnPrimary"
            type="button"
            @click="createOrder"
            :disabled="creating || metaLoading || !form.branch_id || !form.device_id || form.items.length === 0"
          >
            {{ creating ? 'Veido...' : 'Izveidot pieteikumu' }}
          </button>

          <div class="msg" v-if="createError">{{ createError }}</div>
          <div class="msg ok" v-else-if="createSuccess">{{ createSuccess }}</div>
        </div>
      </div>
    </div>

    <div class="sectionHeader">
        <div class="sectionTitle">Mani pasūtījumi</div>
        <div class="muted">Kopā: {{ total }}</div>
      </div>

    <div v-if="listLoading" class="card">
      <div class="loadingBox">Ielādējam pasūtījumus...</div>
    </div>

    <div v-else>
      <div v-if="listError" class="card">
        <div class="msg">{{ listError }}</div>
      </div>

      <div v-else-if="orders.length === 0" class="card">
        <div class="muted">Pasūtījumu vēl nav.</div>
      </div>

      <div class="card orderCard" v-for="order in orders" :key="order.id">
        <div class="orderTop">
          <div class="orderMain">
            <div class="orderLine">
              <div class="orderNum">{{ order.order_number }}</div>
              <span class="badge" :class="'st_' + order.status">{{ order.status }}</span>
            </div>

            <div class="muted">{{ order.problem_description || '—' }}</div>

            <div class="chips">
              <span class="chip">Filiāle: {{ order.branch?.name || order.branch_id }}</span>
              <span class="chip">Ierīce: {{ orderDeviceLabel(order.device) }}</span>
              <span class="chip">Izveidots: {{ formatDate(order.created_at) }}</span>
            </div>
          </div>

          <div class="cost">
            <div class="muted small">Galīgā summa</div>
            <div class="costValue">{{ order.final_cost != null ? formatMoney(order.final_cost) : '—' }}</div>
          </div>
        </div>

        <div class="divider"></div>

        <div class="itemsBlock">
          <div class="subTitle">Pozīcijas</div>
          <ul class="itemsList">
            <li v-for="item in order.items" :key="item.id">
              <template v-if="item.item_type === 'service'">
                <b>pakalpojums:</b> {{ item.service?.name || ('#' + item.service_id) }}
              </template>
              <template v-else>
                <b>detaļa:</b> {{ item.part?.name || ('#' + item.part_id) }}
              </template>
              — {{ item.quantity }} × {{ formatMoney(item.unit_price) }} = <b>{{ formatMoney(item.line_total) }}</b>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div v-if="isDeviceModalOpen" class="modalOverlay" @click.self="closeDeviceModal">
      <div class="modalCard">
        <div class="cardHead">
          <div>
            <div class="cardTitle">Pievienot ierīci</div>
            <div class="cardSubtitle">Saglabā ierīci un uzreiz izvēlies to jaunajam pieteikumam.</div>
          </div>
        </div>

        <label class="field">
          <div class="label">Tips</div>
          <select class="control" v-model="deviceForm.type">
            <option value="phone">Telefons</option>
            <option value="laptop">Portatīvais dators</option>
            <option value="tablet">Planšete</option>
            <option value="other">Cits</option>
          </select>
        </label>

        <div class="grid2 mt12">
          <label class="field">
            <div class="label">Zīmols</div>
            <input class="control" v-model.trim="deviceForm.brand" type="text" />
          </label>

          <label class="field">
            <div class="label">Modelis</div>
            <input class="control" v-model.trim="deviceForm.model" type="text" />
          </label>
        </div>

        <label class="field mt12">
          <div class="label">Sērijas numurs</div>
          <input class="control" v-model.trim="deviceForm.serial_number" type="text" />
        </label>

        <div v-if="deviceErrors.length > 0" class="msg mt12">
          <div v-for="(error, index) in deviceErrors" :key="index">{{ error }}</div>
        </div>

        <div class="row mt16">
          <button class="btn" type="button" @click="closeDeviceModal" :disabled="deviceSaving">Atcelt</button>
          <button class="btn btnPrimary" type="button" @click="saveDevice" :disabled="deviceSaving">
            {{ deviceSaving ? 'Saglabā...' : 'Saglabāt' }}
          </button>
        </div>
      </div>
    </div>

    <div class="spacer"></div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
import { authFetch, currentUser, extractErrorMessage, hasAnyRole, initAuth } from '../auth'

const ORDER_DRAFT_STORAGE_KEY = 'devicelab:orderDraft:v1'
const ADD_DEVICE_OPTION = '__add_device__'

const router = useRouter()

const branches = ref([])
const services = ref([])
const parts = ref([])
const devices = ref([])
const orders = ref([])

const total = ref(0)
const listLoading = ref(false)
const listError = ref('')
const metaLoading = ref(false)
const metaError = ref('')

const creating = ref(false)
const createError = ref('')
const createSuccess = ref('')

const isDeviceModalOpen = ref(false)
const deviceSaving = ref(false)
const deviceErrors = ref([])
const deviceSelectValue = computed({
  get() {
    return form.device_id ? String(form.device_id) : ''
  },
  set(value) {
    if (value === ADD_DEVICE_OPTION) {
      openDeviceModal()
      return
    }

    form.device_id = value ? Number(value) : null
  },
})

const form = reactive({
  branch_id: null,
  device_id: null,
  problem_description: '',
  items: [],
})

const deviceForm = reactive({
  type: 'phone',
  brand: '',
  model: '',
  serial_number: '',
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

const draftTotal = computed(() => form.items.reduce((sum, item) => {
  const unitPrice = item.item_type === 'service'
    ? Number(services.value.find(service => service.id === Number(item.service_id))?.base_price || 0)
    : Number(parts.value.find(part => part.id === Number(item.part_id))?.unit_price || 0)

  return sum + (unitPrice * Number(item.quantity || 0))
}, 0))

function formatMoney(value) {
  const amount = Number(value || 0)
  return `${amount.toFixed(2)} €`
}

function formatDate(value) {
  try {
    return new Date(value).toLocaleString()
  } catch {
    return value
  }
}

function formatDeviceLabel(device) {
  if (!device) return '—'

  const parts = [device.brand, device.model].filter(Boolean)
  const title = parts.join(' ')
  return title ? `${title} (${device.type})` : `IerД«ce #${device.id}`
}

function orderDeviceLabel(device) {
  return device ? formatDeviceLabel(device) : 'вЂ”'
}

function defaultItemType() {
  if (services.value.length > 0) return 'service'
  if (parts.value.length > 0) return 'part'
  return 'service'
}

function syncItemSelection(item) {
  if (item.item_type === 'service') {
    item.part_id = null
    item.service_id = services.value.some(service => service.id === Number(item.service_id))
      ? Number(item.service_id)
      : (services.value[0]?.id ?? null)
    return
  }

  item.service_id = null
  item.part_id = parts.value.some(part => part.id === Number(item.part_id))
    ? Number(item.part_id)
    : (parts.value[0]?.id ?? null)
}

function ensureItems() {
  if (form.items.length === 0) {
    addItem(defaultItemType())
    return
  }

  form.items.forEach(syncItemSelection)
}

function addItem(itemType = defaultItemType()) {
  const item = {
    item_type: itemType,
    service_id: null,
    part_id: null,
    quantity: 1,
  }

  syncItemSelection(item)
  form.items.push(item)
}

function removeItem(index) {
  form.items.splice(index, 1)
}

async function redirectByRole() {
  if (hasAnyRole(currentUser.value, ['staff', 'admin'])) {
    await router.replace('/staff/orders')
    return
  }

  await router.replace({ path: '/login', query: { redirect: '/orders' } })
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

  if (!normalized) return null

  const matchedDevice = devices.value.find(device => normalizeDraftValue(device.type) === normalized)
  return matchedDevice?.id ?? null
}

function syncSelectedBranch(preferredId = null) {
  if (branches.value.length === 0) {
    form.branch_id = null
    return
  }

  const selectedId = preferredId ?? form.branch_id
  const exists = branches.value.some(branch => branch.id === Number(selectedId))
  form.branch_id = exists ? Number(selectedId) : branches.value[0].id
}

function syncSelectedDevice(preferredId = null) {
  if (devices.value.length === 0) {
    form.device_id = null
    return
  }

  const selectedId = preferredId ?? form.device_id
  const exists = devices.value.some(device => device.id === Number(selectedId))
  form.device_id = exists ? Number(selectedId) : devices.value[0].id
}

function applyDraft() {
  const raw = localStorage.getItem(ORDER_DRAFT_STORAGE_KEY)
  if (!raw) {
    syncSelectedBranch()
    syncSelectedDevice()
    return
  }

  try {
    const draft = JSON.parse(raw)

    if (typeof draft?.problem_description === 'string' && draft.problem_description.trim()) {
      form.problem_description = draft.problem_description.trim()
    }

    syncSelectedBranch(resolveBranchIdFromOffice(draft?.office))

    if (draft?.device_id) {
      syncSelectedDevice(Number(draft.device_id))
    } else {
      syncSelectedDevice(resolveDeviceIdFromDraft(draft?.device_type))
    }
  } catch {
    localStorage.removeItem(ORDER_DRAFT_STORAGE_KEY)
    syncSelectedBranch()
    syncSelectedDevice()
  }
}

function resetDeviceForm() {
  deviceForm.type = 'phone'
  deviceForm.brand = ''
  deviceForm.model = ''
  deviceForm.serial_number = ''
  deviceErrors.value = []
}

function openDeviceModal() {
  resetDeviceForm()
  isDeviceModalOpen.value = true
}

function closeDeviceModal() {
  if (deviceSaving.value) return
  isDeviceModalOpen.value = false
}

async function loadDevices(selectedDeviceId = null) {
  const response = await authFetch('/api/my/devices')

  if (!response.ok) {
    if (response.status === 401 || response.status === 403) {
      await redirectByRole()
      return false
    }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt ierīces.'))
  }

  devices.value = (await response.json()) ?? []
  syncSelectedDevice(selectedDeviceId)
  return true
}

async function loadMeta() {
  metaLoading.value = true
  metaError.value = ''

  try {
    const [branchesResponse, servicesResponse, partsResponse] = await Promise.all([
      authFetch('/api/branches'),
      authFetch('/api/services'),
      authFetch('/api/parts'),
    ])

    if (!branchesResponse.ok) {
      throw new Error(await extractErrorMessage(branchesResponse, 'Neizdevās ielādēt filiāles.'))
    }

    if (!servicesResponse.ok) {
      throw new Error(await extractErrorMessage(servicesResponse, 'Neizdevās ielādēt pakalpojumus.'))
    }

    if (!partsResponse.ok) {
      throw new Error(await extractErrorMessage(partsResponse, 'Neizdevās ielādēt detaļas.'))
    }

    branches.value = (await branchesResponse.json()) ?? []
    services.value = (await servicesResponse.json()) ?? []
    parts.value = (await partsResponse.json()) ?? []

    syncSelectedBranch()
    await loadDevices()
    ensureItems()
  } catch (error) {
    branches.value = []
    services.value = []
    parts.value = []
    devices.value = []
    form.branch_id = null
    form.device_id = null
    form.items = []
    metaError.value = (error?.message || 'Neizdevās ielādēt pieteikuma datus.').slice(0, 260)
  } finally {
    metaLoading.value = false
  }
}

async function loadOrders() {
  listLoading.value = true
  listError.value = ''

  try {
    const response = await authFetch('/api/my/orders')

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectByRole()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt pasūtījumus.'))
    }

    const json = await response.json()
    orders.value = json.data ?? []
    total.value = json.total ?? orders.value.length
  } catch (error) {
    orders.value = []
    total.value = 0
    listError.value = (error?.message || 'Neizdevās ielādēt pasūtījumus.').slice(0, 260)
  } finally {
    listLoading.value = false
  }
}

async function createOrder() {
  creating.value = true
  createError.value = ''
  createSuccess.value = ''

  try {
    if (!form.branch_id) {
      throw new Error('Lūdzu, izvēlies filiāli.')
    }

    if (!form.device_id) {
      throw new Error('Lūdzu, izvēlies ierīci.')
    }

    if (form.items.length === 0) {
      throw new Error('Pievieno vismaz vienu pozīciju.')
    }

    const response = await authFetch('/api/my/orders', {
      method: 'POST',
      json: {
        branch_id: form.branch_id,
        device_id: form.device_id,
        problem_description: form.problem_description,
        items: form.items.map(item => ({
          item_type: item.item_type,
          service_id: item.item_type === 'service' ? (item.service_id || null) : null,
          part_id: item.item_type === 'part' ? (item.part_id || null) : null,
          quantity: item.quantity,
        })),
      },
    })

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectByRole()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās izveidot pieteikumu.'))
    }

    const created = await response.json()
    localStorage.removeItem(ORDER_DRAFT_STORAGE_KEY)
    createSuccess.value = `Izveidots: ${created.order_number}`
    form.items = []
    ensureItems()
    await loadOrders()
  } catch (error) {
    createError.value = (error?.message || 'Kļūda').slice(0, 260)
  } finally {
    creating.value = false
  }
}

async function saveDevice() {
  deviceSaving.value = true
  deviceErrors.value = []

  try {
    const response = await authFetch('/api/my/devices', {
      method: 'POST',
      json: {
        type: deviceForm.type,
        brand: deviceForm.brand,
        model: deviceForm.model,
        serial_number: deviceForm.serial_number || null,
      },
    })

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectByRole()
        return
      }

      const text = await response.text()

      try {
        const payload = JSON.parse(text)
        const validationErrors = Object.values(payload?.errors || {}).flat().map(String)
        deviceErrors.value = validationErrors.length > 0
          ? validationErrors
          : [payload?.message || 'Neizdevās saglabāt ierīci.']
      } catch {
        deviceErrors.value = [text || 'Neizdevās saglabāt ierīci.']
      }

      return
    }

    const created = await response.json()
    const loaded = await loadDevices(created?.id)

    if (loaded) {
      closeDeviceModal()
      resetDeviceForm()
    }
  } catch (error) {
    deviceErrors.value = [(error?.message || 'Neizdevās saglabāt ierīci.').slice(0, 260)]
  } finally {
    deviceSaving.value = false
  }
}

onMounted(async () => {
  await initAuth().catch(() => null)
  await loadMeta()
  applyDraft()
  ensureItems()
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
  max-width: 1040px;
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
  align-items: center;
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

.cardTitle,
.sectionTitle {
  font-weight: 800;
  font-size: 16px;
}

.cardSubtitle {
  margin-top: 6px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.6;
}

.sectionHeader {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-top: 18px;
  padding: 0 2px;
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
  padding: 11px 12px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  outline: none;
  font: inherit;
}
.control:focus {
  border-color: rgba(37, 99, 235, 0.6);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.textarea {
  resize: vertical;
  min-height: 96px;
}

.grid2 {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 14px;
}

.items {
  display: grid;
  gap: 10px;
  margin-top: 10px;
}

.itemRow {
  display: grid;
  grid-template-columns: minmax(140px, 180px) minmax(0, 1fr) minmax(110px, 130px) 44px;
  gap: 10px;
  align-items: center;
}

.qtyControl {
  text-align: center;
}

.summaryRow {
  display: flex;
  justify-content: flex-end;
}

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
  text-decoration: none;
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

.loadingBox,
.emptyHint {
  font-size: 14px;
  color: #475569;
  background: rgba(148, 163, 184, 0.10);
  border: 1px solid rgba(148, 163, 184, 0.20);
  padding: 12px 14px;
  border-radius: 12px;
}

.muted { color: #64748b; }
.small { font-size: 12px; }

.modalOverlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: grid;
  place-items: center;
  padding: 18px;
  z-index: 100;
}

.modalCard {
  width: min(100%, 560px);
  background: #fff;
  border-radius: 18px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
  padding: 18px;
}

.mt12 { margin-top: 12px; }
.mt14 { margin-top: 14px; }
.mt16 { margin-top: 16px; }
.mt18 { margin-top: 18px; }
.spacer { height: 10px; }

@media (max-width: 920px) {
  .topbar { align-items: flex-start; flex-direction: column; }
  .orderTop { grid-template-columns: 1fr; }
  .cost { text-align: left; }
  .grid2 { grid-template-columns: 1fr; }
  .itemRow { grid-template-columns: 1fr; }
  .summaryRow { justify-content: flex-start; }
}
</style>

