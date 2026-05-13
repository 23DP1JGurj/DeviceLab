<template>
  <AuthLayout
    eyebrow="AUTORIZĀCIJA"
    title="Ielogoties kontā"
    subtitle="Pieslēdzies, lai pārvaldītu pieteikumus un profilu."
  >
    <form class="authForm" @submit.prevent="submit" novalidate>
      <div v-if="serverError" class="authAlert">{{ serverError }}</div>

      <div class="authStack">
        <label class="floatingField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.email?.length }"
            v-model.trim="form.email"
            type="email"
            autocomplete="email"
            placeholder=" "
            required
          />
          <span class="floatingLabel">E-pasts</span>
          <span v-if="fieldErrors.email?.[0]" class="authFieldError">{{ fieldErrors.email[0] }}</span>
        </label>

        <div class="floatingField passwordField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.password?.length || fieldErrors.email?.length }"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Parole</span>
          <button
            class="passwordToggle"
            type="button"
            :aria-label="showPassword ? 'Paslēpt paroli' : 'Rādīt paroli'"
            @click="showPassword = !showPassword"
          >
            <svg v-if="!showPassword" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg v-else viewBox="0 0 24 24" aria-hidden="true">
              <path d="M3 3l18 18"/>
              <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a17.8 17.8 0 0 1-3.1 4.1"/>
              <path d="M6.4 6.8C3.9 8.5 2.5 12 2.5 12s3.5 7 9.5 7c1.6 0 3-.4 4.2-1"/>
              <path d="M9.9 9.9A3 3 0 0 0 14.1 14"/>
            </svg>
          </button>
          <span v-if="fieldErrors.password?.[0]" class="authFieldError">{{ fieldErrors.password[0] }}</span>
        </div>
      </div>

      <button class="authSubmit" type="submit" :disabled="loading">
        <span v-if="loading" class="authLoader" aria-hidden="true"></span>
        <span>{{ loading ? 'Ielogojas...' : 'Ielogoties' }}</span>
      </button>
    </form>

    <div class="authSwitch">
      Nav konta?
      <RouterLink to="/register">Reģistrēties</RouterLink>
    </div>

    <div v-if="isDev" class="devAccounts">
      <button class="devToggle" type="button" @click="showTestAccounts = !showTestAccounts">
        Testa konti
      </button>
      <div v-if="showTestAccounts" class="devAccountBox">
        <div><b>admin</b> admin@devicelab.local / password</div>
        <div><b>staff</b> staff@devicelab.local / password</div>
        <div><b>client</b> client@devicelab.local / password</div>
      </div>
    </div>
  </AuthLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { defaultRouteForUser, login } from '../auth'
import AuthLayout from './AuthLayout.vue'

const router = useRouter()
const isDev = import.meta.env.DEV

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const showTestAccounts = ref(false)
const serverError = ref('')
const fieldErrors = ref({})
const showPassword = ref(false)

function resetErrors() {
  serverError.value = ''
  fieldErrors.value = {}
}

async function submit() {
  loading.value = true
  resetErrors()

  try {
    const user = await login(form.email, form.password)
    await router.push(defaultRouteForUser(user))
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
