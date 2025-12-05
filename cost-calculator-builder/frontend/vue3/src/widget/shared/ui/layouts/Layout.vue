<template>
  <transition name="layout-fade" mode="out-in">
    <component
      :is="currentComponents"
      :key="appearanceStore.getAppearanceBoxStyle"
    >
      <slot />
    </component>
  </transition>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from "vue";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";

const appearanceStore = useAppearanceStore();

const currentComponents = computed(() => {
  if (appearanceStore.getAppearanceBoxStyle === "horizontal") {
    return defineAsyncComponent(() => import("./components/Horizontal.vue"));
  } else if (appearanceStore.getAppearanceBoxStyle === "vertical") {
    return defineAsyncComponent(() => import("./components/Vertical.vue"));
  } else {
    return defineAsyncComponent(() => import("./components/TwoColumns.vue"));
  }
});
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
