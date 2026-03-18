<template>
  <div class="authPage">
    <div class="authShell">
      <RouterLink class="backLink" to="/">← DeviceLab</RouterLink>

      <div class="authCard">
        <div class="eyebrow">Autorizācija</div>
        <h1 class="title">Ielogoties kontā</h1>
        <p class="subtitle">Pieslēdzies, lai pārvaldītu savus pieteikumus vai atvērtu darbinieka paneli.</p>

        <form class="form" @submit.prevent="submit" novalidate>
          <div v-if="serverError" class="alert alertError">{{ serverError }}</div>

          <div class="formGrid">
            <label class="field">
              <span class="label">E-pasts</span>
              <input
                class="control"
                :class="{ hasError: fieldErrors.email?.length }"
                v-model.trim="form.email"
                type="email"
                autocomplete="email"
                required
              />
              <span v-if="fieldErrors.email?.[0]" class="fieldError">{{ fieldErrors.email[0] }}</span>
            </label>

            <label class="field">
              <span class="label">Parole</span>
              <input
                class="control"
                :class="{ hasError: fieldErrors.password?.length || fieldErrors.email?.length }"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                required
              />
              <span v-if="fieldErrors.password?.[0]" class="fieldError">{{ fieldErrors.password[0] }}</span>
            </label>
          </div>

          <button class="submitBtn" type="submit" :disabled="loading">
            <span v-if="loading" class="loader" aria-hidden="true"></span>
            <span>{{ loading ? 'Ielogojas...' : 'Ielogoties' }}</span>
          </button>
        </form>

        <div class="authSwitch">
          Nav konta?
          <RouterLink to="/register">Reģistrēties</RouterLink>
        </div>

        <div class="accounts">
          <div class="accountsTitle">Testa konti</div>
          <div class="accountLine"><b>admin</b> admin@devicelab.local / password</div>
          <div class="accountLine"><b>staff</b> staff@devicelab.local / password</div>
          <div class="accountLine"><b>client</b> client@devicelab.local / password</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { login, resolveRedirectPath, sanitizeRedirectPath } from '../auth'

const route = useRoute()
const router = useRouter()

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const serverError = ref('')
const fieldErrors = ref({})

function resetErrors() {
  serverError.value = ''
  fieldErrors.value = {}
}

async function submit() {
  loading.value = true
  resetErrors()

  try {
    const user = await login(form.email, form.password)
    await router.push(resolveRedirectPath(user, sanitizeRedirectPath(route.query.redirect)))
  } catch (err) {
    fieldErrors.value = err?.fieldErrors || {}
    serverError.value = err?.status && err.status !== 422
      ? (err?.message || 'Neizdevās ielogoties.')
      : ''
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue";
  background:
    radial-gradient(900px 460px at 10% 0%, rgba(47, 124, 255, 0.14), transparent 60%),
    radial-gradient(700px 420px at 100% 20%, rgba(10, 102, 255, 0.10), transparent 55%),
    linear-gradient(180deg, #f4f8ff 0%, #eef4fb 48%, #f7f7f8 100%);
  color: #0f172a;
}

.authPage {
  min-height: 100vh;
  padding: 32px 16px;
}

.authShell {
  width: min(100%, 760px);
  margin: 0 auto;
}

.backLink {
  display: inline-flex;
  align-items: center;
  margin-bottom: 18px;
  color: #1d4ed8;
  font-weight: 800;
  text-decoration: none;
}

.authCard {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 28px;
  box-shadow: 0 28px 80px rgba(15, 23, 42, 0.10);
  padding: 32px;
}

.eyebrow {
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #2563eb;
}

.title {
  margin: 14px 0 12px;
  font-size: clamp(38px, 5vw, 56px);
  line-height: 1.04;
  letter-spacing: -0.04em;
}

.subtitle {
  margin: 0 0 24px;
  max-width: 560px;
  color: #64748b;
  line-height: 1.7;
  font-size: 18px;
}

.form {
  display: grid;
  gap: 18px;
}

.alert {
  border-radius: 16px;
  padding: 12px 14px;
  font-size: 14px;
  line-height: 1.5;
}

.alertError {
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.18);
}

.formGrid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 18px 20px;
}

.field {
  display: grid;
  gap: 8px;
  min-width: 0;
}

.label {
  font-size: 13px;
  font-weight: 800;
  color: #334155;
}

.control {
  width: 100%;
  min-width: 0;
  min-height: 56px;
  padding: 14px 16px;
  border: 1px solid rgba(148, 163, 184, 0.34);
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.96);
  outline: none;
  font: inherit;
  font-size: 16px;
  transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}

.control:focus {
  border-color: rgba(37, 99, 235, 0.54);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.control.hasError {
  border-color: rgba(239, 68, 68, 0.38);
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
}

.fieldError {
  font-size: 13px;
  color: #b91c1c;
  line-height: 1.45;
}

.submitBtn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 56px;
  padding: 14px 22px;
  border: 0;
  border-radius: 16px;
  background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
  color: #fff;
  font-size: 17px;
  font-weight: 900;
  cursor: pointer;
  box-shadow: 0 18px 34px rgba(37, 99, 235, 0.22);
}

.submitBtn:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.loader {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.34);
  border-top-color: #fff;
  border-radius: 999px;
  animation: spin 0.8s linear infinite;
}

.authSwitch {
  margin-top: 22px;
  color: #64748b;
  font-size: 15px;
}

.authSwitch a {
  margin-left: 6px;
  color: #1d4ed8;
  font-weight: 800;
  text-decoration: none;
}

.accounts {
  margin-top: 22px;
  padding: 16px 18px;
  border-radius: 18px;
  background: rgba(37, 99, 235, 0.06);
  border: 1px solid rgba(37, 99, 235, 0.12);
}

.accountsTitle {
  margin-bottom: 8px;
  color: #1e3a8a;
  font-size: 13px;
  font-weight: 900;
}

.accountLine {
  color: #475569;
  font-size: 14px;
  line-height: 1.7;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 720px) {
  .authCard {
    padding: 24px 18px;
    border-radius: 24px;
  }

  .title {
    font-size: 42px;
  }

  .subtitle {
    font-size: 16px;
  }

  .formGrid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}
</style>
