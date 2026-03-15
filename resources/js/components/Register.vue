<template>
  <div class="authPage">
    <div class="authCard">
      <RouterLink class="backLink" to="/">← DeviceLab</RouterLink>

      <div class="eyebrow">Reģistrācija</div>
      <h1 class="title">Konta izveide drīzumā</h1>
      <p class="subtitle">
        Demo projektā reģistrācija vēl nav ieslēgta. Pagaidām izmanto kādu no testa kontiem pieslēgšanās lapā.
      </p>

      <div class="actions">
        <RouterLink class="submitBtn" to="/login">Iet uz ielogošanos</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue";
  background:
    radial-gradient(900px 460px at 10% 0%, rgba(47, 124, 255, 0.14), transparent 60%),
    radial-gradient(700px 420px at 100% 20%, rgba(10, 102, 255, 0.12), transparent 55%),
    linear-gradient(180deg, #f4f8ff 0%, #eef4fb 48%, #f7f7f8 100%);
  color: #0f172a;
}

.authPage {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 24px 16px;
}

.authCard {
  width: min(100%, 460px);
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 24px;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
  padding: 28px;
}

.backLink {
  display: inline-flex;
  align-items: center;
  margin-bottom: 18px;
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.eyebrow {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #2563eb;
}

.title {
  margin: 12px 0 10px;
  font-size: 34px;
  line-height: 1.05;
  letter-spacing: -0.03em;
}

.subtitle {
  margin: 0;
  color: #64748b;
  line-height: 1.7;
}

.actions {
  margin-top: 22px;
}

.submitBtn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 14px;
  padding: 13px 18px;
  background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
  color: #fff;
  font-weight: 800;
  text-decoration: none;
  box-shadow: 0 18px 34px rgba(37, 99, 235, 0.22);
}
</style>
