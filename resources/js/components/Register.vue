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
        <label class="floatingField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.first_name?.length || fieldErrors.name?.length }"
            v-model.trim="form.first_name"
            type="text"
            autocomplete="given-name"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Vārds</span>
          <span class="authFieldError">{{ fieldError('first_name') || fieldError('name') }}</span>
        </label>

        <label class="floatingField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.last_name?.length }"
            v-model.trim="form.last_name"
            type="text"
            autocomplete="family-name"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Uzvārds</span>
          <span class="authFieldError">{{ fieldError('last_name') }}</span>
        </label>

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
          <span class="authFieldError">{{ fieldError('email') }}</span>
        </label>

        <label class="floatingField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.phone?.length }"
            v-model.trim="form.phone"
            type="text"
            autocomplete="tel"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Tālrunis</span>
          <span class="authFieldError">{{ fieldError('phone') }}</span>
        </label>

        <label class="floatingField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.password?.length }"
            v-model="form.password"
            type="password"
            autocomplete="new-password"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Parole</span>
          <span class="authFieldError">{{ passwordFieldError }}</span>
        </label>

        <label class="floatingField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.password_confirmation?.length }"
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Atkārto paroli</span>
          <span class="authFieldError">{{ fieldError('password_confirmation') }}</span>
        </label>
      </div>

      <div v-if="passwordErrorSummary" class="authAlert compactAuthAlert">
        {{ passwordErrorSummary }}
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
import { RouterLink, useRouter } from 'vue-router'
import { defaultRouteForUser, register } from '../auth'
import AuthLayout from './AuthLayout.vue'

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
const passwordFieldError = computed(() => fieldErrors.value.password?.length ? 'Parole neatbilst prasībām.' : '')
const passwordErrorSummary = computed(() => {
  if (!fieldErrors.value.password?.length) return ''
  return 'Parole neatbilst prasībām. Tai jābūt vismaz 8 simbolus garai, ar lielo burtu, ciparu un speciālo simbolu.'
})

function resetErrors() {
  serverError.value = ''
  fieldErrors.value = {}
}

function fieldError(field) {
  return fieldErrors.value[field]?.[0] || ''
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

    await router.push(defaultRouteForUser(user))
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
