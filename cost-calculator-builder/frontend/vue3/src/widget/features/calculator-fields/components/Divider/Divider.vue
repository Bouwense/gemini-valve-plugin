<template>
  <div class="ccb-field ccb-divider" :class="field.additionalStyles">
    <div class="ccb-divider__item" :style="{ ...dividerCss }"></div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, defineProps } from "vue";
import { IDividerField } from "@/widget/shared/types/fields";

type Props = {
  field: IDividerField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const dividerLength = computed(() => {
  return field.value.len || "100%";
});

const dividerHeight = computed(() => {
  return field.value.size || "1px";
});

const dividerStyle = computed(() => {
  return field.value.style || "solid";
});

const dividerCss = computed(() => {
  return {
    width: dividerLength.value,
    borderBottom:
      dividerHeight.value +
      " " +
      dividerStyle.value +
      " " +
      "var(--ccb-fields-border-color)",
  };
});
</script>

<style lang="scss">
.ccb-divider {
  display: flex;
  justify-content: center;

  &__item {
    width: 100%;
  }
}

.ccb-horizontal {
  .line {
    display: flex;
    align-items: center;
  }
}
</style>
