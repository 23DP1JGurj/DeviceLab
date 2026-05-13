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
            @input="form.phone = sanitizePhoneInput(form.phone)"
            required
          />
          <span class="floatingLabel">Tālrunis</span>
          <span class="authFieldError">{{ fieldError('phone') }}</span>
        </label>

        <div class="floatingField passwordField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.password?.length }"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="new-password"
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
          <span class="authFieldError">{{ passwordFieldError }}</span>
        </div>

        <div class="floatingField passwordField">
          <input
            class="floatingInput"
            :class="{ hasError: fieldErrors.password_confirmation?.length }"
            v-model="form.password_confirmation"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            autocomplete="new-password"
            placeholder=" "
            required
          />
          <span class="floatingLabel">Atkārto paroli</span>
          <button
            class="passwordToggle"
            type="button"
            :aria-label="showPasswordConfirmation ? 'Paslēpt paroli' : 'Rādīt paroli'"
            @click="showPasswordConfirmation = !showPasswordConfirmation"
          >
            <svg v-if="!showPasswordConfirmation" viewBox="0 0 24 24" aria-hidden="true">
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
          <span class="authFieldError">{{ fieldError('password_confirmation') }}</span>
        </div>
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
import { isValidPhoneInput, sanitizePhoneInput } from '../phoneInput'

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
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const passwordFieldError = computed(() => fieldErrors.value.password?.[0] || '')
const passwordErrorSummary = computed(() => {
  if (!fieldErrors.value.password?.length) return ''
  if (fieldErrors.value.password[0] !== 'Parole neatbilst prasībām.') return ''
  return 'Parole neatbilst prasībām. Tai jābūt vismaz 8 simbolus garai, ar lielo burtu, ciparu un speciālo simbolu.'
})

function resetErrors() {
  serverError.value = ''
  fieldErrors.value = {}
}

function fieldError(field) {
  return fieldErrors.value[field]?.[0] || ''
}

function validateForm() {
  const errors = {}
  const phone = sanitizePhoneInput(form.phone)

  if (!form.first_name.trim()) {
    errors.first_name = ['Vārds ir obligāts.']
  }

  if (!form.last_name.trim()) {
    errors.last_name = ['Uzvārds ir obligāts.']
  }

  if (!form.email.trim()) {
    errors.email = ['E-pasts ir obligāts.']
  } else if (!/^\S+@\S+\.\S+$/.test(form.email.trim())) {
    errors.email = ['Ievadi derīgu e-pasta adresi.']
  }

  if (!phone) {
    errors.phone = ['Tālrunis ir obligāts.']
  } else if (!isValidPhoneInput(phone)) {
    errors.phone = ['Tālruņa numurā drīkst būt tikai cipari un sākumā simbols +.']
  } else if (phone.length < 6) {
    errors.phone = ['Tālrunim jābūt vismaz 6 ciparus garam.']
  }

  if (!form.password) {
    errors.password = ['Parole ir obligāta.']
  } else if (
    form.password.length < 8
    || !/[A-Z]/.test(form.password)
    || !/[0-9]/.test(form.password)
    || !/[!@#$%^&*_\-+=?]/.test(form.password)
  ) {
    errors.password = ['Parole neatbilst prasībām.']
  }

  if (!form.password_confirmation) {
    errors.password_confirmation = ['Atkārtotā parole ir obligāta.']
  } else if (!errors.password && form.password !== form.password_confirmation) {
    errors.password_confirmation = ['Paroles nesakrīt.']
  }

  return errors
}

async function submit() {
  loading.value = true
  resetErrors()

  const validationErrors = validateForm()

  if (Object.keys(validationErrors).length > 0) {
    fieldErrors.value = validationErrors
    loading.value = false
    return
  }

  try {
    const user = await register({
      name: fullName.value,
      last_name: form.last_name,
      email: form.email,
      phone: sanitizePhoneInput(form.phone),
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
