<template>
  <div class="page">
    <DashboardTopbar title="Klienti" subtitle="Klientu konti un aktivitāte servisā." back-to="/admin" />

    <div class="sectionHeader">
      <div class="sectionTitle">Klientu saraksts</div>
      <div class="muted">Kopā: {{ total }}</div>
    </div>

    <div v-if="loading" class="card muted">Ielādējam klientus...</div>
    <div v-else-if="error" class="card"><div class="msg">{{ error }}</div></div>
    <div v-else-if="clients.length === 0" class="card muted">Klientu vēl nav.</div>

    <div v-else class="stack">
      <article class="card" v-for="client in clients" :key="client.id">
        <div class="rowTop">
          <div class="mainText">
            <div class="itemTitle">{{ client.name }}</div>
            <div class="description">{{ client.email }} · {{ client.phone || 'tālrunis nav norādīts' }}</div>
          </div>
          <div class="adminActions">
            <span :class="['badge', client.is_blocked ? 'badgeBlocked' : 'badgeActive']">
              {{ client.is_blocked ? 'Bloķēts' : 'Aktīvs' }}
            </span>
            <button class="btn btnSmall" type="button" @click="toggleBlock(client)" :disabled="busyId === client.id">
              {{ client.is_blocked ? 'Atbloķēt' : 'Bloķēt' }}
            </button>
          </div>
        </div>
        <div class="chips">
          <span class="chip">Ierīces: {{ client.devices_count }}</span>
          <span class="chip">Pasūtījumi: {{ client.orders_count }}</span>
          <span class="chip">Pēdējais pasūtījums: {{ formatDate(client.latest_order_date) }}</span>
          <span class="chip">Reģistrēts: {{ formatDate(client.created_at) }}</span>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { authFetch, extractErrorMessage } from '../auth'
import DashboardTopbar from './DashboardTopbar.vue'

const router = useRouter()
const clients = ref([])
const total = ref(0)
const loading = ref(false)
const error = ref('')
const busyId = ref(null)

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Date(value).toLocaleDateString()
  } catch {
    return value
  }
}

async function loadClients() {
  loading.value = true
  error.value = ''
  try {
    const response = await authFetch('/api/admin/clients')
    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await router.push({ path: '/login', query: { redirect: '/admin/clients' } })
        return
      }
      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt klientus.'))
    }
    const json = await response.json()
    clients.value = json.data ?? []
    total.value = json.total ?? clients.value.length
  } catch (e) {
    error.value = (e?.message || 'Neizdevās ielādēt klientus.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

async function toggleBlock(client) {
  const action = client.is_blocked ? 'unblock' : 'block'
  const label = client.is_blocked ? 'atbloķēt' : 'bloķēt'

  if (!confirm(`Vai tiešām vēlaties ${label} šo lietotāju?`)) return

  busyId.value = client.id
  error.value = ''

  try {
    const response = await authFetch(`/api/admin/users/${client.id}/${action}`, { method: 'PATCH' })

    if (!response.ok) {
      throw new Error(await extractErrorMessage(response, 'Neizdevās mainīt lietotāja statusu.'))
    }

    await loadClients()
  } catch (e) {
    error.value = (e?.message || 'Neizdevās mainīt lietotāja statusu.').slice(0, 260)
  } finally {
    busyId.value = null
  }
}

onMounted(loadClients)
</script>

<style scoped src="./adminPanel.css"></style>
