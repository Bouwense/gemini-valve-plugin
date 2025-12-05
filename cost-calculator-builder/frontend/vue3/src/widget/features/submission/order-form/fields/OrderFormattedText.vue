<template>
  <div
    class="ccb-order-field ccb-order-formatted-text"
    :class="{ [`ccb-col-${field.fieldWidth}`]: true }"
  >
    <div class="ccb-order-field__title">{{ field.label }}</div>
    <div class="ccb-field__input-wrapper">
      <div class="ccb-formatted-text" v-html="field.value"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IFormField } from "@/widget/shared/types/fields";
import { toRefs } from "vue";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);
</script>
