<template>
  <div ref="wrap" :class="['accWrap', `accWrap--${variant}`, { 'is-open': isOpen }]">
    <button
      type="button"
      class="accTrigger"
      aria-haspopup="menu"
      :aria-expanded="menuVisible ? 'true' : 'false'"
      @click="handleTriggerClick"
    >
      <span class="accIcon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.9" />
          <path
            d="M18 20a6 6 0 0 0-12 0"
            stroke="currentColor"
            stroke-width="1.9"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </span>

      <span class="accText">
        <span class="accGreeting">Sveiki</span>
        <span class="accPrimary">{{ primaryLabel }}</span>
      </span>
    </button>

    <div class="accDropdown" role="menu" :aria-hidden="menuVisible ? 'false' : 'true'">
      <div class="accList">
        <template v-if="isGuest">
          <RouterLink class="accItem" to="/login" @click="closeMenu">Ielogoties</RouterLink>
          <RouterLink class="accItem" to="/register" @click="closeMenu">Reģistrēties</RouterLink>
        </template>

        <template v-else-if="isAdmin">
          <RouterLink class="accItem" to="/admin" @click="closeMenu">Admin panelis</RouterLink>
          <RouterLink class="accItem" to="/admin/orders" @click="closeMenu">Visi pasūtījumi</RouterLink>
          <RouterLink class="accItem" to="/admin/clients" @click="closeMenu">Klienti</RouterLink>
          <RouterLink class="accItem" to="/admin/staff" @click="closeMenu">Darbinieki</RouterLink>
          <RouterLink class="accItem" to="/admin/reviews" @click="closeMenu">Atsauksmes</RouterLink>
          <RouterLink class="accItem" to="/profile" @click="closeMenu">Profils</RouterLink>
        </template>

        <template v-else>
          <RouterLink class="accItem" to="/profile" @click="closeMenu">Profils</RouterLink>
          <RouterLink v-if="isClient" class="accItem" to="/devices" @click="closeMenu">
            Manas ierīces
          </RouterLink>
          <RouterLink v-if="isClient" class="accItem" to="/orders/new" @click="closeMenu">
            Jauns pieteikums
          </RouterLink>
          <RouterLink v-if="isClient" class="accItem" to="/orders/history" @click="closeMenu">
            Pasūtījumu vēsture
          </RouterLink>
          <RouterLink v-if="isStaff" class="accItem" to="/staff/orders/new" @click="closeMenu">
            Jaunie pasūtījumi
          </RouterLink>
          <RouterLink v-if="isStaff" class="accItem" to="/staff/orders/my" @click="closeMenu">
            Pieņemtie pasūtījumi
          </RouterLink>
          <RouterLink v-if="isAdmin" class="accItem" to="/staff/orders/all" @click="closeMenu">
            Visi pasūtījumi
          </RouterLink>
          <RouterLink v-if="isStaff" class="accItem" to="/staff/orders/history" @click="closeMenu">
            Pasūtījumu vēsture
          </RouterLink>
          <RouterLink v-if="isStaff" class="accItem" to="/staff/reviews" @click="closeMenu">
            Atsauksmes
          </RouterLink>
        </template>

        <RouterLink v-if="!isGuest" class="accItem accItem--notify" to="/notifications" @click="closeMenu">
          <span>Paziņojumi</span>
          <span v-if="unreadCount > 0" class="accBadge">{{ unreadCount }}</span>
        </RouterLink>
      </div>

      <div v-if="!isGuest" class="accFooter">
        <button class="accAction" type="button" @click="handleLogout">Iziet</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { authFetch, currentUser, hasAnyRole, logout } from '../auth'

defineProps({
  variant: {
    type: String,
    default: 'light',
  },
})

const router = useRouter()
const wrap = ref(null)
const isOpen = ref(false)
const supportsHover = ref(false)
const unreadCount = ref(0)

const isGuest = computed(() => !currentUser.value)
const isStaff = computed(() => hasAnyRole(currentUser.value, ['staff', 'admin']))
const isAdmin = computed(() => currentUser.value?.role === 'admin')
const isClient = computed(() => currentUser.value?.role === 'client')
const primaryLabel = computed(() => currentUser.value?.name?.trim() || 'Ielogoties')
const menuVisible = computed(() => isOpen.value)

let mediaQuery = null
let mediaQueryHandler = null
let onDocumentPointerDown = null
let onWindowKeydown = null
let onNotificationsUpdated = null

function syncHoverCapability() {
  supportsHover.value = Boolean(mediaQuery?.matches)
  if (supportsHover.value) {
    isOpen.value = false
  }
}

function closeMenu() {
  isOpen.value = false
}

async function loadUnreadCount() {
  if (!currentUser.value) {
    unreadCount.value = 0
    return
  }

  try {
    const response = await authFetch('/api/notifications/unread-count')

    if (!response.ok) {
      unreadCount.value = 0
      return
    }

    const json = await response.json()
    unreadCount.value = Number(json.unread_count || 0)
  } catch {
    unreadCount.value = 0
  }
}

function handleTriggerClick() {
  if (supportsHover.value) return
  isOpen.value = !isOpen.value
}

async function handleLogout() {
  await logout()
  unreadCount.value = 0
  closeMenu()
  await router.push('/')
}

onMounted(() => {
  mediaQuery = window.matchMedia('(hover: hover) and (pointer: fine)')
  syncHoverCapability()

  mediaQueryHandler = () => syncHoverCapability()
  if (typeof mediaQuery.addEventListener === 'function') {
    mediaQuery.addEventListener('change', mediaQueryHandler)
  } else if (typeof mediaQuery.addListener === 'function') {
    mediaQuery.addListener(mediaQueryHandler)
  }

  onDocumentPointerDown = (event) => {
    if (!wrap.value?.contains(event.target)) {
      closeMenu()
    }
  }

  onWindowKeydown = (event) => {
    if (event.key === 'Escape') {
      closeMenu()
    }
  }

  onNotificationsUpdated = () => loadUnreadCount()

  document.addEventListener('pointerdown', onDocumentPointerDown)
  window.addEventListener('keydown', onWindowKeydown)
  window.addEventListener('devicelab:notifications-updated', onNotificationsUpdated)
  loadUnreadCount()
})

watch(currentUser, () => {
  loadUnreadCount()
})

onBeforeUnmount(() => {
  if (mediaQuery && mediaQueryHandler) {
    if (typeof mediaQuery.removeEventListener === 'function') {
      mediaQuery.removeEventListener('change', mediaQueryHandler)
    } else if (typeof mediaQuery.removeListener === 'function') {
      mediaQuery.removeListener(mediaQueryHandler)
    }
  }

  if (onDocumentPointerDown) {
    document.removeEventListener('pointerdown', onDocumentPointerDown)
  }

  if (onWindowKeydown) {
    window.removeEventListener('keydown', onWindowKeydown)
  }

  if (onNotificationsUpdated) {
    window.removeEventListener('devicelab:notifications-updated', onNotificationsUpdated)
  }
})
</script>

<style>
.accWrap {
  position: relative;
  display: inline-flex;
  align-items: flex-start;
  justify-content: flex-end;
  flex: 0 0 auto;
  color: #0f172a;
  z-index: 90;
}

.accWrap--dark {
  color: rgba(255, 255, 255, 0.94);
}

.accTrigger {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  min-width: 180px;
  max-width: 220px;
  min-height: 56px;
  padding: 10px 14px;
  border-radius: 18px;
  border: 1px solid transparent;
  background: rgba(255, 255, 255, 0.96);
  color: inherit;
  cursor: pointer;
  text-align: left;
  font: inherit;
  appearance: none;
  transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.10);
}

.accWrap--dark .accTrigger {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.18);
  box-shadow: none;
  backdrop-filter: blur(10px);
}

.accWrap:hover .accTrigger,
.accWrap:focus-within .accTrigger,
.accWrap.is-open .accTrigger {
  transform: translateY(-1px);
}

.accWrap--light:hover .accTrigger,
.accWrap--light:focus-within .accTrigger,
.accWrap--light.is-open .accTrigger {
  border-color: rgba(37, 99, 235, 0.18);
  box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
}

.accIcon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  flex: 0 0 auto;
}

.accIcon svg {
  width: 24px;
  height: 24px;
}

.accText {
  display: grid;
  gap: 2px;
  min-width: 0;
}

.accGreeting {
  font-size: 12px;
  line-height: 1.1;
  opacity: 0.76;
}

.accPrimary {
  font-size: 15px;
  font-weight: 800;
  line-height: 1.15;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.accDropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: min(320px, calc(100vw - 24px));
  padding: 12px;
  border-radius: 18px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 24px 54px rgba(15, 23, 42, 0.16);
  backdrop-filter: blur(12px);
  opacity: 0;
  transform: translateY(-8px);
  pointer-events: none;
  visibility: hidden;
  transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
}

.accDropdown::before {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  top: -12px;
  height: 12px;
}

.accList {
  display: grid;
  gap: 8px;
}

.accItem,
.accAction {
  display: inline-flex;
  align-items: center;
  justify-content: flex-start;
  width: 100%;
  min-height: 46px;
  padding: 0 14px;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.16);
  background: rgba(148, 163, 184, 0.10);
  color: #0f172a;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease;
}

.accItem:hover,
.accAction:hover {
  transform: translateY(-1px);
  background: rgba(37, 99, 235, 0.10);
  border-color: rgba(37, 99, 235, 0.18);
}

.accItem--notify {
  justify-content: space-between;
  gap: 12px;
}

.accBadge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  padding: 0 7px;
  border-radius: 999px;
  color: #fff;
  background: #2563eb;
  font-size: 12px;
  font-weight: 900;
}

.accFooter {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(148, 163, 184, 0.16);
}

.accAction {
  justify-content: center;
  cursor: pointer;
  font: inherit;
  appearance: none;
}

@media (hover: hover) and (pointer: fine) {
  .accWrap:hover .accDropdown,
  .accWrap:focus-within .accDropdown {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
    visibility: visible;
  }
}

.accWrap.is-open .accDropdown {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
  visibility: visible;
}

@media (max-width: 720px) {
  .accTrigger {
    min-width: 170px;
    max-width: 190px;
    min-height: 52px;
    padding: 10px 12px;
  }

  .accDropdown {
    width: min(300px, calc(100vw - 20px));
  }
}
</style>
