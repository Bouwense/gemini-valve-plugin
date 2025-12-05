<template>
  <div class="ccb-field__input-wrapper">
    <input
      type="text"
      :placeholder="field.placeholder"
      @input="onInput"
      :value="field.displayValue"
    />
  </div>
</template>

<script setup lang="ts">
import { IValidatedFormField } from "@/widget/shared/types/fields/input.type";
import { useValidatedFormField } from "@/widget/actions/fields/composable/useValidatedFormField.ts";

const emit = defineEmits<{
  (event: "update", value: string): void;
}>();

type Props = {
  field: IValidatedFormField;
};

const props = defineProps<Props>();
const { field, onInput } = useValidatedFormField(props, emit);
</script>

<style lang="scss"></style>
