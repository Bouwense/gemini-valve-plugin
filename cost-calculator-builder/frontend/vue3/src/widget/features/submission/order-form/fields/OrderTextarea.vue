<template>
  <div
    class="ccb-order-field ccb-order-textarea"
    :class="{
      [`ccb-col-${field.fieldWidth}`]: true,
      'ccb-order-field--required': isRequired,
    }"
  >
    <div class="ccb-order-field__wrapper">
      <div v-if="isRequired" class="ccb-required-tooltip">
        <RequiredHint
          v-if="requiredType === 'characterLimit'"
          :text="
            field.attributes.characterLimit +
            translationsStore.getTranslations.charactersMax
          "
        />
        <RequiredHint
          v-else
          :text="translationsStore.getTranslations.requiredField"
        />
      </div>
      <label class="ccb-order-field__label">
        {{ field.label }}
        <span class="ccb-order-required-mark" v-if="field.required">*</span>
      </label>
      <textarea
        :placeholder="field.placeholder"
        v-model="field.value"
        @input="updateValue"
        cols="6"
        rows="5"
        :maxlength="field.attributes.characterLimit"
      />
      <div class="ccb-order-field__counter">
        <span>{{ valueLength }} / {{ field.attributes.characterLimit }}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IFormField } from "@/widget/shared/types/fields";
import { toRefs, computed, ref } from "vue";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const requiredType = ref<"required" | "characterLimit">("required");

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();

const updateValue = (e: Event): void => {
  requiredType.value = "required";
  const { updateValue } = useOrderForm();
  const target = e.target as HTMLInputElement;
  const value: string = target.value;

  if (value.length > (field.value.attributes.characterLimit || 0)) {
    requiredType.value = "characterLimit";
  }

  updateValue(field.value.id, value);
};

const isRequired = computed(() => {
  if (requiredType.value === "characterLimit") return true;
  if (!field.value.required) return false;
  return checkFieldRequired(field.value);
});

const valueLength = computed(() => {
  return field.value.value?.length || 0;
});
</script>

<style lang="scss" scoped>
.ccb-order-textarea {
  textarea {
    resize: vertical;
  }
}
</style>
