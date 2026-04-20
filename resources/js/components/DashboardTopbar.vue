<template>
  <header class="dashTopbar">
    <div class="leftGroup">
      <RouterLink class="backButton" :to="backTo">← {{ backLabel }}</RouterLink>
      <div class="titleBlock">
        <h1 class="title">{{ title }}</h1>
        <div v-if="subtitle" class="subtitle">{{ subtitle }}</div>
        <div v-else-if="reserveSubtitle" class="subtitle subtitleReserve" aria-hidden="true">.</div>
      </div>
    </div>

    <div class="rightGroup">
      <AccountMenu />
    </div>
  </header>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import AccountMenu from './AccountMenu.vue'

defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  backTo: {
    type: [String, Object],
    default: '/',
  },
  backLabel: {
    type: String,
    default: 'Sākums',
  },
  reserveSubtitle: {
    type: Boolean,
    default: false,
  },
})
</script>

<style scoped>
.dashTopbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.leftGroup {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.rightGroup {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  flex: 0 0 auto;
}

.backButton {
  flex: 0 0 auto;
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: rgba(255, 255, 255, 0.76);
  color: #0f172a;
  padding: 10px 14px;
  border-radius: 13px;
  cursor: pointer;
  font-weight: 850;
  text-decoration: none;
  transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
}

.backButton:hover {
  transform: translateY(-1px);
  border-color: rgba(37, 99, 235, 0.24);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.07);
}

.titleBlock {
  min-width: 0;
}

.title {
  margin: 0;
  overflow: hidden;
  color: #071833;
  font-size: 34px;
  line-height: 1.05;
  font-weight: 950;
  letter-spacing: -0.03em;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.subtitle {
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
  line-height: 1.45;
}

.subtitleReserve {
  visibility: hidden;
}

@media (max-width: 760px) {
  .dashTopbar {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: start;
  }

  .leftGroup {
    display: grid;
    gap: 10px;
  }

  .title {
    white-space: normal;
  }
}
</style>
