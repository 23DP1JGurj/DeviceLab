<template>
  <div class="page">
    <DashboardTopbar title="Atsauksmes" subtitle="Visas klientu atsauksmes par servisu." back-to="/admin" />

    <div class="sectionHeader">
      <div class="sectionTitle">Atsauksmju saraksts</div>
      <div class="muted">Kopā: {{ total }}</div>
    </div>

    <div v-if="loading" class="card muted">Ielādējam atsauksmes...</div>
    <div v-else-if="error" class="card"><div class="msg">{{ error }}</div></div>
    <div v-else-if="reviews.length === 0" class="card muted">Atsauksmju vēl nav.</div>

    <div v-else class="stack">
      <article class="card" v-for="review in reviews" :key="review.id">
        <div class="rowTop">
          <div class="mainText">
            <div class="stars">{{ starText(review.rating) }}</div>
            <p v-if="review.comment" class="comment">{{ review.comment }}</p>
            <p v-else class="comment muted">Komentārs nav pievienots.</p>
          </div>
          <div class="muted">{{ formatDate(review.created_at) }}</div>
        </div>
        <div class="chips">
          <span class="chip">Klients: {{ review.user?.name || review.user_id }}</span>
          <span class="chip">Darbinieks: {{ review.staff?.name || 'nav piešķirts' }}</span>
          <span class="chip">Filiāle: {{ review.branch?.name || review.branch_id }}</span>
          <span class="chip">Pasūtījums: {{ review.order?.order_number || review.order_id }}</span>
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
const reviews = ref([])
const total = ref(0)
const loading = ref(false)
const error = ref('')

function starText(rating) {
  const value = Math.max(0, Math.min(5, Number(rating || 0)))
  return '★'.repeat(value) + '☆'.repeat(5 - value)
}

function formatDate(value) {
  try {
    return new Date(value).toLocaleString()
  } catch {
    return value
  }
}

async function loadReviews() {
  loading.value = true
  error.value = ''
  try {
    const response = await authFetch('/api/admin/reviews')
    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await router.push({ path: '/login', query: { redirect: '/admin/reviews' } })
        return
      }
      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt atsauksmes.'))
    }
    const json = await response.json()
    reviews.value = json.data ?? []
    total.value = json.total ?? reviews.value.length
  } catch (e) {
    error.value = (e?.message || 'Neizdevās ielādēt atsauksmes.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

onMounted(loadReviews)
</script>

<style scoped src="./adminPanel.css"></style>
