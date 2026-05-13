<template>
  <div class="authPage">
    <div class="authShell" :class="{ registerShell: variant === 'register' }">
      <RouterLink class="authBackLink" to="/">← DeviceLab</RouterLink>

      <section class="authCard">
        <h1 class="authTitle">{{ title }}</h1>

        <slot />
      </section>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  eyebrow: { type: String, default: '' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
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

.authTitle {
  margin: 0 0 28px;
  color: #071833;
  font-size: clamp(34px, 5vw, 48px);
  line-height: 1.04;
  letter-spacing: -0.045em;
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
  min-height: 19px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

:global(.compactAuthAlert) {
  padding: 10px 12px;
  font-size: 13px;
}

:global(.floatingField) {
  position: relative;
  display: grid;
  min-width: 0;
}

:global(.floatingInput) {
  width: 100%;
  min-width: 0;
  min-height: 60px;
  padding: 19px 16px 12px;
  border: 1px solid #d8e0ee;
  border-radius: 16px;
  background: #fff;
  outline: none;
  color: #071833;
  font: inherit;
  font-size: 16px;
  line-height: 1.25;
  transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}

:global(.floatingInput:focus) {
  border-color: rgba(37, 99, 235, 0.58);
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

:global(.floatingInput.hasError) {
  border-color: rgba(239, 68, 68, 0.42);
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
}

:global(.floatingLabel) {
  position: absolute;
  left: 14px;
  top: 20px;
  max-width: calc(100% - 28px);
  padding: 0 5px;
  border-radius: 999px;
  background: #fff;
  color: #64748b;
  font-size: 15px;
  font-weight: 850;
  line-height: 1.15;
  pointer-events: none;
  transform-origin: left top;
  transition:
    top 0.16s ease,
    color 0.16s ease,
    font-size 0.16s ease,
    transform 0.16s ease;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

:global(.floatingInput:focus + .floatingLabel),
:global(.floatingInput:not(:placeholder-shown) + .floatingLabel) {
  top: -7px;
  color: #1d4ed8;
  font-size: 12px;
  transform: translateY(0);
}

:global(.floatingInput.hasError + .floatingLabel) {
  color: #b91c1c;
}

:global(.floatingField .authFieldError) {
  margin-top: 7px;
}

:global(.floatingField .authFieldError span) {
  display: block;
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
