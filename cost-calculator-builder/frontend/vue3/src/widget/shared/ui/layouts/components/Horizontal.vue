<template>
  <div class="ccb-horizontal">
    <slot />
  </div>
</template>

<script setup lang="ts"></script>

<style lang="scss">
.ccb-horizontal {
  position: relative;
  display: grid;
  gap: 30px;
  max-width: var(--ccb-horizontal-max-width);
  margin: auto;
  padding: 10px;

  .ccb-fields-list {
    @media (max-width: 768px) {
      grid-template-columns: 1fr !important;
    }
  }
}
</style>
