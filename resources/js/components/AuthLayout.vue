<template>
  <div class="authPage">
    <div class="authShell">
      <RouterLink class="authBackLink" to="/">← DeviceLab</RouterLink>

      <section class="authCard">
        <aside class="authBrandPanel">
          <div class="brandMark">
            <span></span>
            <strong>DeviceLab</strong>
          </div>
          <div>
            <p class="brandKicker">Servisa panelis</p>
            <h2>Ērta piekļuve pieteikumiem, ierīcēm un paziņojumiem.</h2>
          </div>
          <div class="brandChips">
            <span>Pieteikumi</span>
            <span>Ierīces</span>
            <span>Paziņojumi</span>
          </div>
        </aside>

        <main class="authFormPanel">
          <div class="authEyebrow">{{ eyebrow }}</div>
          <h1 class="authTitle">{{ title }}</h1>
          <p class="authSubtitle">{{ subtitle }}</p>
          <slot />
        </main>
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
})
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue";
  background:
    radial-gradient(900px 460px at 10% 0%, rgba(47, 124, 255, 0.14), transparent 60%),
    radial-gradient(700px 420px at 100% 20%, rgba(10, 102, 255, 0.10), transparent 55%),
    linear-gradient(180deg, #f4f8ff 0%, #eef4fb 48%, #f7f7f8 100%);
  color: #071833;
}

.authPage {
  min-height: 100vh;
  display: grid;
  align-items: center;
  padding: 34px 18px;
}

.authShell {
  width: min(100%, 1040px);
  margin: 0 auto;
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
  display: grid;
  grid-template-columns: minmax(280px, 0.82fr) minmax(0, 1.18fr);
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 28px 80px rgba(15, 23, 42, 0.11);
}

.authBrandPanel {
  display: grid;
  align-content: space-between;
  min-height: 560px;
  padding: 34px;
  color: #fff;
  background:
    radial-gradient(520px 360px at 0% 0%, rgba(147, 197, 253, 0.45), transparent 58%),
    linear-gradient(145deg, #0a66ff 0%, #1d4ed8 52%, #0f2f7a 100%);
}

.brandMark {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 20px;
  font-weight: 950;
}

.brandMark span {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  background:
    radial-gradient(circle at 50% 18%, #fff 0 13%, transparent 14%),
    radial-gradient(circle at 50% 100%, #fff 0 31%, transparent 32%);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45);
}

.brandKicker {
  margin: 0 0 12px;
  color: rgba(255, 255, 255, 0.72);
  font-size: 13px;
  font-weight: 900;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.authBrandPanel h2 {
  max-width: 360px;
  margin: 0;
  font-size: clamp(30px, 4vw, 44px);
  line-height: 1.04;
  letter-spacing: -0.04em;
}

.brandChips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.brandChips span {
  padding: 9px 12px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.88);
  font-size: 13px;
  font-weight: 850;
}

.authFormPanel {
  padding: 46px;
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
  font-size: clamp(36px, 5vw, 52px);
  line-height: 1.04;
  letter-spacing: -0.045em;
}

.authSubtitle {
  max-width: 500px;
  margin: 0 0 26px;
  color: #64748b;
  font-size: 16px;
  line-height: 1.65;
}

:global(.authForm) {
  display: grid;
  gap: 18px;
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

@media (max-width: 820px) {
  .authPage {
    align-items: start;
    padding: 22px 14px;
  }

  .authCard {
    grid-template-columns: 1fr;
    border-radius: 24px;
  }

  .authBrandPanel {
    min-height: auto;
    gap: 28px;
    padding: 26px;
  }

  .authBrandPanel h2 {
    font-size: 30px;
  }

  .authFormPanel {
    padding: 28px 20px;
  }

  :global(.authGrid) {
    grid-template-columns: 1fr;
  }

  :global(.authField.wide) {
    grid-column: auto;
  }
}
</style>
