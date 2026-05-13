<template>
  <div class="page">
    <DashboardTopbar :title="pageTitle" />

    <div v-if="showCreate" class="dashboardGrid">
      <section class="card formCard">
        <div class="cardHead">
          <div>
            <div class="cardTitle">Jauns pieteikums</div>
            <div class="cardSubtitle">Izvēlies pieteikuma veidu, filiāli un ierīci. Servisa pozīcijas tiks sagatavotas automātiski.</div>
          </div>
        </div>

        <div v-if="metaLoading" class="loadingBox">Ielādējam filiāles, ierīces un cenu katalogu...</div>
        <div v-if="metaError" class="msg mt12">{{ metaError }}</div>

        <div class="formSection">
          <div class="sectionEyebrow">Izvēlies pieteikuma veidu</div>

          <div class="requestTypeGrid">
            <button
              v-for="requestType in requestTypes"
              :key="requestType.value"
              type="button"
              :class="['requestTypeCard', { active: form.request_type === requestType.value }]"
              @click="selectRequestType(requestType.value)"
            >
              <strong>{{ requestType.title }}</strong>
            </button>
          </div>
        </div>

        <div class="formSection">
          <div class="sectionEyebrow">Pamatinformācija</div>

          <div class="grid2">
            <label class="field">
              <div class="label">Filiāle</div>
              <select class="control" v-model.number="form.branch_id" :disabled="metaLoading || branches.length === 0">
                <option value="" disabled hidden>Izvēlies filiāli</option>
                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                  {{ formatBranchShort(branch) }}{{ branch.address ? ` — ${branch.address}` : '' }}
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

          <div v-if="form.request_type === 'screen_battery'" class="repairChoice">
            <div class="label">Remonta veids</div>
            <div class="repairToggle">
              <button type="button" :class="['repairButton', { active: form.repair_option === 'screen' }]" @click="form.repair_option = 'screen'">
                Ekrāna maiņa
              </button>
              <button type="button" :class="['repairButton', { active: form.repair_option === 'battery' }]" @click="form.repair_option = 'battery'">
                Akumulatora maiņa
              </button>
            </div>
          </div>
        </div>

        <div v-if="form.request_type === 'general'" class="formSection">
          <div class="sectionEyebrow">Problēmas apraksts</div>

          <label class="field">
            <textarea
              class="control textarea"
              v-model="form.problem_description"
              rows="4"
              :placeholder="problemPlaceholder"
              required
            ></textarea>
          </label>
        </div>

        <div class="formSection">
          <div class="sectionEyebrow">Ierīces fotoattēli</div>
          <div class="photoHint">Varat pievienot līdz 5 fotoattēliem, lai darbinieks labāk saprastu bojājumu.</div>

          <label class="photoDrop">
            <input
              type="file"
              multiple
              accept="image/jpeg,image/png,image/webp"
              @change="handlePhotoInput"
            />
            <span>Izvēlēties fotoattēlus</span>
            <small>JPG, PNG vai WEBP līdz 5 MB katrs</small>
          </label>

          <div v-if="photoError" class="msg mt12">{{ photoError }}</div>

          <div v-if="selectedPhotos.length > 0" class="photoPreviewGrid">
            <article class="photoPreview" v-for="photo in selectedPhotos" :key="photo.id">
              <img :src="photo.previewUrl" :alt="photo.file.name" />
              <div class="photoMeta">
                <strong>{{ photo.file.name }}</strong>
                <span>{{ formatFileSize(photo.file.size) }}</span>
              </div>
              <button class="photoRemove" type="button" @click="removeSelectedPhoto(photo.id)">×</button>
            </article>
          </div>
        </div>

        <div class="formSection">
          <div class="sectionEyebrow">Pozīcijas</div>

          <div class="lineItems">
            <div class="lineItemCard">
              <div class="lineMain">
                <div class="lineName">
                  <strong>{{ displayServiceTitle }}</strong>
                </div>
              </div>

              <div class="fixedQty" aria-label="Daudzums">
                <span>Daudzums</span>
                <strong>1</strong>
              </div>

              <div class="priceBox">
                <span>Cena</span>
                <strong>{{ formatMoney(draftTotal) }}</strong>
              </div>
            </div>
          </div>
        </div>
      </section>

      <aside class="summaryCard">
        <div class="summaryTitle">Kopsavilkums</div>

        <div class="summaryList">
          <div class="summaryItem">
            <span>Filiāle</span>
            <b>{{ selectedBranch ? selectedBranch.name : 'Nav izvēlēta' }}</b>
          </div>
          <div class="summaryItem">
            <span>Ierīce</span>
            <b>{{ selectedDevice ? formatDeviceLabel(selectedDevice) : 'Nav izvēlēta' }}</b>
          </div>
          <div class="summaryItem">
            <span>Pieteikuma veids</span>
            <b>{{ selectedRequestType.title }}</b>
          </div>
          <div class="summaryItem">
            <span>Pakalpojums</span>
            <b>{{ displayServiceTitle }}</b>
          </div>
        </div>

        <div class="summaryTotal">
          <span>Aptuvenā summa</span>
          <strong>{{ formatMoney(draftTotal) }}</strong>
        </div>

        <button
          class="btn btnPrimary summaryCta"
          type="button"
          @click="createOrder"
          :disabled="creating || metaLoading || !form.branch_id || !form.device_id || !canSubmitRequest"
        >
          {{ creating ? 'Veido...' : 'Izveidot pieteikumu' }}
        </button>

        <div class="summaryNote">Gala summa var mainīties pēc diagnostikas.</div>

        <div class="msg mt12" v-if="createError">{{ createError }}</div>
        <div class="msg ok mt12" v-else-if="createSuccess">{{ createSuccess }}</div>
      </aside>
    </div>

    <section v-if="showHistory" class="ordersSection">
      <div class="sectionHeader">
        <div>
          <div class="sectionTitle">{{ ordersSectionTitle }}</div>
          <div class="sectionHint">{{ ordersSectionHint }}</div>
        </div>
        <div class="muted">Kopā: {{ total }}</div>
      </div>

      <div class="filterBar">
        <input class="control" v-model.trim="filters.search" type="search" placeholder="Meklēt pēc numura vai ierīces" />
        <select class="control" v-model="filters.status">
          <option value="">Visi statusi</option>
          <option value="new">Jauns</option>
          <option value="confirmed">Apstiprināts</option>
          <option value="diagnostics">Diagnostika</option>
          <option value="in_progress">Remontā</option>
          <option value="waiting_parts">Gaida detaļas</option>
          <option value="ready">Gatavs saņemšanai</option>
          <option value="done">Pabeigts</option>
          <option value="cancelled">Atcelts</option>
        </select>
        <select class="control" v-model="filters.payment_status">
          <option value="">Apmaksa: visi</option>
          <option value="paid">Apmaksāts</option>
          <option value="unpaid">Gaida apmaksu</option>
        </select>
        <button class="btn btnSoft" type="button" @click="resetFilters">Atiestatīt</button>
      </div>

      <div v-if="listLoading" class="card stateCard">
        <div class="loadingBox">Ielādējam pasūtījumus...</div>
      </div>

      <div v-else>
        <div v-if="listError" class="card stateCard">
          <div class="msg">{{ listError }}</div>
        </div>

        <div v-else-if="orders.length === 0" class="card stateCard">
          <div class="muted">{{ hasActiveFilters ? 'Pēc filtriem pasūtījumi netika atrasti.' : emptyOrdersText }}</div>
        </div>

        <div class="card orderCard" v-for="order in orders" :key="order.id">
          <div class="orderTop">
            <div class="orderMain">
              <div class="orderLine">
                <div class="orderNum">{{ order.order_number }}</div>
                <span class="badge" :class="'st_' + order.status">{{ statusLabel(order.status) }}</span>
              </div>

              <div class="orderDescription">{{ order.problem_description || '—' }}</div>

              <div class="chips">
                <span class="chip">Filiāle: {{ order.branch?.name || order.branch_id }}</span>
                <span class="chip">Ierīce: {{ orderDeviceLabel(order.device) }}</span>
                <span class="chip" :class="order.assigned_staff ? 'chipAssigned' : 'chipUnassigned'">
                  Darbinieks: {{ order.assigned_staff?.name || 'nav piešķirts' }}
                </span>
                <span class="chip">Izveidots: {{ formatDate(order.created_at) }}</span>
              </div>
            </div>

            <div class="orderActionColumn">
              <div class="muted small">Galīgā summa</div>
              <div class="costValue">{{ order.final_cost != null ? formatMoney(order.final_cost) : '—' }}</div>
              <RouterLink class="btn btnPrimary actionOpenButton" :to="`/orders/${order.id}`">Atvērt</RouterLink>
            </div>
          </div>

          <div v-if="canCancelOrder(order)" class="actions compactActions">
            <button
              class="btn btnDanger"
              type="button"
              @click="cancelOrder(order)"
              :disabled="cancellingId === order.id"
            >
              {{ cancellingId === order.id ? 'Atceļ...' : 'Atcelt pieteikumu' }}
            </button>
          </div>
        </div>
      </div>
    </section>

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
            <AutocompleteInput
              v-model.trim="deviceForm.brand"
              :suggestions="brandSuggestions"
              placeholder="Apple, Samsung..."
              @input="loadBrandSuggestions"
              @select="loadModelSuggestions"
            />
          </label>

          <label class="field">
            <div class="label">Modelis</div>
            <AutocompleteInput
              v-model.trim="deviceForm.model"
              :suggestions="modelSuggestions"
              placeholder="iPhone 14 Pro..."
              @input="loadModelSuggestions"
            />
          </label>
        </div>

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
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AutocompleteInput from './AutocompleteInput.vue'
import DashboardTopbar from './DashboardTopbar.vue'
import { authFetch, currentUser, extractErrorMessage, hasAnyRole, initAuth } from '../auth'
import { formatBranchShort, uniqueBranches } from '../branchFormat'
import { fetchDeviceBrands, fetchDeviceModelsByType } from '../deviceCatalog'
import { formatDevice } from '../deviceFormat'
import { statusLabel } from '../orderStatus'
import OrderStatusTimeline from './OrderStatusTimeline.vue'

const props = defineProps({
  mode: {
    type: String,
    default: 'all',
    validator: value => ['all', 'new', 'active', 'history'].includes(value),
  },
})

const ORDER_DRAFT_STORAGE_KEY = 'devicelab:orderDraft:v1'
const ADD_DEVICE_OPTION = '__add_device__'

const router = useRouter()

const showCreate = computed(() => props.mode === 'all' || props.mode === 'new')
const showHistory = computed(() => props.mode === 'all' || props.mode === 'active' || props.mode === 'history')
const pageTitle = computed(() => {
  if (showCreate.value && !showHistory.value) return 'Jauns pieteikums'
  if (props.mode === 'active') return 'Aktīvie pasūtījumi'
  return 'Pasūtījumu vēsture'
})
const ordersSectionTitle = computed(() => (props.mode === 'active' ? 'Aktīvie pasūtījumi' : 'Mani pasūtījumi'))
const ordersSectionHint = computed(() => (
  props.mode === 'active'
    ? 'Pasūtījumi, kas vēl nav pabeigti vai atcelti.'
    : 'Pārskati savus pabeigtos un atceltos servisa pieteikumus.'
))
const emptyOrdersText = computed(() => (
  props.mode === 'active'
    ? 'Aktīvu pasūtījumu vēl nav.'
    : 'Pasūtījumu vēl nav.'
))

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
const cancellingId = ref(null)
const photoError = ref('')
const selectedPhotos = ref([])
const expandedOrderIds = ref([])
const payingId = ref(null)
const paymentError = ref('')
const paymentSuccess = ref('')
const paymentMessageId = ref(null)
const reviewForms = ref({})
const reviewSubmittingId = ref(null)
const reviewError = ref('')
const reviewSuccess = ref('')
const reviewMessageId = ref(null)

const isDeviceModalOpen = ref(false)
const deviceSaving = ref(false)
const deviceErrors = ref([])
const brandSuggestions = ref([])
const modelSuggestions = ref([])
let brandSuggestionsTimer = null
let modelSuggestionsTimer = null
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
  request_type: 'general',
  repair_option: 'screen',
  problem_description: '',
  items: [],
})

const filters = reactive({
  search: '',
  status: '',
  payment_status: '',
})

const deviceForm = reactive({
  type: 'phone',
  brand: '',
  model: '',
})

const requestTypes = [
  {
    value: 'general',
    title: 'Vispārīgs servisa pieteikums',
  },
  {
    value: 'screen_battery',
    title: 'Ekrāna vai akumulatora maiņa',
  },
  {
    value: 'quick_diagnostics',
    title: 'Ātrā diagnostika',
  },
]

const OFFICE_TO_BRANCH_ID = {
  'riga-centrs': 1,
  'riga-center': 1,
  centrs: 1,
  center: 1,
  'riga-purvciems': 2,
  purvciems: 2,
  'riga-imanta': 3,
  imanta: 3,
  'riga-teika': 4,
  teika: 4,
  'riga-pardaugava': 5,
  pardaugava: 5,
  pārdaugava: 5,
}

const selectedRequestType = computed(() => (
  requestTypes.find(type => type.value === form.request_type) ?? requestTypes[0]
))

const selectedService = computed(() => findServiceForRequest())

const displayServiceTitle = computed(() => {
  if (form.request_type === 'general') {
    return 'Vispārīgs servisa pieteikums'
  }

  if (form.request_type === 'screen_battery') {
    return form.repair_option === 'battery'
      ? 'Akumulatora maiņa'
      : 'Ekrāna maiņa'
  }

  if (form.request_type === 'quick_diagnostics') {
    return selectedService.value?.name || 'Ātrā diagnostika'
  }

  return selectedService.value?.name || 'Serviss tiks precizēts'
})

const draftTotal = computed(() => Number(selectedService.value?.base_price || 0))

const positionCount = computed(() => (selectedService.value ? 1 : 0))

const problemPlaceholder = computed(() => {
  if (form.request_type === 'quick_diagnostics') return 'Pievieno komentāru ātrajai diagnostikai, ja nepieciešams.'
  if (form.request_type === 'screen_battery') return 'Pievieno komentāru par ekrāna vai akumulatora maiņu.'
  return 'Īsi apraksti problēmu'
})

const canSubmitRequest = computed(() => {
  if (form.request_type === 'general') {
    return form.problem_description.trim().length > 0
  }

  return form.request_type !== 'screen_battery' || ['screen', 'battery'].includes(form.repair_option)
})

const selectedBranch = computed(() => (
  branches.value.find(branch => branch.id === Number(form.branch_id)) ?? null
))

const selectedDevice = computed(() => (
  devices.value.find(device => device.id === Number(form.device_id)) ?? null
))

const hasActiveFilters = computed(() => Object.values(filters).some(Boolean))

function formatMoney(value) {
  const amount = Number(value || 0)
  return `${amount.toFixed(2)} €`
}

function formatFileSize(bytes) {
  const size = Number(bytes || 0)

  if (size < 1024 * 1024) {
    return `${Math.max(1, Math.round(size / 1024))} KB`
  }

  return `${(size / 1024 / 1024).toFixed(1)} MB`
}

function revokePhotoPreview(photo) {
  if (photo?.previewUrl) {
    URL.revokeObjectURL(photo.previewUrl)
  }
}

function clearSelectedPhotos() {
  selectedPhotos.value.forEach(revokePhotoPreview)
  selectedPhotos.value = []
  photoError.value = ''
}

function handlePhotoInput(event) {
  photoError.value = ''
  const files = Array.from(event.target.files || [])
  event.target.value = ''

  if (selectedPhotos.value.length + files.length > 5) {
    photoError.value = 'Var pievienot ne vairāk kā 5 fotoattēlus.'
    return
  }

  const nextPhotos = []

  for (const file of files) {
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      photoError.value = 'Var pievienot tikai JPG, PNG vai WEBP attēlus.'
      continue
    }

    if (file.size > 5 * 1024 * 1024) {
      photoError.value = 'Katrs fotoattēls drīkst būt līdz 5 MB.'
      continue
    }

    nextPhotos.push({
      id: `${Date.now()}-${Math.random().toString(16).slice(2)}`,
      file,
      previewUrl: URL.createObjectURL(file),
    })
  }

  selectedPhotos.value = [...selectedPhotos.value, ...nextPhotos].slice(0, 5)
}

function removeSelectedPhoto(photoId) {
  const photo = selectedPhotos.value.find(item => item.id === photoId)
  revokePhotoPreview(photo)
  selectedPhotos.value = selectedPhotos.value.filter(item => item.id !== photoId)
}

function itemName(item) {
  return item.item_type === 'service'
    ? (item.service?.name || `#${item.service_id}`)
    : (item.part?.name || `#${item.part_id}`)
}

function isOrderExpanded(orderId) {
  return expandedOrderIds.value.includes(orderId)
}

function toggleOrderItems(orderId) {
  expandedOrderIds.value = isOrderExpanded(orderId)
    ? expandedOrderIds.value.filter(id => id !== orderId)
    : [...expandedOrderIds.value, orderId]
}

function visibleOrderItems(order) {
  const items = order.items ?? []
  return isOrderExpanded(order.id) ? items : items.slice(0, 3)
}

function hasMoreOrderItems(order) {
  return (order.items ?? []).length > 3
}

function isOrderPaid(order) {
  return order.payment?.status === 'paid' || order.status === 'done'
}

function canReviewOrder(order) {
  return order.status === 'done' && order.payment?.status === 'paid'
}

function canCancelOrder(order) {
  return order.status === 'new' && !order.assigned_staff_id && order.payment?.status !== 'paid'
}

function reviewForm(order) {
  if (!reviewForms.value[order.id]) {
    reviewForms.value[order.id] = {
      rating: 0,
      comment: '',
    }
  }

  return reviewForms.value[order.id]
}

function starText(rating) {
  const value = Math.max(0, Math.min(5, Number(rating || 0)))
  return '★'.repeat(value) + '☆'.repeat(5 - value)
}

function replaceOrder(updatedOrder) {
  const index = orders.value.findIndex(order => order.id === updatedOrder.id)

  if (index >= 0) {
    orders.value[index] = updatedOrder
  }
}

function formatDate(value) {
  try {
    return new Date(value).toLocaleString()
  } catch {
    return value
  }
}

function formatDeviceLabel(device) {
  return formatDevice(device)
}

function orderDeviceLabel(device) {
  return device ? formatDeviceLabel(device) : '—'
}

function findServiceForRequest() {
  const name = form.request_type === 'quick_diagnostics'
    ? 'Ātrā diagnostika'
    : form.request_type === 'screen_battery'
      ? (form.repair_option === 'screen' ? 'Ekrāna maiņa' : 'Akumulatora maiņa')
      : 'Diagnostika'

  return services.value.find(service => service.name === name)
    ?? services.value.find(service => service.name?.toLowerCase().includes(name.toLowerCase()))
    ?? null
}

function selectRequestType(type) {
  form.request_type = type

  if (type !== 'screen_battery') {
    form.repair_option = null
  } else if (!form.repair_option) {
    form.repair_option = 'screen'
  }

  if (type !== 'general') {
    form.problem_description = ''
  }
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
    await router.replace('/staff/orders/new')
    return
  }

  await router.replace({ path: '/login', query: { redirect: router.currentRoute.value.fullPath } })
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
  deviceErrors.value = []
  modelSuggestions.value = []
}

function openDeviceModal() {
  resetDeviceForm()
  isDeviceModalOpen.value = true
  loadBrandSuggestions()
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

    branches.value = uniqueBranches((await branchesResponse.json()) ?? [])
    services.value = (await servicesResponse.json()) ?? []
    parts.value = (await partsResponse.json()) ?? []

    syncSelectedBranch()
    await loadDevices()
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
    const params = new URLSearchParams()
    Object.entries(filters).forEach(([key, value]) => {
      if (value) params.set(key, value)
    })
    const scope = props.mode === 'active' ? 'active' : (props.mode === 'history' ? 'history' : '')
    if (scope) params.set('scope', scope)
    const query = params.toString()

    const endpoint = props.mode === 'active' || props.mode === 'history'
      ? '/api/client/orders'
      : (showHistory.value ? '/api/my/orders/history' : '/api/my/orders')
    const response = await authFetch(query ? `${endpoint}?${query}` : endpoint)

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

function resetFilters() {
  filters.search = ''
  filters.status = ''
  filters.payment_status = ''
  loadOrders()
}

async function createOrder() {
  creating.value = true
  createError.value = ''
  createSuccess.value = ''
  photoError.value = ''

  try {
    if (!form.branch_id) {
      throw new Error('Lūdzu, izvēlies filiāli.')
    }

    if (!form.device_id) {
      throw new Error('Lūdzu, izvēlies ierīci.')
    }

    if (!canSubmitRequest.value) {
      throw new Error(form.request_type === 'general'
        ? 'Lūdzu, apraksti problēmu.'
        : 'Lūdzu, izvēlies remonta veidu.')
    }

    const response = await authFetch('/api/my/orders', {
      method: 'POST',
      json: {
        branch_id: form.branch_id,
        device_id: form.device_id,
        request_type: form.request_type,
        repair_option: form.request_type === 'screen_battery' ? form.repair_option : null,
        problem_description: form.request_type === 'general' ? form.problem_description : null,
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
    let uploadWarning = ''

    if (selectedPhotos.value.length > 0) {
      try {
        await uploadOrderPhotos(created.id)
        clearSelectedPhotos()
      } catch (uploadError) {
        uploadWarning = ' Pieteikums izveidots, bet foto neizdevās augšupielādēt.'
        photoError.value = (uploadError?.message || 'Foto neizdevās augšupielādēt.').slice(0, 260)
      }
    }

    localStorage.removeItem(ORDER_DRAFT_STORAGE_KEY)
    createSuccess.value = `Izveidots: ${created.order_number}.${uploadWarning}`
    form.problem_description = ''
    if (showHistory.value) {
      await loadOrders()
    }
  } catch (error) {
    createError.value = (error?.message || 'Kļūda').slice(0, 260)
  } finally {
    creating.value = false
  }
}

async function uploadOrderPhotos(orderId) {
  const formData = new FormData()

  selectedPhotos.value.forEach(photo => {
    formData.append('photos[]', photo.file)
  })

  const response = await authFetch(`/api/my/orders/${orderId}/attachments`, {
    method: 'POST',
    body: formData,
  })

  if (!response.ok) {
    throw new Error(await extractErrorMessage(response, 'Foto neizdevās augšupielādēt.'))
  }

  return response.json()
}

async function cancelOrder(order) {
  if (!confirm('Vai tiešām vēlaties atcelt šo pieteikumu?')) return

  cancellingId.value = order.id
  listError.value = ''

  try {
    const response = await authFetch(`/api/my/orders/${order.id}/cancel`, { method: 'PATCH' })

    if (!response.ok) {
      throw new Error(await extractErrorMessage(response, 'Neizdevās atcelt pieteikumu.'))
    }

    replaceOrder(await response.json())
  } catch (error) {
    listError.value = (error?.message || 'Neizdevās atcelt pieteikumu.').slice(0, 260)
  } finally {
    cancellingId.value = null
  }
}

async function payOrder(order) {
  payingId.value = order.id
  paymentError.value = ''
  paymentSuccess.value = ''
  paymentMessageId.value = order.id

  try {
    const response = await authFetch(`/api/my/orders/${order.id}/pay`, {
      method: 'POST',
    })

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectByRole()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās apmaksāt pasūtījumu.'))
    }

    const updatedOrder = await response.json()
    replaceOrder(updatedOrder)
    paymentSuccess.value = 'Pasūtījums apmaksāts.'
  } catch (error) {
    paymentError.value = (error?.message || 'Neizdevās apmaksāt pasūtījumu.').slice(0, 260)
  } finally {
    payingId.value = null
  }
}

async function submitReview(order) {
  const formState = reviewForm(order)
  reviewSubmittingId.value = order.id
  reviewMessageId.value = order.id
  reviewError.value = ''
  reviewSuccess.value = ''

  try {
    const response = await authFetch(`/api/my/orders/${order.id}/review`, {
      method: 'POST',
      json: {
        rating: formState.rating,
        comment: formState.comment || null,
      },
    })

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await redirectByRole()
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās iesniegt atsauksmi.'))
    }

    const review = await response.json()
    replaceOrder({
      ...order,
      review,
    })
    reviewForms.value[order.id] = { rating: 0, comment: '' }
    reviewSuccess.value = 'Paldies! Atsauksme iesniegta.'
  } catch (error) {
    reviewError.value = (error?.message || 'Neizdevās iesniegt atsauksmi.').slice(0, 260)
  } finally {
    reviewSubmittingId.value = null
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
        component_type: null,
        brand: deviceForm.brand,
        model: deviceForm.model,
        specs: null,
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
      isDeviceModalOpen.value = false
      resetDeviceForm()
    }
  } catch (error) {
    deviceErrors.value = [(error?.message || 'Neizdevās saglabāt ierīci.').slice(0, 260)]
  } finally {
    deviceSaving.value = false
  }
}

async function loadBrandSuggestions() {
  clearTimeout(brandSuggestionsTimer)
  brandSuggestionsTimer = setTimeout(async () => {
    brandSuggestions.value = await fetchDeviceBrands(deviceForm.type, deviceForm.brand)
  }, 250)
}

async function loadModelSuggestions() {
  clearTimeout(modelSuggestionsTimer)
  modelSuggestionsTimer = setTimeout(async () => {
    modelSuggestions.value = await fetchDeviceModelsByType(deviceForm.type, deviceForm.brand, deviceForm.model)
  }, 250)
}

watch(
  () => deviceForm.brand,
  async () => {
    deviceForm.model = ''
    modelSuggestions.value = []
    loadBrandSuggestions()
    loadModelSuggestions()
  },
)

watch(
  () => deviceForm.type,
  () => {
    brandSuggestions.value = []
    modelSuggestions.value = []
    loadBrandSuggestions()
    loadModelSuggestions()
  },
)

let filtersTimer = null
watch(
  filters,
  () => {
    if (!showHistory.value) return
    clearTimeout(filtersTimer)
    filtersTimer = setTimeout(() => loadOrders(), 300)
  },
  { deep: true }
)

onMounted(async () => {
  await initAuth().catch(() => null)

  if (showCreate.value) {
    await loadMeta()
    applyDraft()
  }

  if (showHistory.value) {
    await loadOrders()
  }
})

onBeforeUnmount(() => {
  clearSelectedPhotos()
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

.titleBlock::after {
  content: "";
  display: block;
  height: 20px;
}

.titleRow {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

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

.btnBack {
  min-height: 38px;
  padding: 8px 12px;
  font-size: 14px;
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

.cost,
.orderActionColumn { text-align: right; }
.costValue { font-size: 22px; font-weight: 900; letter-spacing: -0.01em; }
.orderActionColumn {
  display: grid;
  justify-items: end;
  gap: 9px;
}
.actionOpenButton {
  min-width: 112px;
  justify-content: center;
}

.badge {
  font-size: 12px;
  padding: 5px 10px;
  border-radius: 999px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: rgba(15, 23, 42, 0.04);
}
.st_new { background: rgba(37, 99, 235, 0.10); border-color: rgba(37, 99, 235, 0.22); }
.st_confirmed { background: rgba(16, 185, 129, 0.10); border-color: rgba(16, 185, 129, 0.22); }
.st_diagnostics { background: rgba(14, 165, 233, 0.10); border-color: rgba(14, 165, 233, 0.24); }
.st_in_progress { background: rgba(245, 158, 11, 0.10); border-color: rgba(245, 158, 11, 0.24); }
.st_waiting_parts { background: rgba(139, 92, 246, 0.10); border-color: rgba(139, 92, 246, 0.24); }
.st_ready { background: rgba(34, 197, 94, 0.10); border-color: rgba(34, 197, 94, 0.24); }
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
.btnDanger {
  color: #be123c;
  background: #fff1f2;
  border-color: #fecdd3;
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
  .cost,
  .orderActionColumn { text-align: left; }
  .orderActionColumn { justify-items: stretch; }
  .grid2 { grid-template-columns: 1fr; }
  .itemRow { grid-template-columns: 1fr; }
  .summaryRow { justify-content: flex-start; }
}

/* Dashboard redesign */
:global(body) {
  background: #f3f6fb;
  color: #071833;
}

.page {
  max-width: 1180px;
  padding: 28px 22px 48px;
}

.topbar {
  align-items: center;
  margin-bottom: 18px;
}

.h1 {
  color: #071833;
  font-size: 36px;
  line-height: 1.05;
  letter-spacing: 0;
}

.subtitle,
.muted,
.cardSubtitle,
.sectionHint {
  color: #64748b;
}

.topActions {
  justify-content: flex-end;
}

.dashboardGrid {
  display: grid;
  grid-template-columns: minmax(0, 2.1fr) minmax(280px, 0.9fr);
  gap: 20px;
  align-items: start;
}

.card,
.summaryCard {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.07);
}

.formCard {
  padding: 24px;
  margin-top: 0;
}

.cardHead {
  margin-bottom: 20px;
}

.cardTitle {
  color: #071833;
  font-size: 20px;
  letter-spacing: 0;
}

.cardSubtitle {
  max-width: 620px;
  font-size: 14px;
}

.formSection {
  padding-top: 20px;
  margin-top: 20px;
  border-top: 1px solid #e8edf5;
}

.formSection:first-of-type {
  padding-top: 0;
  margin-top: 0;
  border-top: 0;
}

.sectionHead {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  align-items: flex-start;
}

.sectionEyebrow {
  color: #071833;
  font-size: 14px;
  font-weight: 900;
  letter-spacing: 0;
}

.sectionHint {
  margin-top: 5px;
  font-size: 13px;
  line-height: 1.5;
}

.requestTypeGrid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-top: 12px;
}

.requestTypeCard {
  display: grid;
  align-content: start;
  gap: 9px;
  min-height: 118px;
  padding: 16px;
  text-align: left;
  color: #071833;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  cursor: pointer;
  transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease, background-color 160ms ease;
}

.requestTypeCard:hover,
.requestTypeCard.active {
  background: #f5f9ff;
  border-color: #93c5fd;
  box-shadow: 0 16px 32px rgba(37, 99, 235, 0.12);
  transform: translateY(-1px);
}

.requestTypeCard strong {
  font-size: 16px;
  line-height: 1.25;
}

.requestTypeCard small {
  color: #64748b;
  font-size: 13px;
  line-height: 1.45;
}

.grid2 {
  gap: 16px;
}

.label {
  color: #334155;
  font-size: 12px;
  font-weight: 700;
}

.control {
  min-height: 50px;
  border-radius: 14px;
  border-color: #d8e0eb;
  color: #071833;
  background: #fff;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
}

.control:hover {
  border-color: #bdc9d9;
}

.control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.textarea {
  min-height: 116px;
  line-height: 1.5;
}

.smallTextarea {
  min-height: 90px;
}

.repairChoice {
  margin-top: 16px;
}

.repairToggle {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 8px;
}

.repairButton {
  min-height: 42px;
  padding: 10px 14px;
  color: #071833;
  background: #fff;
  border: 1px solid #d8e0eb;
  border-radius: 14px;
  cursor: pointer;
  font-weight: 800;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
}

.repairButton:hover,
.repairButton.active {
  color: #1d4ed8;
  background: #eef5ff;
  border-color: #93c5fd;
  box-shadow: 0 10px 24px rgba(37, 99, 235, 0.12);
}

.items {
  gap: 12px;
  margin-top: 14px;
}

.itemRow {
  grid-template-columns: minmax(130px, 170px) minmax(0, 1fr) minmax(92px, 120px) 42px;
  gap: 10px;
  padding: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
}

.typeControl {
  font-weight: 700;
}

.qtyControl {
  text-align: center;
  font-weight: 700;
}

.btn {
  border-radius: 14px;
  transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, background-color 160ms ease;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btnPrimary {
  background: #2563eb;
  border-color: #2563eb;
  box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
}

.btnSoft {
  background: #f8fafc;
  border-color: #d8e0eb;
}

.btnSmall {
  min-height: 38px;
  padding: 8px 12px;
  font-size: 13px;
}

.btnIcon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
}

.summaryCard {
  position: sticky;
  top: 22px;
  padding: 22px;
}

.summaryTitle {
  color: #071833;
  font-size: 20px;
  font-weight: 900;
}

.summaryList {
  display: grid;
  gap: 12px;
  margin-top: 20px;
}

.summaryItem {
  display: grid;
  gap: 5px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e8edf5;
}

.summaryItem span,
.summaryTotal span {
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.summaryItem b {
  color: #071833;
  font-size: 14px;
  line-height: 1.35;
}

.summaryTotal {
  display: grid;
  gap: 6px;
  margin-top: 18px;
  padding: 18px;
  border-radius: 18px;
  background: #eef5ff;
  border: 1px solid #d7e8ff;
}

.summaryTotal strong {
  color: #071833;
  font-size: 30px;
  line-height: 1;
}

.summaryCta {
  width: 100%;
  justify-content: center;
  margin-top: 16px;
  padding: 14px 16px;
  font-size: 15px;
}

.ordersSection {
  margin-top: 28px;
}

.filterBar {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(160px, 220px) minmax(160px, 220px) auto;
  gap: 10px;
  margin: 0 0 14px;
}

.sectionHeader {
  margin: 0 0 14px;
  padding: 0 2px;
}

.sectionTitle {
  color: #071833;
  font-size: 20px;
  letter-spacing: 0;
}

.stateCard {
  margin-top: 0;
}

.orderCard {
  padding: 20px;
  margin-top: 14px;
}

.orderTop {
  grid-template-columns: minmax(0, 1fr) minmax(150px, 190px);
  align-items: start;
}

.orderLine {
  gap: 12px;
}

.orderNum {
  color: #071833;
  font-size: 20px;
  letter-spacing: 0;
}

.orderDescription {
  margin-top: 8px;
  color: #475569;
  font-size: 15px;
  line-height: 1.45;
}

.costValue {
  color: #071833;
  font-size: 26px;
}

.orderActionColumn {
  gap: 10px;
}

.badge {
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 800;
  text-transform: capitalize;
}

.chips {
  margin-top: 12px;
  gap: 9px;
}

.chip {
  background: #f1f5f9;
  border-color: #dbe4ef;
  color: #334155;
  font-weight: 600;
}

.chipAssigned {
  background: #ecfdf5;
  border-color: #bbf7d0;
  color: #166534;
}

.chipUnassigned {
  background: #fff7ed;
  border-color: #fed7aa;
  color: #9a3412;
}

.itemsBlock {
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid #e8edf5;
}

.subTitle {
  color: #071833;
  font-size: 14px;
}

.orderItems {
  display: grid;
  gap: 8px;
  margin-top: 10px;
}

.orderItem {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto auto;
  gap: 14px;
  align-items: center;
  padding: 11px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fafc;
}

.orderItem b {
  color: #071833;
}

.orderItem span,
.orderItem strong {
  white-space: nowrap;
}

.loadingBox,
.emptyHint,
.msg {
  border-radius: 14px;
}

@media (max-width: 980px) {
  .dashboardGrid {
    grid-template-columns: 1fr;
  }

  .requestTypeGrid {
    grid-template-columns: 1fr;
  }

  .summaryCard {
    position: static;
  }
}

@media (max-width: 720px) {
  .page {
    padding: 20px 14px 36px;
  }

  .topbar {
    align-items: flex-start;
    flex-direction: column;
  }

  .topActions {
    width: 100%;
    justify-content: space-between;
  }

  .h1 {
    font-size: 30px;
  }

  .formCard,
  .summaryCard,
  .orderCard {
    border-radius: 18px;
    padding: 16px;
  }

  .sectionHead {
    flex-direction: column;
  }

  .grid2,
  .filterBar,
  .itemRow,
  .orderTop,
  .orderItem {
    grid-template-columns: 1fr;
  }

  .cost,
  .orderActionColumn {
    text-align: left;
  }

  .orderActionColumn {
    justify-items: stretch;
  }

  .orderItem span,
  .orderItem strong {
    white-space: normal;
  }
}

/* Modern SaaS dashboard polish */
.page {
  max-width: 1220px;
  background:
    radial-gradient(900px 420px at 12% -10%, rgba(47, 124, 255, 0.10), transparent 62%),
    linear-gradient(180deg, #f6f8fc 0%, #f8fafc 100%);
}

.topbar {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: flex-start;
}

.titleBlock {
  min-width: 0;
}

.topActions {
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
  gap: 12px;
  min-width: 0;
  margin-top: 2px;
}

.dashboardGrid {
  grid-template-columns: minmax(0, 1.9fr) minmax(300px, 0.8fr);
  gap: 22px;
}

.card,
.summaryCard,
.modalCard {
  border-radius: 18px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
}

.formCard {
  padding: 26px;
}

.cardHead {
  padding-bottom: 18px;
  margin-bottom: 0;
  border-bottom: 0;
}

.cardTitle {
  font-size: 22px;
}

.formSection {
  padding-top: 26px;
  margin-top: 24px;
  border-top: 1px solid rgba(226, 232, 240, 0.85);
}

.formSection:first-of-type {
  padding-top: 0;
  margin-top: 0;
  border-top: 0;
}

.sectionEyebrow {
  margin-bottom: 14px;
  font-size: 15px;
}

.requestTypeGrid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.requestTypeCard {
  min-height: 104px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  align-content: center;
}

.requestTypeCard.active {
  border-color: #2f7cff;
  box-shadow: 0 18px 36px rgba(10, 102, 255, 0.15);
}

.control {
  min-height: 52px;
  border-radius: 15px;
  font-size: 15px;
}

.textarea {
  min-height: 112px;
}

.lineItems {
  display: grid;
  gap: 10px;
}

.photoHint {
  margin-top: -6px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.photoDrop {
  display: grid;
  gap: 5px;
  margin-top: 12px;
  padding: 18px;
  color: #1d4ed8;
  background: #f8fbff;
  border: 1px dashed #93c5fd;
  border-radius: 18px;
  cursor: pointer;
  transition: border-color 160ms ease, background-color 160ms ease, box-shadow 160ms ease;
}

.photoDrop:hover {
  background: #f1f7ff;
  border-color: #2f7cff;
  box-shadow: 0 12px 28px rgba(37, 99, 235, 0.08);
}

.photoDrop input {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.photoDrop span {
  font-weight: 900;
}

.photoDrop small {
  color: #64748b;
}

.photoPreviewGrid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 12px;
  margin-top: 14px;
}

.photoPreview {
  position: relative;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
}

.photoPreview img {
  width: 100%;
  height: 112px;
  object-fit: cover;
  display: block;
  background: #f1f5f9;
}

.photoMeta {
  display: grid;
  gap: 3px;
  padding: 10px 12px;
  min-width: 0;
}

.photoMeta strong {
  overflow: hidden;
  color: #071833;
  font-size: 13px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.photoMeta span {
  color: #64748b;
  font-size: 12px;
}

.photoRemove {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 30px;
  height: 30px;
  border: 1px solid rgba(239, 68, 68, 0.22);
  border-radius: 999px;
  color: #b91c1c;
  background: rgba(255, 255, 255, 0.94);
  cursor: pointer;
  font-size: 18px;
  font-weight: 900;
  line-height: 1;
}

.lineItemCard {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 118px 112px;
  gap: 12px;
  align-items: center;
  padding: 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  transition: border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
}

.lineItemCard:hover {
  background: #fbfdff;
  border-color: #cbd5e1;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
}

.lineMain {
  display: flex;
  align-items: center;
  min-width: 0;
}

.lineName {
  display: grid;
  gap: 4px;
  min-width: 0;
}

.lineName strong {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.fixedQty {
  display: grid;
  gap: 2px;
  min-height: 42px;
  padding: 6px 12px;
  text-align: center;
  background: #fff;
  border: 1px solid #d8e0eb;
  border-radius: 14px;
}

.fixedQty span {
  color: #64748b;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.fixedQty strong {
  color: #071833;
  font-size: 15px;
}

.priceBox {
  display: grid;
  gap: 2px;
  min-width: 0;
  text-align: right;
}

.priceBox span {
  color: #64748b;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.priceBox strong {
  color: #071833;
  font-size: 15px;
}

.linkButton {
  width: fit-content;
  padding: 0;
  color: #0a66ff;
  background: transparent;
  border: 0;
  cursor: pointer;
  font-weight: 800;
}

.linkButton:hover {
  color: #064fc7;
  text-decoration: underline;
}

.summaryCard {
  top: 18px;
  border-color: rgba(10, 102, 255, 0.12);
}

.summaryCard .summaryList {
  margin-top: 18px;
}

.summaryTotal {
  background: linear-gradient(180deg, #eaf2ff 0%, #f5f9ff 100%);
  border-color: #cfe1ff;
}

.summaryNote {
  margin-top: 12px;
  color: #64748b;
  font-size: 12px;
  line-height: 1.45;
}

.summaryCta {
  background: linear-gradient(180deg, #2f7cff 0%, #0a66ff 100%);
  border-color: #0a66ff;
}

.orderCard {
  display: grid;
  gap: 16px;
  padding: 22px;
}

.itemsBlockHead {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.paymentBox,
.reviewBox {
  margin-top: 18px;
  padding: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
}

.paymentHead,
.paymentReady,
.paymentPaid {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}

.paymentBadge {
  display: inline-flex;
  align-items: center;
  min-height: 28px;
  padding: 5px 11px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 900;
  white-space: nowrap;
}

.paymentBadge.paid {
  color: #166534;
  background: #dcfce7;
  border: 1px solid #bbf7d0;
}

.paymentBadge.pending {
  color: #92400e;
  background: #fef3c7;
  border: 1px solid #fde68a;
}

.paymentReady,
.paymentPaid,
.paymentUnavailable {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #e2e8f0;
}

.paymentAmountLabel,
.paymentPaid span {
  color: #64748b;
  font-size: 12px;
  font-weight: 800;
}

.paymentAmount {
  color: #071833;
  font-size: 28px;
  font-weight: 950;
  letter-spacing: -0.03em;
}

.paymentPaid {
  justify-content: flex-start;
  flex-wrap: wrap;
}

.paymentPaid div {
  display: grid;
  gap: 4px;
  min-width: 150px;
}

.paymentPaid strong {
  color: #071833;
}

.paymentUnavailable {
  color: #64748b;
  font-size: 14px;
  line-height: 1.5;
}

.payButton {
  min-width: 132px;
  justify-content: center;
}

.reviewCard,
.reviewForm {
  display: grid;
  gap: 12px;
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #e2e8f0;
}

.reviewTop {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.reviewLabel {
  color: #0f172a;
  font-weight: 900;
}

.stars {
  margin-top: 4px;
  color: #f59e0b;
  font-size: 20px;
  letter-spacing: 1px;
}

.reviewComment {
  margin: 0;
  color: #334155;
  line-height: 1.55;
}

.ratingButtons {
  display: flex;
  gap: 4px;
}

.starButton {
  width: 38px;
  height: 38px;
  border: 1px solid #d8e0eb;
  border-radius: 12px;
  color: #94a3b8;
  background: #fff;
  cursor: pointer;
  font-size: 20px;
  transition: color 160ms ease, border-color 160ms ease, background-color 160ms ease;
}

.starButton:hover,
.starButton.active {
  color: #f59e0b;
  border-color: #fde68a;
  background: #fffbeb;
}

.reviewTextarea {
  min-height: 92px;
}

.orderItems.compact {
  gap: 7px;
}

.orderItems.compact .orderItem {
  background: #fff;
}

.btnGhost {
  min-height: 46px;
  background: rgba(255, 255, 255, 0.82);
}

.formCard .cardHead,
.formCard .cardHead {
  border-bottom: 0;
}

@media (max-width: 980px) {
  .dashboardGrid,
  .requestTypeGrid,
  .summaryCard {
    position: static;
  }

  .lineItemCard {
    grid-template-columns: 1fr 118px 1fr 1fr;
  }
}

@media (max-width: 720px) {
  .topActions {
    width: 100%;
    justify-content: space-between;
  }

  .lineItemCard {
    grid-template-columns: 1fr;
  }

  .paymentHead,
  .paymentReady {
    align-items: flex-start;
    flex-direction: column;
  }

  .lineMain {
    align-items: flex-start;
    flex-direction: column;
  }

  .priceBox {
    text-align: left;
  }

}
</style>
