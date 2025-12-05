<template>
  <div class="ccb-fields-list">
    <slot />
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.ccb-fields-list {
  display: grid;
  gap: var(--ccb-field-spacing);
  @media only screen and (max-width: 480px) {
    gap: var(--ccb-mobile-field-spacing);
  }
}

.ccb-horizontal,
.ccb-two-column {
  .ccb-fields-list {
    grid-template-columns: repeat(2, 1fr);

    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  }
}
</style>
