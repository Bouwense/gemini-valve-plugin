<template>
  <div
    class="ccb-order-field ccb-order-number"
    :class="{
      [`ccb-col-${field.fieldWidth}`]: true,
      'ccb-order-field--required': isRequired,
    }"
  >
    <div class="ccb-order-field__title">
      <RequiredHint
        v-if="isRequired"
        :text="translationsStore.getTranslations.requiredField"
      />
      <label :for="'number_' + field.id" class="ccb-order-field__label">
        {{ field.label }}
        <span class="ccb-order-required-mark" v-if="field.required">*</span>
      </label>
    </div>
    <div class="ccb-order-field__wrapper">
      <input
        type="number"
        :id="'number_' + field.id"
        :placeholder="field.placeholder"
        @input="updateValueHandler"
        v-model="field.value"
      />
      <div class="ccb-input-counter up" @click="increment">
        <i class="ccb-icon-Path-3486"></i>
      </div>
      <div class="ccb-input-counter down" @click="decrement">
        <i class="ccb-icon-Path-3485"></i>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { toRefs, computed } from "vue";
import { IFormField } from "@/widget/shared/types/fields";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();

const isRequired = computed(() => {
  if (!field.value.required) return false;
  return checkFieldRequired(field.value);
});

const updateValueHandler = (e: Event): void => {
  const { updateValue } = useOrderForm();
  const target = e.target as HTMLInputElement;
  const value: string = target.value;
  updateValue(field.value.id, value);
};

const increment = (): void => {
  const { updateValue } = useOrderForm();
  let value = Number(field.value.value || 0);
  updateValue(field.value.id, value + 1);
};

const decrement = (): void => {
  const { updateValue } = useOrderForm();
  let value = Number(field.value.value || 0);
  updateValue(field.value.id, value - 1 || 0);
};
</script>

<style lang="scss" scoped>
.ccb-order-number {
  .ccb-order-field__wrapper {
    position: relative;
  }
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
