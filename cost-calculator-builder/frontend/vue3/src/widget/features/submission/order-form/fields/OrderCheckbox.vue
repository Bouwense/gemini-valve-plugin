<template>
  <div
    class="ccb-order-field ccb-order-checkbox"
    :class="{
      [`ccb-col-${field.fieldWidth}`]: true,
      'ccb-order-field--required': isRequired,
    }"
  >
    <div class="ccb-order-field__wrapper">
      <div class="ccb-order-field__title">
        <RequiredHint
          v-if="isRequired"
          :text="translationsStore.getTranslations.requiredField"
        />
        <label class="ccb-order-field__label">
          {{ field.label }}
          <span class="ccb-order-required-mark" v-if="field.required">*</span>
        </label>
      </div>
      <div
        class="ccb-default-checkbox"
        :class="{ [field.attributes?.display || '']: true }"
      >
        <label
          :for="field.id + 'order_checkbox_' + idx"
          v-for="(option, idx) in field.attributes.options"
          :key="idx"
        >
          <input
            type="checkbox"
            :id="field.id + 'order_checkbox_' + idx"
            :name="getName"
            :value="option.optionText"
            :checked="selectedValues.includes(idx)"
            @change="() => selectValue(idx)"
          />
          <span class="ccb-checkbox-label">{{ option.optionText }}</span>
        </label>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IFormField } from "@/widget/shared/types/fields";
import { computed, toRefs } from "vue";
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

const selectedValues = computed((): number[] => {
  return field.value.selectedIdx || [];
});

const getName = computed(() => {
  return Math.random().toString(36).substring(2, 15);
});

const selectValue = (idx: number): void => {
  let newValues: number[] = field.value.selectedIdx || [];
  if (selectedValues.value.includes(idx)) {
    newValues =
      field.value.selectedIdx?.filter((innerIdx) => innerIdx !== idx) || [];
  } else {
    newValues.push(idx);
  }

  let values: string[] = [];
  const options = field.value?.attributes?.options || [];
  for (let i = 0; i < options.length; i++) {
    const option = options[i];
    if (newValues.includes(i)) {
      values.push(option.optionText || "");
    }
  }

  const { updateValue } = useOrderForm();
  field.value.value = values;
  updateValue(field.value.id, field.value.value, newValues);
};
</script>

<style lang="scss" scoped>
.ccb-order-checkbox {
  .ccb-order-field__title {
    position: relative;
    width: fit-content;
  }

  .ccb-default-checkbox {
    &.vertical {
      flex-direction: column;
    }
  }
}

.ccb-default-checkbox {
  display: flex;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  padding: 5px 0px;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  label {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    padding: 0;
    margin: 0;
    cursor: pointer;
    line-height: 1.4;
    width: fit-content;
    padding: 0 0 0 25px;
    position: relative;

    &:before {
      left: 0;
      content: "";
      max-width: 20px;
      min-width: 20px;
      max-height: 20px;
      min-height: 20px;
      height: 20px;
      width: 20px;
      position: absolute;
      margin: 0 !important;
      background-color: var(--ccb-field-bg-color);
      border: 2px solid var(--ccb-fields-border-color);
      transition: transform 0.28s ease;
      box-sizing: border-box;
      border-radius: 3px;
    }

    &:after {
      left: 5px;
      height: 5px;
      width: 10px;
      content: "";
      display: block;
      position: absolute;
      border-left: 2px solid #fff;
      border-bottom: 2px solid #fff;
      transition: transform ease 0.25s;
      transform: rotate(-45deg) scale(0) translateY(-10%);
      box-sizing: border-box;
    }

    input {
      display: none;
    }

    .ccb-checkbox-label {
      word-break: break-word;
    }
  }

  label:has(input[type="checkbox"]:checked):before {
    background: var(--ccb-accent-color);
    border-color: var(--ccb-accent-color);
  }

  label:has(input[type="checkbox"]:checked):after {
    transform: rotate(-45deg) scale(1) translateY(-10%);
  }
}
</style>
