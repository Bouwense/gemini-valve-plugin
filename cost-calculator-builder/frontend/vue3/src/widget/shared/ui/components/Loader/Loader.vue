<template>
  <div class="ccb-loader">
    <component :is="currentComponent" />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, defineProps } from "vue";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";

type Props = {
  type?: string;
};

const { type } = defineProps<Props>();

const appearanceStore = useAppearanceStore();

const currentComponent = computed(() => {
  if (Number(appearanceStore.getAppearanceLoaderType) === 0) {
    return defineAsyncComponent(() => import("./styles/default.vue"));
  } else if (Number(appearanceStore.getAppearanceLoaderType) === 1) {
    return defineAsyncComponent(() => import("./styles/dots.vue"));
  } else if (Number(appearanceStore.getAppearanceLoaderType) === 2) {
    return defineAsyncComponent(() => import("./styles/lazyDots.vue"));
  } else if (Number(appearanceStore.getAppearanceLoaderType) === 3) {
    return defineAsyncComponent(() => import("./styles/segment.vue"));
  } else if (Number(appearanceStore.getAppearanceLoaderType) === 4) {
    return defineAsyncComponent(() => import("./styles/spinner.vue"));
  } else if (Number(appearanceStore.getAppearanceLoaderType) === 5) {
    if (type === "submit") {
      return defineAsyncComponent(() => import("./styles/submitSkeleton.vue"));
    } else {
      return defineAsyncComponent(
        () => import("./styles/skeletonCalculator.vue"),
      );
    }
  }
});
</script>

<style lang="scss">
.ccb-loader {
  widows: 100%;
  display: flex;
  justify-content: center;
  padding: 20px 0;
}
</style>
