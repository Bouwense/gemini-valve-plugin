<template>
  <div
    class="ccb-field"
    :class="'ccb_field_with_' + field.fieldName"
    :data-id="field.alias"
    :data-repeater="field.repeaterIdx"
  >
    <component v-if="FieldComponent" :is="FieldComponent" :field="field" />
  </div>
</template>

<script lang="ts" setup>
import { computed, toRefs } from "vue";
import {
  fieldRegistry,
  IFields,
} from "@/widget/actions/fields/fieldRegistry.ts";
import { Field } from "@/widget/shared/types/fields";

type Props = {
  name: string;
  field: Field;
};

const props = defineProps<Props>();
const { field, name } = toRefs(props);

const FieldComponent = computed(() => {
  const key: keyof IFields = name.value as keyof IFields;
  return fieldRegistry[key];
});
</script>

<style lang="scss">
.ccb-field__descriptions {
  display: flex;
  flex-direction: column;

  &.ccb-before {
    margin-bottom: 5px;
  }

  &.ccb-after {
    margin-top: 5px;
  }

  & > div {
    width: 100%;
  }
}

.ccb-option-disabled {
  opacity: 0.5 !important;
  pointer-events: none !important;
}
</style>
