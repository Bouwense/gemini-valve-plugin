<template>
  <div
    class="ccb-order-field ccb-order-email"
    :class="{
      [`ccb-col-${field.fieldWidth}`]: true,
      'ccb-order-field--required': isRequired,
    }"
  >
    <div class="ccb-order-field__wrapper">
      <div v-if="isRequired" class="ccb-required-tooltip">
        <RequiredHint
          v-if="errorType === 'incorrect_email'"
          :text="warningTextEmailFormat"
        />
        <RequiredHint v-else :text="warningTextEmailField" />
      </div>
      <label class="ccb-order-field__label">
        {{ field.label }}
        <span class="ccb-order-required-mark" v-if="field.required">*</span>
      </label>
      <input
        type="text"
        @focusout="validateEmailHandler"
        @input="updateValueHandler"
        :placeholder="field.placeholder"
      />
    </div>

    <div
      class="ccb-order-field__wrapper"
      style="padding-top: 10px"
      v-if="field.attributes?.confirmation"
    >
      <label class="ccb-order-field__label">
        {{ field.attributes?.confirmationLabel }}
        <span class="ccb-order-required-mark">*</span>
      </label>
      <input
        type="text"
        v-model="confirmationEmail"
        @input="updateValueConfirmValue"
        :placeholder="field.attributes.confirmationPlaceholder"
      />
      <span class="ccb-order-field__same-email" v-if="!isSameEmail">
        {{ translationsStore.getTranslations.theEmailDoNotMatch }}</span
      >
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IFormField } from "@/widget/shared/types/fields";
import { toRefs, ref, computed } from "vue";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { validateEmail } from "@/widget/shared/utils/validate-email.utils";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();
const settingStore = useSettingsStore();

const warningTextEmailField = computed(() => {
  return (
    settingStore.getWarningTexts?.formFields?.emailField ||
    translationsStore.getTranslations.requiredField
  );
});

const warningTextEmailFormat = computed(() => {
  return (
    settingStore.getWarningTexts?.formFields?.emailFormat ||
    translationsStore.getTranslations.incorrectEmailFormat
  );
});

const errorType = ref<string>("");
const isSameEmail = ref<boolean>(true);
const confirmationEmail = ref<string>("");

const updateValueHandler = (e: Event): void => {
  errorType.value = "";
  const { updateValue } = useOrderForm();
  const target = e.target as HTMLInputElement;
  const value: string = target.value;
  if (value && !validateEmail(value)) {
    errorType.value = "incorrect_email";
  }

  updateValue(field.value.id, value);
  updateValueConfirmValue();
};

const updateValueConfirmValue = (): void => {
  isSameEmail.value = field.value.value === confirmationEmail.value;
};

const validateEmailHandler = (e: Event): void => {
  const target = e.target as HTMLInputElement;
  const value: string = target.value;

  if (value && !validateEmail(value)) {
    errorType.value = "incorrect_email";
  }
};

const isRequired = computed(() => {
  if (errorType.value === "incorrect_email") return true;
  if (!field.value.required) return false;
  return checkFieldRequired(field.value);
});
</script>

<style lang="scss">
.ccb-order-field {
  &__same-email {
    color: red;
    font-size: 12px;
    font-weight: 500;
    text-transform: none;
  }
}
</style>
