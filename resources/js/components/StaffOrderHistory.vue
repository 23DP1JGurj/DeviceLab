<template>
  <StaffReadonlyOrders
    endpoint="/api/staff/orders/history"
    title="Pasūtījumu vēsture"
    subtitle="Pabeigtie un atceltie pasūtījumi."
    section-title="Vēsture"
    empty-text="Vēsturē vēl nav pasūtījumu."
  />
</template>

<script setup>
import StaffReadonlyOrders from './StaffReadonlyOrders.vue'
</script>
