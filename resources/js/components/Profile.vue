<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Profils</h1>
        <div class="subtitle">Pārvaldi savus konta un kontaktinformācijas datus.</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <section class="profileSummary">
      <div class="identityBlock">
        <div class="avatar" aria-hidden="true">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8" />
            <path d="M4.75 20a7.25 7.25 0 0 1 14.5 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
          </svg>
        </div>

        <div class="identityText">
          <div class="profileName">{{ user.name || 'Klients' }}</div>
          <div class="profileMeta">Aktīvs DeviceLab konts</div>
        </div>
      </div>

      <div class="summaryGrid">
        <div class="summaryItem">
          <span>Statuss</span>
          <strong>Aktīvs</strong>
        </div>
        <div class="summaryItem">
          <span>Kontakts</span>
          <strong>{{ user.phone || 'Nav norādīts' }}</strong>
        </div>
        <div class="summaryItem">
          <span>E-pasts</span>
          <strong>{{ user.email || 'Nav norādīts' }}</strong>
        </div>
      </div>
    </section>

    <section class="editCard">
      <div class="cardHead">
        <div class="smallAvatar" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
          <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8" />
          <path d="M4.75 20a7.25 7.25 0 0 1 14.5 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
        </svg>
        </div>
        <div>
          <div class="cardTitle">Rediģēt profilu</div>
          <div class="cardSubtitle">Izmaiņas tiks saglabātas tavā kontā.</div>
        </div>
      </div>

      <form class="profileForm" @submit.prevent="submit">
        <div class="formSection">
          <div class="sectionTitle">Pamatinformācija</div>

          <div class="twoCols">
            <label class="field">
              <span class="label">Vārds</span>
              <input class="control" v-model.trim="form.first_name" type="text" autocomplete="given-name" />
            </label>

            <label class="field">
              <span class="label">Uzvārds</span>
              <input class="control" v-model.trim="form.last_name" type="text" autocomplete="family-name" />
            </label>
          </div>
        </div>

        <div class="formSection">
          <div class="sectionTitle">Kontakti</div>

          <label class="field">
            <span class="label">E-pasts</span>
            <input class="control" v-model.trim="form.email" type="email" autocomplete="email" />
          </label>

          <label class="field">
            <span class="label">Tālrunis</span>
            <input class="control" v-model.trim="form.phone" type="text" autocomplete="tel" />
          </label>
        </div>

        <div v-if="error" class="message error">{{ error }}</div>
        <div v-else-if="success" class="message ok">{{ success }}</div>

        <div class="actions">
          <button class="btn btnPrimary" type="submit" :disabled="saving">
            {{ saving ? 'Saglabā...' : 'Saglabāt' }}
          </button>
          <button class="btn btnSoft" type="button" @click="openPasswordModal">
            Mainīt paroli
          </button>
          <button class="btn btnSoft" type="button" @click="signOut">
            Izrakstīties
          </button>
        </div>
      </form>
    </section>

    <div v-if="passwordModalOpen" class="modalOverlay" @click.self="closePasswordModal">
      <section class="modalCard" role="dialog" aria-modal="true" aria-labelledby="passwordTitle">
        <div class="modalHead">
          <div>
            <div id="passwordTitle" class="modalTitle">Mainīt paroli</div>
            <div class="modalSubtitle">Ievadi pašreizējo paroli un jauno paroli divreiz.</div>
          </div>
          <button class="iconButton" type="button" aria-label="Aizvērt" @click="closePasswordModal">×</button>
        </div>

        <form class="passwordForm" @submit.prevent="submitPassword">
          <label class="field">
            <span class="label">Pašreizējā parole</span>
            <input class="control" v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
          </label>

          <label class="field">
            <span class="label">Jaunā parole</span>
            <input class="control" v-model="passwordForm.new_password" type="password" autocomplete="new-password" />
          </label>

          <label class="field">
            <span class="label">Atkārtot jauno paroli</span>
            <input class="control" v-model="passwordForm.new_password_confirmation" type="password" autocomplete="new-password" />
          </label>

          <div v-if="passwordError" class="message error">{{ passwordError }}</div>
          <div v-else-if="passwordSuccess" class="message ok">{{ passwordSuccess }}</div>

          <div class="modalActions">
            <button class="btn btnSoft" type="button" @click="closePasswordModal">Atcelt</button>
            <button class="btn btnPrimary" type="submit" :disabled="passwordSaving">
              {{ passwordSaving ? 'Saglabā...' : 'Saglabāt' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
import { currentUser, initAuth, logout, updatePassword, updateProfile } from '../auth'

const router = useRouter()

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
})

const saving = ref(false)
const error = ref('')
const success = ref('')
const passwordModalOpen = ref(false)
const passwordSaving = ref(false)
const passwordError = ref('')
const passwordSuccess = ref('')

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const user = computed(() => currentUser.value ?? {
  name: '',
  email: '',
  phone: '',
})

function splitName(name) {
  const parts = String(name || '').trim().split(/\s+/).filter(Boolean)

  if (parts.length === 0) {
    return { first: '', last: '' }
  }

  return {
    first: parts[0],
    last: parts.slice(1).join(' '),
  }
}

function fullName() {
  return [form.first_name, form.last_name].map(part => part.trim()).filter(Boolean).join(' ')
}

function syncFormFromUser(value) {
  const name = splitName(value?.name)
  form.first_name = name.first
  form.last_name = name.last
  form.email = value?.email ?? ''
  form.phone = value?.phone ?? ''
}

function validateForm() {
  if (form.first_name.trim().length < 2) {
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
      name: fullName(),
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

async function signOut() {
  await logout()
  await router.push('/')
}

function resetPasswordForm() {
  passwordForm.current_password = ''
  passwordForm.new_password = ''
  passwordForm.new_password_confirmation = ''
  passwordError.value = ''
  passwordSuccess.value = ''
}

function openPasswordModal() {
  resetPasswordForm()
  passwordModalOpen.value = true
}

function closePasswordModal() {
  if (passwordSaving.value) return
  passwordModalOpen.value = false
  resetPasswordForm()
}

async function submitPassword() {
  passwordError.value = ''
  passwordSuccess.value = ''

  if (!passwordForm.current_password) {
    passwordError.value = 'Ievadi pašreizējo paroli.'
    return
  }

  if (passwordForm.new_password.length < 8) {
    passwordError.value = 'Jaunajai parolei jābūt vismaz 8 rakstzīmes garai.'
    return
  }

  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    passwordError.value = 'Jaunās paroles nesakrīt.'
    return
  }

  passwordSaving.value = true

  try {
    await updatePassword({
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation,
    })

    passwordSuccess.value = 'Parole veiksmīgi nomainīta.'
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.new_password_confirmation = ''
  } catch (err) {
    passwordError.value = err?.message || 'Neizdevās nomainīt paroli.'
  } finally {
    passwordSaving.value = false
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
  max-width: 1040px;
  margin: 0 auto;
  padding: 22px 18px 38px;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 18px;
}

.titleBlock { min-width: 0; }

.h1 {
  margin: 0;
  color: #071833;
  font-size: 40px;
  line-height: 1;
  letter-spacing: -0.035em;
}

.subtitle {
  margin-top: 8px;
  color: #64748b;
  font-size: 15px;
}

.topActions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.profileSummary,
.editCard {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 22px;
  box-shadow: 0 18px 48px rgba(15, 23, 42, 0.08);
}

.profileSummary {
  display: grid;
  gap: 16px;
  padding: 20px;
}

.identityBlock {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.identityText {
  min-width: 0;
}

.profileName {
  overflow: hidden;
  color: #071833;
  font-size: 22px;
  font-weight: 900;
  letter-spacing: -0.02em;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.profileMeta {
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
}

.summaryGrid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.summaryItem {
  min-width: 0;
  padding: 16px;
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 18px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.summaryItem span {
  display: block;
  color: #64748b;
  font-size: 12px;
  font-weight: 800;
}

.summaryItem strong {
  display: block;
  overflow: hidden;
  margin-top: 7px;
  color: #071833;
  font-size: 17px;
  font-weight: 900;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.avatar {
  flex: 0 0 auto;
  display: grid;
  place-items: center;
  width: 54px;
  height: 54px;
  border-radius: 999px;
  color: #fff;
  background: linear-gradient(180deg, #2f7cff 0%, #1d4ed8 100%);
  box-shadow: 0 14px 24px rgba(37, 99, 235, 0.24);
}

.avatar svg {
  display: block;
}

.editCard {
  margin-top: 18px;
  padding: 24px;
}

.cardHead {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 22px;
}

.smallAvatar {
  display: grid;
  place-items: center;
  flex: 0 0 auto;
  width: 42px;
  height: 42px;
  border-radius: 14px;
  color: #2563eb;
  background: rgba(37, 99, 235, 0.10);
  border: 1px solid rgba(37, 99, 235, 0.16);
}

.smallAvatar svg {
  display: block;
}

.cardTitle {
  color: #071833;
  font-size: 24px;
  font-weight: 900;
  letter-spacing: -0.02em;
}

.cardSubtitle {
  margin-top: 7px;
  color: #64748b;
  font-size: 14px;
}

.profileForm {
  display: grid;
  gap: 22px;
}

.formSection {
  display: grid;
  gap: 14px;
  padding-top: 20px;
  border-top: 1px solid rgba(148, 163, 184, 0.22);
}

.formSection:first-child {
  padding-top: 0;
  border-top: 0;
}

.sectionTitle {
  color: #071833;
  font-size: 16px;
  font-weight: 900;
}

.twoCols {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.field {
  display: grid;
  gap: 7px;
  min-width: 0;
}

.label {
  color: #475569;
  font-size: 12px;
  font-weight: 800;
}

.control {
  width: 100%;
  min-width: 0;
  padding: 13px 15px;
  border: 1px solid rgba(15, 23, 42, 0.14);
  border-radius: 15px;
  background: #fff;
  color: #071833;
  outline: none;
  font: inherit;
}

.control:focus {
  border-color: rgba(37, 99, 235, 0.56);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.message {
  border-radius: 15px;
  padding: 11px 13px;
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
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.btn {
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  color: #0f172a;
  padding: 11px 16px;
  border-radius: 13px;
  cursor: pointer;
  font-weight: 800;
  text-decoration: none;
  transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
}

.btn:hover {
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

.btnPrimary {
  background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
  color: #fff;
  border-color: rgba(29, 78, 216, 0.60);
  box-shadow: 0 12px 22px rgba(37, 99, 235, 0.22);
}

.btnSoft {
  background: #f8fafc;
}

.btnGhost {
  background: transparent;
}

.modalOverlay {
  position: fixed;
  inset: 0;
  z-index: 60;
  display: grid;
  place-items: center;
  padding: 18px;
  background: rgba(15, 23, 42, 0.42);
  backdrop-filter: blur(8px);
}

.modalCard {
  width: min(100%, 480px);
  border: 1px solid rgba(15, 23, 42, 0.10);
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 28px 70px rgba(15, 23, 42, 0.22);
  padding: 22px;
}

.modalHead {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
}

.modalTitle {
  color: #071833;
  font-size: 22px;
  font-weight: 900;
  letter-spacing: -0.02em;
}

.modalSubtitle {
  margin-top: 6px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.iconButton {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 12px;
  background: #fff;
  color: #0f172a;
  cursor: pointer;
  font-size: 22px;
  line-height: 1;
}

.passwordForm {
  display: grid;
  gap: 14px;
}

.modalActions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@media (max-width: 780px) {
  .topbar {
    align-items: flex-start;
    flex-direction: column;
  }

  .summaryGrid,
  .twoCols {
    grid-template-columns: 1fr;
  }

  .avatar {
    width: 50px;
    height: 50px;
  }

  .modalActions {
    justify-content: stretch;
  }

  .modalActions .btn {
    flex: 1;
  }
}
</style>
