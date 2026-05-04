<template>
  <AuthLayout
    eyebrow="REĢISTRĀCIJA"
    title="Izveidot klienta kontu"
    subtitle="Pēc reģistrācijas varēsi pievienot ierīces un noformēt pieteikumu."
    variant="register"
  >
    <form class="authForm" @submit.prevent="submit" novalidate>
      <div v-if="serverError" class="authAlert">{{ serverError }}</div>

      <div class="authGrid">
        <label class="authField">
          <span class="authLabel">Vārds</span>
          <input
            class="authControl"
            :class="{ hasError: fieldErrors.first_name?.length || fieldErrors.name?.length }"
            v-model.trim="form.first_name"
            type="text"
            autocomplete="given-name"
            required
          />
          <span v-if="fieldErrors.first_name?.[0] || fieldErrors.name?.[0]" class="authFieldError">
            {{ fieldErrors.first_name?.[0] || fieldErrors.name?.[0] }}
          </span>
        </label>

        <label class="authField">
          <span class="authLabel">Uzvārds</span>
          <input
            class="authControl"
            :class="{ hasError: fieldErrors.last_name?.length }"
            v-model.trim="form.last_name"
            type="text"
            autocomplete="family-name"
            required
          />
          <span v-if="fieldErrors.last_name?.[0]" class="authFieldError">{{ fieldErrors.last_name[0] }}</span>
        </label>

        <label class="authField">
          <span class="authLabel">E-pasts</span>
          <input
            class="authControl"
            :class="{ hasError: fieldErrors.email?.length }"
            v-model.trim="form.email"
            type="email"
            autocomplete="email"
            required
          />
          <span v-if="fieldErrors.email?.[0]" class="authFieldError">{{ fieldErrors.email[0] }}</span>
        </label>

        <label class="authField">
          <span class="authLabel">Tālrunis</span>
          <input
            class="authControl"
            :class="{ hasError: fieldErrors.phone?.length }"
            v-model.trim="form.phone"
            type="text"
            autocomplete="tel"
            required
          />
          <span v-if="fieldErrors.phone?.[0]" class="authFieldError">{{ fieldErrors.phone[0] }}</span>
        </label>

        <label class="authField">
          <span class="authLabel">Parole</span>
          <input
            class="authControl"
            :class="{ hasError: fieldErrors.password?.length }"
            v-model="form.password"
            type="password"
            autocomplete="new-password"
            required
          />
          <span v-if="fieldErrors.password?.[0]" class="authFieldError">{{ fieldErrors.password[0] }}</span>
        </label>

        <label class="authField">
          <span class="authLabel">Atkārto paroli</span>
          <input
            class="authControl"
            :class="{ hasError: fieldErrors.password_confirmation?.length }"
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
            required
          />
          <span v-if="fieldErrors.password_confirmation?.[0]" class="authFieldError">
            {{ fieldErrors.password_confirmation[0] }}
          </span>
        </label>
      </div>

      <button class="authSubmit" type="submit" :disabled="loading">
        <span v-if="loading" class="authLoader" aria-hidden="true"></span>
        <span>{{ loading ? 'Reģistrē...' : 'Reģistrēties' }}</span>
      </button>
    </form>

    <div class="authSwitch">
      Jau ir konts?
      <RouterLink to="/login">Ielogoties</RouterLink>
    </div>
  </AuthLayout>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { register, resolveRedirectPath, sanitizeRedirectPath } from '../auth'
import AuthLayout from './AuthLayout.vue'

const route = useRoute()
const router = useRouter()

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const fullName = computed(() => [form.first_name, form.last_name].filter(Boolean).join(' ').trim())
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
    const user = await register({
      name: fullName.value,
      email: form.email,
      phone: form.phone,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    await router.push(resolveRedirectPath(user, sanitizeRedirectPath(route.query.redirect)))
  } catch (err) {
    fieldErrors.value = err?.fieldErrors || {}
    serverError.value = err?.status && err.status !== 422
      ? (err?.message || 'Neizdevās reģistrēties.')
      : ''
  } finally {
    loading.value = false
  }
}
</script>
