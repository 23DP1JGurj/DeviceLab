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

        <label class="floatingField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.password?.length || fieldErrors.email?.length }"
            v-model="form.password"
            type="password"
            autocomplete="current-password"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Parole</span>
          <span v-if="fieldErrors.password?.[0]" class="authFieldError">{{ fieldErrors.password[0] }}</span>
        </label>
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
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { login, resolveRedirectPath, sanitizeRedirectPath } from '../auth'
import AuthLayout from './AuthLayout.vue'

const route = useRoute()
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
