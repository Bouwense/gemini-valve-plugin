<template>
  <component :is="currentComponents" :summary="props.summary">
    <slot></slot>
  </component>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from "vue";
import { Field } from "@/widget/shared/types/fields";

type Props = {
  itemType: "total" | "summary" | "repeater" | "group";
  summary: Field;
};

const props = defineProps<Props>();

const currentComponents = computed(() => {
  if (props.itemType === "total") {
    return defineAsyncComponent(() => import("./items/TotalItem.vue"));
  } else if (props.itemType === "summary") {
    return defineAsyncComponent(() => import("./items/SummaryItem.vue"));
  } else if (props.itemType === "repeater") {
    return defineAsyncComponent(() => import("./items/RepeaterItem.vue"));
  } else if (props.itemType === "group") {
    return defineAsyncComponent(() => import("./items/GroupItem.vue"));
  }

  return "";
});
</script>

<style lang="scss">
.ccb-summary-item {
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: center;
  color: var(--ccb-text-color);
  font-size: var(--ccb-summary-text-size);
  font-weight: var(--ccb-summary-text-weight);
  text-transform: var(--ccb-summary-text-transform);
  border-bottom: 1px dashed #ddd;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-summary-text-size);
    font-weight: var(--ccb-mobile-summary-text-weight);
  }

  &__title {
    text-align: left;
    .description {
      font-size: 0.9em;
      padding-left: 10px;
      color: var(--ccb-summary-description-color);
    }
  }
}
</style>
