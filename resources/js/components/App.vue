<template>
  <div style="padding: 16px;">
    <nav style="display:flex; gap:12px; margin-bottom: 12px;">
      <router-link to="/">Home</router-link>
      <router-link to="/orders">Orders</router-link>
    </nav>

    <router-view />
  </div>
</template>
