<template>
  <div class="page">
    <DashboardTopbar title="Manas ierīces" subtitle="Pārvaldi ierīces pirms pieteikuma izveides" />

    <section class="card formCard">
      <div class="cardHead">
        <div>
          <div class="cardTitle">Pievienot ierīci</div>
          <div class="cardSubtitle">Izvēlies tipu, zīmolu un modeli.</div>
        </div>
      </div>

      <div class="grid2">
        <label class="field">
          <div class="label">Tips</div>
          <select class="control" v-model="form.type">
            <option value="phone">Telefons</option>
            <option value="laptop">Portatīvais dators</option>
            <option value="tablet">Planšete</option>
            <option value="other">Cits</option>
          </select>
        </label>

        <label class="field">
          <div class="label">Zīmols</div>
          <AutocompleteInput
            v-model.trim="form.brand"
            :suggestions="brandSuggestions"
            placeholder="Apple, Samsung..."
            @input="loadBrandSuggestions"
            @select="loadModelSuggestions"
          />
        </label>
      </div>

      <div class="mt12">
        <label class="field">
          <div class="label">Modelis</div>
          <AutocompleteInput
            v-model.trim="form.model"
            placeholder="iPhone 12..."
            :suggestions="modelSuggestions"
            @input="loadModelSuggestions"
          />
        </label>
      </div>

      <div class="row mt16">
        <button class="btn btnPrimary" type="button" @click="createDevice" :disabled="creating">
          {{ creating ? 'Saglabā...' : 'Pievienot ierīci' }}
        </button>

        <div class="msg" v-if="createError">{{ createError }}</div>
        <div class="msg ok" v-else-if="createSuccess">{{ createSuccess }}</div>
      </div>
    </section>

    <section class="card listCard">
      <div class="listHead">
        <div>
          <div class="cardTitle">Manas ierīces</div>
          <div class="cardSubtitle">Saglabātās ierīces pieteikumu noformēšanai.</div>
        </div>
        <div class="countPill">{{ devices.length }}</div>
      </div>

      <div v-if="deleteError" class="msg mt12">{{ deleteError }}</div>

      <div v-if="listLoading" class="muted stateText">Ielādējam...</div>

      <div v-else-if="listError" class="msg mt12">{{ listError }}</div>

      <div v-else-if="devices.length === 0" class="emptyState">
        Nav pievienotu ierīču.
      </div>

      <div v-else class="deviceStack">
        <article class="deviceCard" v-for="device in devices" :key="device.id">
        <div class="deviceTop">
          <div>
            <div class="deviceTitle">{{ formatDeviceLabel(device) }}</div>
          </div>

          <button class="btn btnDanger" type="button" @click="deleteDevice(device)" :disabled="deletingId === device.id">
            {{ deletingId === device.id ? 'Dzēš...' : 'Dzēst' }}
          </button>
        </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AutocompleteInput from './AutocompleteInput.vue'
import DashboardTopbar from './DashboardTopbar.vue'
import { authFetch, extractErrorMessage, initAuth } from '../auth'
import { fetchDeviceBrands, fetchDeviceModelsByType } from '../deviceCatalog'
import { formatDevice } from '../deviceFormat'

const router = useRouter()

const devices = ref([])
const listLoading = ref(false)
const listError = ref('')

const creating = ref(false)
const createError = ref('')
const createSuccess = ref('')

const deletingId = ref(null)
const deleteError = ref('')
const brandSuggestions = ref([])
const modelSuggestions = ref([])
let brandSuggestionsTimer = null
let modelSuggestionsTimer = null

const form = reactive({
  type: 'phone',
  brand: '',
  model: '',
})

function resetForm() {
  form.type = 'phone'
  form.brand = ''
  form.model = ''
  modelSuggestions.value = []
}

function formatDeviceLabel(device) {
  return formatDevice(device)
}

async function loadDevices() {
  listLoading.value = true
  listError.value = ''
  deleteError.value = ''

  try {
    const res = await authFetch('/api/my/devices')

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/devices' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Neizdevās ielādēt ierīces.'))
    }

    devices.value = await res.json()
  } catch (e) {
    devices.value = []
    listError.value = (e?.message || 'Neizdevās ielādēt ierīces.').slice(0, 260)
  } finally {
    listLoading.value = false
  }
}

async function createDevice() {
  creating.value = true
  createError.value = ''
  createSuccess.value = ''
  deleteError.value = ''

  try {
    const res = await authFetch('/api/my/devices', {
      method: 'POST',
      json: {
        type: form.type,
        component_type: null,
        brand: form.brand || null,
        model: form.model || null,
        specs: null,
      },
    })

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/devices' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Neizdevās pievienot ierīci.'))
    }

    resetForm()
    createSuccess.value = 'Ierīce pievienota.'
    await loadDevices()
  } catch (e) {
    createError.value = (e?.message || 'Neizdevās pievienot ierīci.').slice(0, 260)
  } finally {
    creating.value = false
  }
}

async function deleteDevice(device) {
  deletingId.value = device.id
  createError.value = ''
  createSuccess.value = ''
  deleteError.value = ''

  try {
    const res = await authFetch(`/api/my/devices/${device.id}`, {
      method: 'DELETE',
    })

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/devices' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Neizdevās dzēst ierīci.'))
    }

    await loadDevices()
  } catch (e) {
    deleteError.value = (e?.message || 'Neizdevās dzēst ierīci.').slice(0, 260)
  } finally {
    deletingId.value = null
  }
}

async function loadBrandSuggestions() {
  clearTimeout(brandSuggestionsTimer)
  brandSuggestionsTimer = setTimeout(async () => {
    brandSuggestions.value = await fetchDeviceBrands(form.type, form.brand)
  }, 250)
}

async function loadModelSuggestions() {
  clearTimeout(modelSuggestionsTimer)
  modelSuggestionsTimer = setTimeout(async () => {
    modelSuggestions.value = await fetchDeviceModelsByType(form.type, form.brand, form.model)
  }, 250)
}

watch(
  () => form.brand,
  async () => {
    form.model = ''
    modelSuggestions.value = []
    loadBrandSuggestions()
    loadModelSuggestions()
  },
)

watch(
  () => form.type,
  () => {
    brandSuggestions.value = []
    modelSuggestions.value = []
    loadBrandSuggestions()
    loadModelSuggestions()
  },
)

onMounted(async () => {
  await initAuth().catch(() => null)
  loadBrandSuggestions()
  await loadDevices()
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
  max-width: 1120px;
  margin: 0 auto;
  padding: 22px 18px 38px;
}

.card {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 22px;
  box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
  padding: 24px;
}

.formCard,
.listCard {
  margin-top: 18px;
}

.cardHead {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 18px;
}

.cardTitle {
  color: #071833;
  font-weight: 950;
  font-size: 22px;
  letter-spacing: -0.02em;
}

.cardSubtitle {
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
}

.listHead {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
  margin-bottom: 16px;
}

.countPill {
  min-width: 38px;
  padding: 7px 12px;
  border-radius: 999px;
  border: 1px solid rgba(37, 99, 235, 0.16);
  background: rgba(37, 99, 235, 0.08);
  color: #1d4ed8;
  font-weight: 900;
  text-align: center;
}

.field { min-width: 0; }
.label {
  display: block;
  font-size: 12px;
  color: #475569;
  margin-bottom: 6px;
  font-weight: 800;
}
.control {
  width: 100%;
  min-width: 0;
  padding: 13px 15px;
  border-radius: 15px;
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  outline: none;
  font: inherit;
  color: #071833;
}
.control:focus {
  border-color: rgba(37, 99, 235, 0.6);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.grid2 {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 14px;
}

.deviceCard {
  padding: 16px 18px;
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 18px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.deviceStack {
  display: grid;
  gap: 12px;
}

.deviceTop {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.deviceTitle {
  font-size: 18px;
  font-weight: 900;
  color: #071833;
}

.btn {
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  color: #0f172a;
  min-height: 44px;
  padding: 10px 16px;
  border-radius: 13px;
  cursor: pointer;
  font-weight: 850;
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
  min-height: 40px;
  background: #fff1f2;
  color: #be123c;
  border-color: #fecdd3;
}

.row {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.stateText {
  padding: 10px 0;
}

.emptyState {
  padding: 18px;
  border-radius: 16px;
  border: 1px dashed rgba(148, 163, 184, 0.34);
  background: rgba(248, 250, 252, 0.72);
  color: #64748b;
  font-weight: 700;
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
.mt12 { margin-top: 12px; }
.mt16 { margin-top: 16px; }

@media (max-width: 920px) {
  .grid2 { grid-template-columns: 1fr; }
  .deviceTop { align-items: flex-start; flex-direction: column; }
  .deviceTop .btn { width: 100%; }
}
</style>

