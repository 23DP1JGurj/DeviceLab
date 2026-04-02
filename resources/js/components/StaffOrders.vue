<template>
  <div class="page">
    <div class="topbar">
      <div class="titleBlock">
        <h1 class="h1">Darbinieka panelis</h1>
        <div class="subtitle">Izvēlies darba sadaļu un turpini apstrādāt pasūtījumus.</div>
      </div>

      <div class="topActions">
        <RouterLink class="btn btnGhost" to="/">← Sākums</RouterLink>
        <AccountMenu />
      </div>
    </div>

    <div class="dashboardGrid">
      <RouterLink class="dashCard" to="/staff/orders/new">
        <div class="dashKicker">Pieņemšana</div>
        <div class="dashTitle">Jaunie pasūtījumi</div>
        <div class="dashText">Apskati brīvos pasūtījumus un pieņem tos darbā.</div>
      </RouterLink>

      <RouterLink class="dashCard" to="/staff/orders/my">
        <div class="dashKicker">Darbs</div>
        <div class="dashTitle">Mani pasūtījumi</div>
        <div class="dashText">Maini statusu, pievieno diagnozi un darba piezīmes.</div>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import AccountMenu from './AccountMenu.vue'
</script>

<style scoped>
:global(*), :global(*::before), :global(*::after) { box-sizing: border-box; }
:global(body) {
  margin: 0;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Helvetica Neue";
  background: radial-gradient(1200px 600px at 20% 0%, #eef6ff 0%, #f6f8fb 45%, #f7f7f7 100%);
  color: #0f172a;
}

.page {
  max-width: 980px;
  margin: 0 auto;
  padding: 22px 18px 34px;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 18px;
}

.h1 {
  margin: 0;
  font-size: 34px;
  letter-spacing: 0;
}

.subtitle {
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
}

.topActions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.dashboardGrid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.dashCard {
  display: grid;
  gap: 10px;
  min-height: 190px;
  padding: 22px;
  color: #0f172a;
  text-decoration: none;
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 20px;
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
  transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
}

.dashCard:hover {
  transform: translateY(-2px);
  border-color: rgba(37, 99, 235, 0.24);
  box-shadow: 0 22px 44px rgba(15, 23, 42, 0.12);
}

.dashKicker {
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
}

.dashTitle {
  font-size: 24px;
  font-weight: 900;
}

.dashText {
  color: #64748b;
  line-height: 1.55;
}

.btn {
  border: 1px solid rgba(15, 23, 42, 0.14);
  background: #fff;
  color: #0f172a;
  padding: 10px 14px;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 800;
  text-decoration: none;
}

.btnGhost {
  background: transparent;
}

@media (max-width: 720px) {
  .topbar {
    align-items: flex-start;
    flex-direction: column;
  }

  .dashboardGrid {
    grid-template-columns: 1fr;
  }
}
</style>
