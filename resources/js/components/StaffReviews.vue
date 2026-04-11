<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Atsauksmes</h1>
        <div class="subtitle">Klientu vērtējumi par servisu, filiāli un darbinieku.</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="summaryGrid">
      <div class="card statCard">
        <span>Vidējais vērtējums</span>
        <strong>{{ averageRating ? averageRating.toFixed(2) : '—' }}</strong>
      </div>
      <div class="card statCard">
        <span>Atsauksmes kopā</span>
        <strong>{{ total }}</strong>
      </div>
    </div>

    <div class="sectionHeader">
      <div class="sectionTitle">Atsauksmju saraksts</div>
    </div>

    <div v-if="loading" class="card">
      <div class="muted">Ielādējam atsauksmes...</div>
    </div>

    <div v-else-if="listError" class="card">
      <div class="msg">{{ listError }}</div>
    </div>

    <div v-else-if="reviews.length === 0" class="card">
      <div class="muted">Atsauksmju vēl nav.</div>
    </div>

    <div v-else class="reviewStack">
      <article class="card reviewCard" v-for="review in reviews" :key="review.id">
        <div class="reviewTop">
          <div>
            <div class="stars">{{ starText(review.rating) }}</div>
            <p v-if="review.comment" class="comment">{{ review.comment }}</p>
            <p v-else class="comment muted">Komentārs nav pievienots.</p>
          </div>
          <div class="date">{{ formatDate(review.created_at) }}</div>
        </div>

        <div class="chips">
          <span class="chip">Klients: {{ review.user?.name || review.user_id }}</span>
          <span class="chip">Pasūtījums: {{ review.order?.order_number || review.order_id }}</span>
          <span class="chip">Filiāle: {{ review.branch?.name || review.branch_id }}</span>
          <span class="chip">Darbinieks: {{ review.staff?.name || 'nav piešķirts' }}</span>
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

const reviews = ref([])
const total = ref(0)
const averageRating = ref(0)
const loading = ref(false)
const listError = ref('')

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
  listError.value = ''

  try {
    const response = await authFetch('/api/staff/reviews')

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        await router.push({ path: '/login', query: { redirect: '/staff/reviews' } })
        return
      }

      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt atsauksmes.'))
    }

    const json = await response.json()
    reviews.value = json.data ?? []
    total.value = json.total ?? reviews.value.length
    averageRating.value = Number(json.average_rating || 0)
  } catch (error) {
    reviews.value = []
    total.value = 0
    averageRating.value = 0
    listError.value = (error?.message || 'Neizdevās ielādēt atsauksmes.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

onMounted(loadReviews)
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue"; background: radial-gradient(1200px 600px at 20% 0%, #eef6ff 0%, #f6f8fb 45%, #f7f7f7 100%); color: #0f172a; }
.page { max-width: 1120px; margin: 0 auto; padding: 22px 18px 34px; }
.topbar { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 16px; }
.titleBlock { min-width: 0; }
.h1 { margin: 0; font-size: 34px; letter-spacing: -0.02em; }
.subtitle, .muted { color: #64748b; font-size: 14px; }
.subtitle { margin-top: 6px; }
.topActions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.summaryGrid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin: 18px 0; }
.card { background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 18px; box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07); padding: 18px; }
.statCard { display: grid; gap: 8px; }
.statCard span { color: #64748b; font-size: 13px; font-weight: 800; }
.statCard strong { color: #071833; font-size: 30px; line-height: 1; }
.sectionHeader { display: flex; align-items: center; justify-content: space-between; margin: 22px 0 12px; padding: 0 2px; }
.sectionTitle { font-size: 20px; font-weight: 900; }
.reviewStack { display: grid; gap: 14px; }
.reviewTop { display: flex; justify-content: space-between; gap: 16px; }
.stars { color: #f59e0b; font-size: 22px; letter-spacing: 1px; }
.comment { margin: 10px 0 0; color: #334155; line-height: 1.55; }
.date { color: #64748b; font-size: 13px; white-space: nowrap; }
.chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
.chip { padding: 5px 10px; color: #334155; background: rgba(148, 163, 184, 0.18); border: 1px solid rgba(148, 163, 184, 0.30); border-radius: 999px; font-size: 12px; }
.btn { border: 1px solid rgba(15, 23, 42, 0.14); background: #fff; color: #0f172a; padding: 10px 14px; border-radius: 12px; cursor: pointer; font-weight: 800; text-decoration: none; }
.btnGhost { background: transparent; }
.msg { color: #b91c1c; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.18); padding: 9px 11px; border-radius: 12px; font-size: 13px; }
@media (max-width: 760px) {
  .topbar, .reviewTop { align-items: flex-start; flex-direction: column; }
  .summaryGrid { grid-template-columns: 1fr; }
}
</style>
