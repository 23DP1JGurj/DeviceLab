<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">My Devices</h1>
        <div class="subtitle">Manage devices before creating an order</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Home</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="card">
      <div class="cardHead">
        <div class="cardTitle">Add device</div>
      </div>

      <div class="grid2">
        <label class="field">
          <div class="label">Type</div>
          <select class="control" v-model="form.type">
            <option value="phone">phone</option>
            <option value="laptop">laptop</option>
            <option value="tablet">tablet</option>
            <option value="other">other</option>
          </select>
        </label>

        <label class="field">
          <div class="label">Brand</div>
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
          <div class="label">Model</div>
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
          {{ creating ? 'Saving...' : 'Add device' }}
        </button>

        <div class="msg" v-if="createError">{{ createError }}</div>
        <div class="msg ok" v-else-if="createSuccess">{{ createSuccess }}</div>
      </div>
    </div>

    <div class="sectionHeader">
      <div class="sectionTitle">My devices</div>
      <div class="muted">Total: {{ devices.length }}</div>
    </div>

    <div v-if="listLoading" class="card">
      <div class="muted">Loading...</div>
    </div>

    <div v-else>
      <div v-if="listError" class="card">
        <div class="msg">{{ listError }}</div>
      </div>

      <div v-else-if="devices.length === 0" class="card">
        <div class="muted">No devices yet. Add one to create an order.</div>
      </div>

      <div class="card deviceCard" v-for="device in devices" :key="device.id">
        <div class="deviceTop">
          <div>
            <div class="deviceTitle">{{ formatDeviceLabel(device) }}</div>
          </div>

          <button class="btn btnDanger" type="button" @click="deleteDevice(device)" :disabled="deletingId === device.id">
            {{ deletingId === device.id ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
import AutocompleteInput from './AutocompleteInput.vue'
import { authFetch, extractErrorMessage, initAuth } from '../auth'
import { fetchDeviceBrands, fetchDeviceModels } from '../deviceCatalog'

const router = useRouter()

const devices = ref([])
const listLoading = ref(false)
const listError = ref('')

const creating = ref(false)
const createError = ref('')
const createSuccess = ref('')

const deletingId = ref(null)
const brandSuggestions = ref([])
const modelSuggestions = ref([])

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
  const parts = [device.brand, device.model].filter(Boolean)
  const title = parts.join(' ')
  return title ? `${title} (${device.type})` : `Device #${device.id} (${device.type})`
}

async function loadDevices() {
  listLoading.value = true
  listError.value = ''

  try {
    const res = await authFetch('/api/my/devices')

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/devices' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Unable to load devices.'))
    }

    devices.value = await res.json()
  } catch (e) {
    devices.value = []
    listError.value = (e?.message || 'Unable to load devices.').slice(0, 260)
  } finally {
    listLoading.value = false
  }
}

async function createDevice() {
  creating.value = true
  createError.value = ''
  createSuccess.value = ''

  try {
    const res = await authFetch('/api/my/devices', {
      method: 'POST',
      json: {
        type: form.type,
        brand: form.brand || null,
        model: form.model || null,
      },
    })

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/devices' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Unable to add device.'))
    }

    resetForm()
    createSuccess.value = 'Device added.'
    await loadDevices()
  } catch (e) {
    createError.value = (e?.message || 'Unable to add device.').slice(0, 260)
  } finally {
    creating.value = false
  }
}

async function deleteDevice(device) {
  deletingId.value = device.id
  createError.value = ''
  createSuccess.value = ''

  try {
    const res = await authFetch(`/api/my/devices/${device.id}`, {
      method: 'DELETE',
    })

    if (!res.ok) {
      if (res.status === 401) {
        await router.push({ path: '/login', query: { redirect: '/devices' } })
        return
      }

      throw new Error(await extractErrorMessage(res, 'Unable to delete device.'))
    }

    await loadDevices()
  } catch (e) {
    createError.value = (e?.message || 'Unable to delete device.').slice(0, 260)
  } finally {
    deletingId.value = null
  }
}

async function loadBrandSuggestions() {
  brandSuggestions.value = await fetchDeviceBrands(form.brand)
}

async function loadModelSuggestions() {
  modelSuggestions.value = await fetchDeviceModels(form.brand, form.model)
}

watch(
  () => form.brand,
  async () => {
    form.model = ''
    modelSuggestions.value = []
    await loadBrandSuggestions()
    await loadModelSuggestions()
  },
)

onMounted(async () => {
  await initAuth().catch(() => null)
  await loadBrandSuggestions()
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

.deviceCard {
  padding: 16px;
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
}

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

.btnDanger {
  background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
  color: #fff;
  border-color: rgba(220, 38, 38, 0.60);
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
.mt12 { margin-top: 12px; }
.mt16 { margin-top: 16px; }

@media (max-width: 920px) {
  .topbar { align-items: flex-start; flex-direction: column; }
  .grid2 { grid-template-columns: 1fr; }
  .deviceTop { align-items: flex-start; flex-direction: column; }
}
</style>

