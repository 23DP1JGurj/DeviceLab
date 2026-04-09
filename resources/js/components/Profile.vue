<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Profils</h1>
        <div class="subtitle">Atjauno savus konta datus un kontaktinformāciju</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="card">
      <div class="cardHead">
        <div>
          <div class="cardTitle">Konta informācija</div>
          <div class="cardSubtitle">Izmaiņas uzreiz parādīsies arī konta izvēlnē.</div>
        </div>
      </div>

      <form class="formGrid" @submit.prevent="submit">
        <label class="field">
          <span class="label">Vārds</span>
          <input class="control" v-model.trim="form.name" type="text" autocomplete="name" />
        </label>

        <label class="field">
          <span class="label">E-pasts</span>
          <input class="control" v-model.trim="form.email" type="email" autocomplete="email" />
        </label>

        <label class="field fieldWide">
          <span class="label">Tālrunis</span>
          <input class="control" v-model.trim="form.phone" type="text" autocomplete="tel" />
        </label>

        <div v-if="error" class="message error">{{ error }}</div>
        <div v-else-if="success" class="message ok">{{ success }}</div>

        <div class="actions">
          <button class="btn btnPrimary" type="submit" :disabled="saving">
            {{ saving ? 'Saglabā...' : 'Saglabāt' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
import { currentUser, initAuth, updateProfile } from '../auth'

const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  phone: '',
})

const saving = ref(false)
const error = ref('')
const success = ref('')

const user = computed(() => currentUser.value ?? {
  name: '',
  email: '',
  phone: '',
  role: '',
})

function syncFormFromUser(value) {
  form.name = value?.name ?? ''
  form.email = value?.email ?? ''
  form.phone = value?.phone ?? ''
}

function validateForm() {
  if (form.name.trim().length < 2) {
    return 'Vārdam jābūt vismaz 2 rakstzīmes garam.'
  }

  if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) {
    return 'Ievadi derīgu e-pasta adresi.'
  }

  if (form.phone.trim() && form.phone.trim().length < 6) {
    return 'Tālrunim jābūt vismaz 6 rakstzīmes garam.'
  }

  return ''
}

async function submit() {
  error.value = ''
  success.value = ''

  const validationError = validateForm()
  if (validationError) {
    error.value = validationError
    return
  }

  saving.value = true

  try {
    await updateProfile({
      name: form.name.trim(),
      email: form.email.trim(),
      phone: form.phone.trim() || null,
    })

    success.value = 'Profils saglabāts.'
  } catch (err) {
    const message = err?.message || 'Neizdevās saglabāt profilu.'

    if (message.toLowerCase().includes('unauthenticated')) {
      await router.push({ path: '/login', query: { redirect: '/profile' } })
      return
    }

    error.value = message
  } finally {
    saving.value = false
  }
}

watch(user, (value) => {
  syncFormFromUser(value)
}, { immediate: true })

onMounted(async () => {
  await initAuth().catch(() => null)
  syncFormFromUser(user.value)
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
  margin-bottom: 14px;
}

.cardTitle {
  font-weight: 800;
  font-size: 16px;
}

.cardSubtitle {
  margin-top: 6px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.6;
}

.formGrid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 12px;
}

.field {
  display: grid;
  gap: 6px;
  min-width: 0;
}

.fieldWide {
  grid-column: 1 / -1;
}

.label {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
}

.control {
  width: 100%;
  min-width: 0;
  padding: 12px 14px;
  border: 1px solid rgba(15, 23, 42, 0.14);
  border-radius: 14px;
  background: #fff;
  outline: none;
  font: inherit;
}

.control:focus {
  border-color: rgba(37, 99, 235, 0.56);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.message {
  grid-column: 1 / -1;
  border-radius: 14px;
  padding: 10px 12px;
  font-size: 13px;
}

.message.error {
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.18);
}

.message.ok {
  color: #166534;
  background: rgba(34, 197, 94, 0.10);
  border: 1px solid rgba(34, 197, 94, 0.22);
}

.actions {
  grid-column: 1 / -1;
}

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

@media (max-width: 920px) {
  .topbar {
    align-items: flex-start;
    flex-direction: column;
  }

  .formGrid {
    grid-template-columns: 1fr;
  }
}
</style>
