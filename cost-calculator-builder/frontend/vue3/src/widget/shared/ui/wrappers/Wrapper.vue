<template>
  <component :is="currentComponents">
    <template v-for="(_, name) in $slots" #[name]="scope">
      <slot :name="name" v-bind="scope"></slot>
    </template>
  </component>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, h } from "vue";

type Props = {
  wrapper: "subtotal" | "fields";
};

const props = defineProps<Props>();

const currentComponents = computed(() => {
  if (props.wrapper === "subtotal") {
    return defineAsyncComponent(
      () => import("./components/SubTotalWrapper.vue"),
    );
  } else if (props.wrapper === "fields") {
    return defineAsyncComponent(() => import("./components/FieldsWrapper.vue"));
  }

  return h("div");
});
</script>
