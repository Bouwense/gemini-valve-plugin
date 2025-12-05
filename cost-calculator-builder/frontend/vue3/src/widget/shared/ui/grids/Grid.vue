<template>
  <component :is="currentComponents">
    <slot />
  </component>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, h } from "vue";

type Props = {
  grid: "list" | "card";
};

const props = defineProps<Props>();

const currentComponents = computed(() => {
  if (props.grid === "list") {
    return defineAsyncComponent(() => import("./components/List.vue"));
  } else if (props.grid === "card") {
    return defineAsyncComponent(() => import("./components/Card.vue"));
  }

  return h("div");
});
</script>
