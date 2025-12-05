<template>
  <div class="layout-wrapper">
    <Horizontal v-show="appearanceStore.getAppearanceBoxStyle === 'horizontal'"
      ><slot
    /></Horizontal>
    <Vertical v-show="appearanceStore.getAppearanceBoxStyle === 'vertical'"
      ><slot
    /></Vertical>
    <TwoColumns
      v-show="
        !['horizontal', 'vertical'].includes(
          appearanceStore.getAppearanceBoxStyle,
        )
      "
      ><slot
    /></TwoColumns>
  </div>
</template>

<script setup lang="ts">
import { defineAsyncComponent } from "vue";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";

const appearanceStore = useAppearanceStore();

const Horizontal = defineAsyncComponent(
  () => import("./components/Horizontal.vue"),
);
const Vertical = defineAsyncComponent(
  () => import("./components/Vertical.vue"),
);
const TwoColumns = defineAsyncComponent(
  () => import("./components/TwoColumns.vue"),
);
</script>

<style>
.layout-fade-enter-active,
.layout-fade-leave-active {
  transition:
    opacity 0.4s ease,
    transform 0.4s ease;
}

.layout-fade-enter-from,
.layout-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
  &.ccb-layout-container {
    min-height: fit-content;
  }
  .ccb-invoice {
    .layout-container {
      display: none !important;
    }
  }
}

.layout-fade-enter-to,
.layout-fade-leave-from {
  opacity: 1;
  transform: translateY(0);
  &.ccb-layout-container {
    min-height: fit-content;
  }
  .ccb-invoice {
    .layout-container {
      display: none !important;
    }
  }
}
</style>
