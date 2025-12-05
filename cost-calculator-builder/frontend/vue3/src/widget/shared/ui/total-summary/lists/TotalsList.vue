<template>
  <div class="ccb-totals-list">
    <slot></slot>
  </div>
</template>

<script setup lang="ts"></script>

<style lang="scss">
.ccb-totals-list {
  display: flex;
  flex-direction: column;
  gap: var(--ccb-grand-total-spacing);
  border-bottom: 1px solid var(--ccb-container-border-color);
  padding-bottom: 10px;
}
</style>
