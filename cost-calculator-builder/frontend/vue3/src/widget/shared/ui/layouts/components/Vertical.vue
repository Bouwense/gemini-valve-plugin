<template>
  <div class="ccb-vertical">
    <slot />
  </div>
</template>

<script setup lang="ts"></script>

<style lang="scss">
.ccb-vertical {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 30px;
  max-width: var(--ccb-vertical-max-width);
  margin: auto;
  padding: 10px;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}
</style>
