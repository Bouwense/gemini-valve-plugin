<template>
  <div
    class="ccb-order-field ccb-order-phone"
    :class="{
      [`ccb-col-${field.fieldWidth}`]: true,
      'ccb-order-field--required': isRequired,
    }"
  >
    <div class="ccb-order-field__wrapper">
      <RequiredHint v-if="isRequired" :text="warningTextPhoneField" />
      <label class="ccb-order-field__label">
        {{ field.label }}
        <span class="ccb-order-required-mark" v-if="field.required">*</span>
      </label>
      <input
        type="tel"
        :placeholder="field.placeholder"
        v-model="field.value"
        @input="updateValue"
        pattern="[0-9+]*"
      />
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IFormField } from "@/widget/shared/types/fields";
import { toRefs, computed } from "vue";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

if (field.value.value === undefined) {
  field.value.value = "";
}

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();
const settingStore = useSettingsStore();

const warningTextPhoneField = computed(() => {
  return (
    settingStore.getWarningTexts?.formFields?.phoneField ||
    translationsStore.getTranslations.requiredField
  );
});

const updateValue = (event: Event) => {
  const input = event.target as HTMLInputElement;
  let value = input.value;

  value = value.replace(/[^0-9+]/g, "");

  if (value.includes("+")) {
    if (value.indexOf("+") !== 0) {
      value = value.replace(/\+/g, "");
    }
    if ((value.match(/\+/g) || []).length > 1) {
      value = "+" + value.replace(/\+/g, "");
    }
  }

  field.value.value = value;
};

const isRequired = computed(() => {
  if (!field.value.required) return false;
  return checkFieldRequired(field.value);
});
</script>

<style lang="scss">
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
