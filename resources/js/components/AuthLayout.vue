<template>
  <div class="authPage">
    <div class="authShell" :class="{ registerShell: variant === 'register' }">
      <RouterLink class="authBackLink" to="/">← DeviceLab</RouterLink>

      <section class="authCard">
        <div class="authBrand">
          <strong>DeviceLab</strong>
        </div>

        <div class="authEyebrow">{{ eyebrow }}</div>
        <h1 class="authTitle">{{ title }}</h1>
        <p class="authSubtitle">{{ subtitle }}</p>

        <slot />
      </section>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  eyebrow: { type: String, required: true },
  title: { type: String, required: true },
  subtitle: { type: String, required: true },
  variant: { type: String, default: 'login' },
})
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue";
  background:
    radial-gradient(900px 460px at 10% 0%, rgba(47, 124, 255, 0.12), transparent 60%),
    radial-gradient(700px 420px at 100% 20%, rgba(10, 102, 255, 0.08), transparent 55%),
    linear-gradient(180deg, #f4f8ff 0%, #eef4fb 48%, #f7f7f8 100%);
  color: #071833;
}

.authPage {
  min-height: 100vh;
  padding: 34px 18px;
}

.authShell {
  width: calc(100% - 32px);
  max-width: 580px;
  margin: 44px auto 64px;
}

.authShell.registerShell {
  max-width: 840px;
}

.authBackLink {
  display: inline-flex;
  align-items: center;
  margin-bottom: 16px;
  color: #1d4ed8;
  font-weight: 900;
  text-decoration: none;
}

.authCard {
  width: 100%;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
  padding: 44px;
}

.authBrand {
  display: block;
  margin-bottom: 24px;
  color: #071833;
  font-size: 18px;
  font-weight: 950;
}

.authEyebrow {
  color: #2563eb;
  font-size: 12px;
  font-weight: 950;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.authTitle {
  margin: 12px 0 10px;
  color: #071833;
  font-size: clamp(34px, 5vw, 48px);
  line-height: 1.04;
  letter-spacing: -0.045em;
}

.authSubtitle {
  max-width: 560px;
  margin: 0 0 28px;
  color: #64748b;
  font-size: 16px;
  line-height: 1.6;
}

:global(.authForm) {
  display: grid;
  gap: 18px;
  width: 100%;
}

:global(.authAlert) {
  border-radius: 16px;
  padding: 12px 14px;
  color: #b91c1c;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.18);
  font-size: 14px;
  line-height: 1.5;
}

:global(.authGrid) {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  width: 100%;
}

:global(.authStack) {
  display: grid;
  gap: 16px;
  width: 100%;
}

:global(.authField) {
  display: grid;
  gap: 8px;
  min-width: 0;
}

:global(.authField.wide) {
  grid-column: 1 / -1;
}

:global(.authLabel) {
  color: #334155;
  font-size: 13px;
  font-weight: 900;
}

:global(.authControl) {
  width: 100%;
  min-width: 0;
  min-height: 56px;
  padding: 14px 16px;
  border: 1px solid #d8e0ee;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.96);
  outline: none;
  color: #071833;
  font: inherit;
  font-size: 16px;
  transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}

:global(.authControl::placeholder) {
  color: #94a3b8;
}

:global(.authControl:focus) {
  border-color: rgba(37, 99, 235, 0.54);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

:global(.authControl.hasError) {
  border-color: rgba(239, 68, 68, 0.42);
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
}

:global(.authFieldError) {
  color: #b91c1c;
  font-size: 13px;
  line-height: 1.45;
}

:global(.authSubmit) {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  width: 100%;
  min-height: 56px;
  padding: 14px 22px;
  border: 0;
  border-radius: 16px;
  background: linear-gradient(135deg, #2f7cff, #1d4ed8);
  color: #fff;
  cursor: pointer;
  font-size: 17px;
  font-weight: 950;
  box-shadow: 0 18px 34px rgba(37, 99, 235, 0.24);
}

:global(.authSubmit:disabled) {
  opacity: 0.72;
  cursor: not-allowed;
}

:global(.authLoader) {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.34);
  border-top-color: #fff;
  border-radius: 999px;
  animation: authSpin 0.8s linear infinite;
}

:global(.authSwitch) {
  margin-top: 20px;
  color: #64748b;
  font-size: 15px;
}

:global(.authSwitch a) {
  margin-left: 6px;
  color: #1d4ed8;
  font-weight: 900;
  text-decoration: none;
}

:global(.devAccounts) {
  margin-top: 16px;
}

:global(.devToggle) {
  padding: 8px 11px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 12px;
  background: #f8fafc;
  color: #475569;
  cursor: pointer;
  font: inherit;
  font-size: 13px;
  font-weight: 850;
}

:global(.devAccountBox) {
  margin-top: 10px;
  padding: 12px 14px;
  border: 1px solid rgba(37, 99, 235, 0.12);
  border-radius: 16px;
  background: rgba(37, 99, 235, 0.05);
  color: #475569;
  font-size: 13px;
  line-height: 1.65;
}

@keyframes authSpin {
  to { transform: rotate(360deg); }
}

@media (max-width: 720px) {
  .authPage {
    padding: 22px 14px;
  }

  .authShell {
    width: 100%;
    margin: 0 auto 36px;
  }

  .authCard {
    padding: 28px 20px;
  }

  .authTitle {
    font-size: 38px;
  }

  :global(.authGrid) {
    grid-template-columns: 1fr;
  }

  :global(.authField.wide) {
    grid-column: auto;
  }
}
</style>
