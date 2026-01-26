<template>
  <div class="page">
    <div class="card">
      <h1 class="h1">Home</h1>
      <p class="muted">DeviceLab — demo.</p>
      <RouterLink class="btn btnPrimary" to="/orders">Go to Orders →</RouterLink>
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
  background: radial-gradient(1200px 600px at 20% 0%, #eef6ff 0%, #f6f8fb 45%, #f7f7f7 100%);
  color: #0f172a;
}

.page { max-width: 980px; margin: 0 auto; padding: 22px 18px; }
.card {
  background: rgba(255,255,255,0.92);
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 16px;
  box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
  padding: 18px;
}

.h1 { margin: 0 0 8px; font-size: 34px; letter-spacing: -0.02em; }
.muted { color: #64748b; margin: 0 0 14px; }

.btn {
  display: inline-block;
  text-decoration: none;
  border: 1px solid rgba(15, 23, 42, 0.14);
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 800;
}
.btnPrimary {
  background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
  color: #fff;
  border-color: rgba(29, 78, 216, 0.60);
  box-shadow: 0 10px 18px rgba(37, 99, 235, 0.22);
}
</style>
