<template>
  <StaffReadonlyOrders
    endpoint="/api/staff/orders"
    title="Visi pasūtījumi"
    subtitle="Administratora skats uz visiem pasūtījumiem."
    section-title="Visi pasūtījumi"
    empty-text="Pasūtījumu vēl nav."
  />
</template>

<script setup>
import StaffReadonlyOrders from './StaffReadonlyOrders.vue'
</script>
