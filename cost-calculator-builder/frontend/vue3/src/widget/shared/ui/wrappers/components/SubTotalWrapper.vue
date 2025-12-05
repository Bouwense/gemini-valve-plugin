<template>
  <div class="ccb-block">
    <div class="ccb-subtotal-wrapper">
      <slot />
    </div>
    <slot name="notifications" />
  </div>
</template>

<style scoped lang="scss">
@use "@/styles/widget/_mixins.scss" as mixins;

.ccb-subtotal-wrapper {
  @include mixins.container;
}

.ccb-subtotal-wrapper {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
</style>
<script setup lang="ts"></script>
