<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Admin panelis</h1>
        <div class="subtitle">Sistēmas kopsavilkums un ātra piekļuve pārskatam.</div>
      </div>
      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div v-if="loading" class="card muted">Ielādējam datus...</div>
    <div v-else-if="error" class="card"><div class="msg">{{ error }}</div></div>

    <template v-else>
      <div class="grid">
        <div class="card statCard"><span>Pasūtījumi kopā</span><strong>{{ summary.total_orders }}</strong></div>
        <div class="card statCard"><span>Aktīvie</span><strong>{{ summary.active_orders }}</strong></div>
        <div class="card statCard"><span>Pabeigtie</span><strong>{{ summary.completed_orders }}</strong></div>
        <div class="card statCard"><span>Ieņēmumi</span><strong>{{ formatMoney(summary.total_revenue) }}</strong></div>
        <div class="card statCard"><span>Apmaksāti</span><strong>{{ summary.paid_orders }}</strong></div>
        <div class="card statCard"><span>Vidējais vērtējums</span><strong>{{ summary.average_rating || '—' }}</strong></div>
        <div class="card statCard"><span>Klienti</span><strong>{{ summary.total_clients }}</strong></div>
        <div class="card statCard"><span>Darbinieki</span><strong>{{ summary.total_staff }}</strong></div>
      </div>

      <div class="sectionHeader">
        <div class="sectionTitle">Sadaļas</div>
      </div>

      <div class="grid two">
        <RouterLink class="card linkCard" to="/admin/orders">
          <div class="itemTitle">Visi pasūtījumi</div>
          <div class="description">Pasūtījumu statuss, klienti, darbinieki un apmaksa.</div>
        </RouterLink>
        <RouterLink class="card linkCard" to="/admin/clients">
          <div class="itemTitle">Klienti</div>
          <div class="description">Klientu saraksts, ierīces un pasūtījumu aktivitāte.</div>
        </RouterLink>
        <RouterLink class="card linkCard" to="/admin/staff">
          <div class="itemTitle">Darbinieki</div>
          <div class="description">Darbinieku noslodze, pabeigtie darbi un vērtējumi.</div>
        </RouterLink>
        <RouterLink class="card linkCard" to="/admin/reviews">
          <div class="itemTitle">Atsauksmes</div>
          <div class="description">Klientu atsauksmes par servisu un darbiniekiem.</div>
        </RouterLink>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { authFetch, extractErrorMessage } from '../auth'
import AccountMenu from './AccountMenu.vue'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const summary = ref({})

function formatMoney(value) {
  return `${Number(value || 0).toFixed(2)} €`
}

async function loadSummary() {
  loading.value = true
  error.value = ''
  try {
    const response = await authFetch('/api/admin/summary')
    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await router.push({ path: '/login', query: { redirect: '/admin' } })
        return
      }
      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt admin kopsavilkumu.'))
    }
    summary.value = await response.json()
  } catch (e) {
    error.value = (e?.message || 'Neizdevās ielādēt admin kopsavilkumu.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

onMounted(loadSummary)
</script>

<style scoped src="./adminPanel.css"></style>
