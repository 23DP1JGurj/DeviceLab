<template>
  <div class="authPage">
    <div class="authCard">
      <RouterLink class="backLink" to="/">← DeviceLab</RouterLink>

      <div class="eyebrow">Reģistrācija</div>
      <h1 class="title">Izveidot klienta kontu</h1>
      <p class="subtitle">Pēc reģistrācijas uzreiz varēsi pievienot ierīces un noformēt pieteikumu.</p>

      <form class="form" @submit.prevent="submit">
        <label class="field">
          <span class="label">Vārds</span>
          <input class="control" v-model.trim="form.name" type="text" autocomplete="name" required />
        </label>

        <label class="field">
          <span class="label">E-pasts</span>
          <input class="control" v-model.trim="form.email" type="email" autocomplete="email" required />
        </label>

        <label class="field">
          <span class="label">Tālrunis</span>
          <input class="control" v-model.trim="form.phone" type="text" autocomplete="tel" />
        </label>

        <label class="field">
          <span class="label">Parole</span>
          <input class="control" v-model="form.password" type="password" autocomplete="new-password" required />
        </label>

        <label class="field">
          <span class="label">Atkārto paroli</span>
          <input class="control" v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
        </label>

        <div v-if="error" class="message error">{{ error }}</div>

        <button class="submitBtn" type="submit" :disabled="loading">
          {{ loading ? 'Reģistrē...' : 'Reģistrēties' }}
        </button>
      </form>

      <div class="authSwitch">
        Jau ir konts?
        <RouterLink to="/login">Ielogoties</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { register, resolveRedirectPath, sanitizeRedirectPath } from '../auth'

const route = useRoute()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const error = ref('')

async function submit() {
  loading.value = true
  error.value = ''

  try {
    const user = await register({
      name: form.name,
      email: form.email,
      phone: form.phone || null,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    await router.push(resolveRedirectPath(user, sanitizeRedirectPath(route.query.redirect)))
  } catch (err) {
    error.value = err?.message || 'Neizdevās reģistrēties.'
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
    radial-gradient(700px 420px at 100% 20%, rgba(10, 102, 255, 0.12), transparent 55%),
    linear-gradient(180deg, #f4f8ff 0%, #eef4fb 48%, #f7f7f8 100%);
  color: #0f172a;
}

.authPage {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 24px 16px;
}

.authCard {
  width: min(100%, 480px);
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 24px;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
  padding: 28px;
}

.backLink {
  display: inline-flex;
  align-items: center;
  margin-bottom: 18px;
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.eyebrow {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #2563eb;
}

.title {
  margin: 12px 0 10px;
  font-size: 34px;
  line-height: 1.05;
  letter-spacing: -0.03em;
}

.subtitle {
  margin: 0 0 20px;
  color: #64748b;
  line-height: 1.65;
}

.form {
  display: grid;
  gap: 14px;
}

.field {
  display: grid;
  gap: 6px;
}

.label {
  font-size: 12px;
  font-weight: 700;
  color: #475569;
}

.control {
  width: 100%;
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

.submitBtn {
  margin-top: 4px;
  border: 0;
  border-radius: 14px;
  padding: 13px 18px;
  background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
  color: #fff;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 18px 34px rgba(37, 99, 235, 0.22);
}

.submitBtn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.message {
  border-radius: 14px;
  padding: 10px 12px;
  font-size: 13px;
}

.message.error {
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.18);
}

.authSwitch {
  margin-top: 18px;
  color: #64748b;
  font-size: 14px;
}

.authSwitch a {
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
  margin-left: 6px;
}
</style>
