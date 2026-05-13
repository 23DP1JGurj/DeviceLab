<template>
  <div class="page">
    <DashboardTopbar :title="order?.order_number || 'Pasūtījums'" subtitle="Detalizēts pasūtījuma pārskats." :back-to="backTo" />

    <div v-if="loading" class="card muted">Ielādējam pasūtījumu...</div>
    <div v-else-if="error" class="card"><div class="msg">{{ error }}</div></div>

    <template v-else-if="order">
      <section class="card heroCard">
        <div class="heroMain">
          <div>
            <div class="titleLine">
              <h2>{{ order.order_number }}</h2>
              <span class="badge">{{ statusLabel(order.status) }}</span>
              <span :class="['badge', isPaid(order) ? 'badgePaid' : 'badgePending']">
                {{ isPaid(order) ? 'Apmaksāts' : 'Nav apmaksāts' }}
              </span>
            </div>
            <p>{{ order.problem_description || 'Apraksts nav norādīts.' }}</p>
          </div>
          <div class="total">
            <span>Galīgā summa</span>
            <strong>{{ formatMoney(order.final_cost) }}</strong>
          </div>
        </div>
      </section>

      <section class="infoGrid">
        <div class="card infoCard"><span>Klients</span><strong>{{ order.user?.name || order.user_id }}</strong><small>{{ order.user?.email }}</small></div>
        <div class="card infoCard"><span>Ierīce</span><strong>{{ formatDevice(order.device) }}</strong></div>
        <div class="card infoCard"><span>Filiāle</span><strong>{{ order.branch?.name || order.branch_id }}</strong><small>{{ order.branch?.address }}</small></div>
        <div class="card infoCard"><span>Darbinieks</span><strong>{{ order.assigned_staff?.name || 'nav piešķirts' }}</strong></div>
      </section>

      <section class="contentGrid">
        <div class="mainStack">
          <div class="card">
            <div class="sectionTitle">Pievienotie fotoattēli</div>
            <div v-if="(order.attachments || []).length === 0" class="muted mt12">Fotoattēli nav pievienoti.</div>
            <div v-else class="attachmentGrid">
              <a
                v-for="attachment in order.attachments"
                :key="attachment.id"
                class="attachmentThumb"
                :href="attachment.url"
                target="_blank"
                rel="noopener noreferrer"
              >
                <img :src="attachment.url" :alt="attachment.original_name || 'Ierīces fotoattēls'" />
                <span>{{ attachment.original_name || 'Fotoattēls' }}</span>
              </a>
            </div>
          </div>

          <div class="card">
            <div class="sectionTitle">Pakalpojumi un detaļas</div>
            <div v-if="(order.items || []).length === 0" class="muted mt12">Pozīciju vēl nav.</div>
            <div v-else class="itemList">
              <div class="itemLine" v-for="item in order.items" :key="item.id">
                <b>{{ itemName(item) }}</b>
                <span>{{ item.quantity }} × {{ formatMoney(item.unit_price) }}</span>
                <strong>{{ formatMoney(item.line_total) }}</strong>
              </div>
            </div>
          </div>

          <OrderStatusTimeline
            :histories="order.status_history"
            :current-status="order.status"
            title="Statusa vēsture"
          />
        </div>

        <aside class="sideStack">
          <div class="card">
            <div class="sectionTitle">Apmaksa</div>
            <div v-if="isPaid(order)" class="paymentState">
              <span class="badge badgePaid">Apmaksāts</span>
              <strong>{{ formatMoney(order.payment?.amount ?? order.final_cost) }}</strong>
              <small v-if="order.payment?.paid_at">{{ formatDate(order.payment.paid_at) }}</small>
            </div>
            <div v-else-if="mode === 'client' && order.status === 'ready' && Number(order.final_cost || 0) > 0" class="paymentState">
              <strong>{{ formatMoney(order.final_cost) }}</strong>
              <button class="btn btnPrimary" type="button" @click="payOrder" :disabled="paying">
                {{ paying ? 'Apstrādā...' : 'Apmaksāt' }}
              </button>
            </div>
            <div v-else class="muted mt12">Rēķins būs pieejams, kad pasūtījums būs gatavs saņemšanai.</div>
            <div v-if="paymentMessage" class="msg ok mt12">{{ paymentMessage }}</div>
          </div>

          <div v-if="mode === 'client' && canCancel" class="card">
            <div class="sectionTitle">Pieteikuma atcelšana</div>
            <p class="muted mt12">Pieteikumu var atcelt, kamēr to vēl nav pieņēmis darbinieks.</p>
            <button class="btn btnDanger mt12" type="button" @click="cancelOrder" :disabled="cancelling">
              {{ cancelling ? 'Atceļ...' : 'Atcelt pieteikumu' }}
            </button>
            <div v-if="cancelMessage" class="msg ok mt12">{{ cancelMessage }}</div>
          </div>

          <div class="card">
            <div class="sectionTitle">Atsauksme</div>
            <div v-if="order.review" class="reviewBlock">
              <div class="stars">{{ starText(order.review.rating) }}</div>
              <p v-if="order.review.comment">{{ order.review.comment }}</p>
              <small>{{ formatDate(order.review.created_at) }}</small>
            </div>
            <form v-else-if="mode === 'client' && canReview" class="reviewForm" @submit.prevent="submitReview">
              <div class="ratingButtons">
                <button
                  v-for="rating in 5"
                  :key="rating"
                  type="button"
                  :class="['starButton', { active: visibleReviewRating >= rating }]"
                  @mouseenter="reviewHoverRating = rating"
                  @mouseleave="reviewHoverRating = 0"
                  @click="review.rating = rating"
                >★</button>
              </div>
              <textarea class="control textarea" v-model.trim="review.comment" rows="3" placeholder="Komentārs nav obligāts"></textarea>
              <button class="btn btnPrimary" type="submit" :disabled="reviewing || !review.rating">
                {{ reviewing ? 'Iesniedz...' : 'Iesniegt atsauksmi' }}
              </button>
            </form>
            <div v-else class="muted mt12">Atsauksmi var atstāt pēc pabeigta un apmaksāta pasūtījuma.</div>
            <div v-if="reviewMessage" class="msg ok mt12">{{ reviewMessage }}</div>
          </div>
        </aside>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authFetch, extractErrorMessage } from '../auth'
import { formatDevice } from '../deviceFormat'
import { statusLabel } from '../orderStatus'
import DashboardTopbar from './DashboardTopbar.vue'
import OrderStatusTimeline from './OrderStatusTimeline.vue'

const props = defineProps({
  mode: { type: String, required: true },
  endpointBase: { type: String, required: true },
  backTo: { type: String, required: true },
})

const route = useRoute()
const router = useRouter()
const order = ref(null)
const loading = ref(false)
const error = ref('')
const paying = ref(false)
const cancelling = ref(false)
const paymentMessage = ref('')
const cancelMessage = ref('')
const reviewing = ref(false)
const reviewMessage = ref('')
const reviewHoverRating = ref(0)
const review = reactive({ rating: 0, comment: '' })

const canReview = computed(() => order.value?.status === 'done' && order.value?.payment?.status === 'paid')
const visibleReviewRating = computed(() => reviewHoverRating.value || review.rating)
const canCancel = computed(() => (
  order.value?.status === 'new'
  && !order.value?.assigned_staff_id
  && order.value?.payment?.status !== 'paid'
))

function formatMoney(value) {
  return `${Number(value || 0).toFixed(2)} €`
}

function formatDate(value) {
  try {
    return new Date(value).toLocaleString()
  } catch {
    return value
  }
}

function itemName(item) {
  return item.item_type === 'service'
    ? (item.service?.name || `#${item.service_id}`)
    : (item.part?.name || `#${item.part_id}`)
}

function isPaid(value) {
  return value.payment?.status === 'paid'
}

function starText(rating) {
  const value = Math.max(0, Math.min(5, Number(rating || 0)))
  return '★'.repeat(value) + '☆'.repeat(5 - value)
}

async function loadOrder() {
  loading.value = true
  error.value = ''
  try {
    const response = await authFetch(`${props.endpointBase}/${route.params.id}`)
    if (!response.ok) {
      if (response.status === 401) {
        await router.push({ path: '/login', query: { redirect: route.fullPath } })
        return
      }
      if (response.status === 403) {
        await router.push(props.backTo)
        return
      }
      throw new Error(await extractErrorMessage(response, 'Neizdevās ielādēt pasūtījumu.'))
    }
    order.value = await response.json()
  } catch (e) {
    error.value = (e?.message || 'Neizdevās ielādēt pasūtījumu.').slice(0, 260)
  } finally {
    loading.value = false
  }
}

async function payOrder() {
  if (!order.value) return
  paying.value = true
  paymentMessage.value = ''
  try {
    const response = await authFetch(`/api/my/orders/${order.value.id}/pay`, { method: 'POST' })
    if (!response.ok) throw new Error(await extractErrorMessage(response, 'Neizdevās apmaksāt pasūtījumu.'))
    order.value = await response.json()
    paymentMessage.value = 'Pasūtījums apmaksāts.'
  } catch (e) {
    error.value = (e?.message || 'Neizdevās apmaksāt pasūtījumu.').slice(0, 260)
  } finally {
    paying.value = false
  }
}

async function cancelOrder() {
  if (!order.value || !confirm('Vai tiešām vēlaties atcelt šo pieteikumu?')) return

  cancelling.value = true
  cancelMessage.value = ''

  try {
    const response = await authFetch(`/api/my/orders/${order.value.id}/cancel`, { method: 'PATCH' })
    if (!response.ok) throw new Error(await extractErrorMessage(response, 'Neizdevās atcelt pieteikumu.'))
    order.value = await response.json()
    cancelMessage.value = 'Pieteikums atcelts.'
  } catch (e) {
    error.value = (e?.message || 'Neizdevās atcelt pieteikumu.').slice(0, 260)
  } finally {
    cancelling.value = false
  }
}

async function submitReview() {
  if (!order.value) return
  reviewing.value = true
  reviewMessage.value = ''
  try {
    const response = await authFetch(`/api/my/orders/${order.value.id}/review`, {
      method: 'POST',
      json: { rating: review.rating, comment: review.comment || null },
    })
    if (!response.ok) throw new Error(await extractErrorMessage(response, 'Neizdevās iesniegt atsauksmi.'))
    order.value.review = await response.json()
    review.rating = 0
    reviewHoverRating.value = 0
    review.comment = ''
    reviewMessage.value = 'Paldies! Atsauksme iesniegta.'
  } catch (e) {
    error.value = (e?.message || 'Neizdevās iesniegt atsauksmi.').slice(0, 260)
  } finally {
    reviewing.value = false
  }
}

onMounted(loadOrder)
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) { margin: 0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue"; background: radial-gradient(1200px 600px at 20% 0%, #eef6ff 0%, #f6f8fb 45%, #f7f7f7 100%); color: #0f172a; }
.page { max-width: 1180px; margin: 0 auto; padding: 22px 18px 36px; }
.topbar, .heroMain, .titleLine { display: flex; gap: 16px; }
.topbar { justify-content: space-between; align-items: flex-end; margin-bottom: 18px; }
.titleBlock { min-width: 0; }
.h1 { margin: 0; font-size: 34px; letter-spacing: -0.02em; }
.subtitle, .muted, small { color: #64748b; font-size: 14px; }
.subtitle { margin-top: 6px; }
.topActions { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.card { background: rgba(255,255,255,.95); border: 1px solid rgba(15,23,42,.08); border-radius: 18px; box-shadow: 0 14px 34px rgba(15,23,42,.07); padding: 18px; }
.heroCard { margin-bottom: 14px; }
.heroMain { justify-content: space-between; align-items: flex-start; }
.heroMain h2 { margin: 0; font-size: 26px; }
.heroMain p { margin: 10px 0 0; color: #475569; line-height: 1.55; }
.titleLine { align-items: center; flex-wrap: wrap; }
.total { display: grid; gap: 4px; text-align: right; }
.total span { color: #64748b; font-size: 12px; font-weight: 800; }
.total strong { font-size: 30px; }
.infoGrid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 14px; }
.infoCard { display: grid; gap: 6px; }
.infoCard span { color: #64748b; font-size: 12px; font-weight: 800; }
.infoCard strong { min-width: 0; color: #071833; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.contentGrid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(290px, .75fr); gap: 14px; }
.mainStack, .sideStack { display: grid; align-content: start; gap: 14px; }
.sectionTitle { font-size: 18px; font-weight: 900; }
.mt12 { margin-top: 12px; }
.attachmentGrid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-top: 14px; }
.attachmentThumb { overflow: hidden; color: #0f172a; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; text-decoration: none; transition: transform 160ms ease, border-color 160ms ease, box-shadow 160ms ease; }
.attachmentThumb:hover { transform: translateY(-1px); border-color: #93c5fd; box-shadow: 0 12px 26px rgba(15,23,42,.08); }
.attachmentThumb img { display: block; width: 100%; height: 120px; object-fit: cover; background: #f1f5f9; }
.attachmentThumb span { display: block; overflow: hidden; padding: 9px 10px; color: #334155; font-size: 12px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
.itemList { display: grid; gap: 8px; margin-top: 14px; }
.itemLine { display: grid; grid-template-columns: minmax(0,1fr) auto auto; align-items: center; gap: 12px; padding: 11px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; }
.badge { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; border: 1px solid rgba(148,163,184,.3); background: rgba(148,163,184,.18); font-size: 12px; font-weight: 900; }
.badgePaid { color: #166534; background: #dcfce7; border-color: #bbf7d0; }
.badgePending { color: #92400e; background: #fef3c7; border-color: #fde68a; }
.paymentState, .reviewBlock, .reviewForm { display: grid; gap: 12px; margin-top: 14px; }
.stars { color: #f59e0b; font-size: 22px; letter-spacing: 1px; }
.reviewBlock p { margin: 0; color: #334155; line-height: 1.55; }
.ratingButtons { display: flex; gap: 4px; }
.starButton { width: 38px; height: 38px; border: 1px solid #d8e0eb; border-radius: 12px; color: #94a3b8; background: #fff; cursor: pointer; font-size: 20px; }
.starButton.active { color: #f59e0b; border-color: #fde68a; background: #fffbeb; }
.control { width: 100%; padding: 10px 12px; border: 1px solid rgba(15,23,42,.14); border-radius: 12px; background: #fff; }
.textarea { resize: vertical; line-height: 1.5; }
.btn { border: 1px solid rgba(15,23,42,.14); background: #fff; color: #0f172a; padding: 10px 14px; border-radius: 12px; cursor: pointer; font-weight: 800; text-decoration: none; }
.btnPrimary { background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border-color: rgba(29,78,216,.60); box-shadow: 0 10px 18px rgba(37,99,235,.22); }
.btnDanger { color: #be123c; background: #fff1f2; border-color: #fecdd3; }
.btnGhost { background: transparent; }
.msg { color: #b91c1c; background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.18); padding: 9px 11px; border-radius: 12px; font-size: 13px; }
.msg.ok { color: #166534; background: rgba(34,197,94,.10); border-color: rgba(34,197,94,.22); }
@media (max-width: 920px) {
  .topbar, .heroMain { align-items: flex-start; flex-direction: column; }
  .total { text-align: left; }
  .infoGrid, .contentGrid, .itemLine { grid-template-columns: 1fr; }
}
</style>
