<template>
  <div class="ccb-block">
    <div class="ccb-fields-wrapper">
      <slot />
    </div>
  </div>
</template>

<style scoped lang="scss">
@use "@/styles/widget/_mixins.scss" as mixins;

.ccb-fields-wrapper {
  @include mixins.container;
}
</style>
