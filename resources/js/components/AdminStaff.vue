<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Darbinieki</h1>
        <div class="subtitle">Darbinieku noslodze, specializācija un vērtējumi.</div>
      </div>
      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/admin">← Admin panelis</RouterLink>
        <AccountMenu />
      </div>
    </div>

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
          <div class="rightValue">{{ formatRating(person.average_rating) }}</div>
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
import { RouterLink, useRouter } from 'vue-router'
import { authFetch, extractErrorMessage } from '../auth'
import AccountMenu from './AccountMenu.vue'

const router = useRouter()
const staff = ref([])
const total = ref(0)
const loading = ref(false)
const error = ref('')

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

onMounted(loadStaff)
</script>

<style scoped src="./adminPanel.css"></style>
