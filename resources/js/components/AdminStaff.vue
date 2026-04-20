<template>
  <div class="page">
    <DashboardTopbar title="Darbinieki" subtitle="Darbinieku noslodze, specializācija un vērtējumi." back-to="/admin" />

    <div class="sectionHeader">
      <div class="sectionTitle">Darbinieku saraksts</div>
      <div class="muted">Kopā: {{ total }}</div>
    </div>

    <div v-if="loading" class="card muted">Ielādējam darbiniekus...</div>
    <div v-else-if="error" class="card"><div class="msg">{{ error }}</div></div>
    <div v-else-if="staff.length === 0" class="card muted">Darbinieku vēl nav.</div>

    <div v-else class="stack">
      <article class="card" v-for="person in staff" :key="person.id">
        <div class="rowTop">
          <div class="mainText">
            <div class="itemTitle">{{ person.name }}</div>
            <div class="description">{{ person.email }} · {{ person.phone || 'tālrunis nav norādīts' }}</div>
          </div>
          <div class="adminActions">
            <div class="rightValue">{{ formatRating(person.average_rating) }}</div>
            <span :class="['badge', person.is_blocked ? 'badgeBlocked' : 'badgeActive']">
              {{ person.is_blocked ? 'Bloķēts' : 'Aktīvs' }}
            </span>
            <button class="btn btnSmall" type="button" @click="toggleBlock(person)" :disabled="busyId === person.id">
              {{ person.is_blocked ? 'Atbloķēt' : 'Bloķēt' }}
            </button>
          </div>
        </div>
        <div class="chips">
          <span class="chip">Specializācija: {{ person.specialization || 'nav norādīta' }}</span>
          <span class="chip">Filiāle: {{ person.branch?.name || 'nav piesaistīta' }}</span>
          <span class="chip">Piešķirti: {{ person.assigned_orders_count }}</span>
          <span class="chip">Pabeigti: {{ person.completed_orders_count }}</span>
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
const staff = ref([])
const total = ref(0)
const loading = ref(false)
const error = ref('')
const busyId = ref(null)

function formatRating(value) {
  const rating = Number(value || 0)
  return rating > 0 ? rating.toFixed(2) : '—'
}

async function loadStaff() {
  loading.value = true
  error.value = ''
  try {
    const response = await authFetch('/api/admin/staff')
    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await router.push({ path: '/login', query: { redirect: '/admin/staff' } })
        return
      }
      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt darbiniekus.'))
    }
    const json = await response.json()
    staff.value = json.data ?? []
    total.value = json.total ?? staff.value.length
  } catch (e) {
    error.value = (e?.message || 'Neizdevās ielādēt darbiniekus.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

async function toggleBlock(person) {
  const action = person.is_blocked ? 'unblock' : 'block'
  const label = person.is_blocked ? 'atbloķēt' : 'bloķēt'

  if (!confirm(`Vai tiešām vēlaties ${label} šo lietotāju?`)) return

  busyId.value = person.id
  error.value = ''

  try {
    const response = await authFetch(`/api/admin/users/${person.id}/${action}`, { method: 'PATCH' })

    if (!response.ok) {
      throw new Error(await extractErrorMessage(response, 'Neizdevās mainīt lietotāja statusu.'))
    }

    await loadStaff()
  } catch (e) {
    error.value = (e?.message || 'Neizdevās mainīt lietotāja statusu.').slice(0, 260)
  } finally {
    busyId.value = null
  }
}

onMounted(loadStaff)
</script>

<style scoped src="./adminPanel.css"></style>
