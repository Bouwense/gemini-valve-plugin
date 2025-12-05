<template>
  <div class="ccb-fields-card">
    <slot />
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.ccb-fields-card {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.field {
  width: 100%;
  height: 30px;
  background: #ddd;
  border-radius: 4px;
}
</style>
