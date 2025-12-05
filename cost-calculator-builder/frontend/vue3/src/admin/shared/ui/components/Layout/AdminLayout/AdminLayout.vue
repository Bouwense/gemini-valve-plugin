<template>
  <div class="ccb-admin-layout">
    <div class="ccb-admin-layout__header">
      <slot name="header"></slot>
    </div>
    <div class="ccb-admin-layout__content">
      <slot name="content"></slot>
    </div>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.ccb-admin-layout {
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%;
  border-radius: 8px;

  border: none;
  box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.15);
  // overflow: hidden;

  &__header {
    min-height: 60px;
    border-radius: 8px 8px 0px 0px;
    border-bottom: 1px solid #ddd;
    background: #fff;
    display: flex;
    align-items: center;
  }

  &__content {
    flex: 1;
    height: 100%;
    background: #eef1f7;
    border-radius: 0px 0px 8px 8px;
  }
}
</style>
