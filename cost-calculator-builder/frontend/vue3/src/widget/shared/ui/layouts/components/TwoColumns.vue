<template>
  <div class="ccb-two-column">
    <slot />
  </div>
</template>

<script setup lang="ts"></script>

<style lang="scss">
.ccb-two-column {
  position: relative;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
  max-width: var(--ccb-two-columns-max-width);
  margin: auto;
  padding: 10px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}
</style>
