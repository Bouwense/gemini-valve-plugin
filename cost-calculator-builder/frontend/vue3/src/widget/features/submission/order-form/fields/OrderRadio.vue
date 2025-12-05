<template>
  <div
    class="ccb-order-field ccb-order-radio"
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
        class="ccb-default-radio"
        :class="{ [field.attributes?.display || '']: true }"
      >
        <template v-if="field.attributes?.options">
          <label v-for="(option, idx) in field.attributes.options" :key="idx">
            <input
              type="radio"
              :name="'form_radio_' + field.id"
              :value="option.optionText"
              @change="changeValue"
              v-model="selected"
            />
            <span class="ccb-radio-label">
              {{ option.optionText }}
            </span>
          </label>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { IFormField } from "@/widget/shared/types/fields";
import { onMounted, ref, toRefs, computed } from "vue";
import { splitByLastUnderscore } from "@/widget/shared/utils/split-by-last-underscore.utils.ts";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IFormField;
};

const selected = ref<string>("");

const props = defineProps<Props>();
const { field } = toRefs(props);

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();

const isRequired = computed(() => {
  if (!field.value.required) return false;
  return checkFieldRequired(field.value);
});

const changeValue = (): void => {
  field.value.value = selected.value;
  const { updateValue } = useOrderForm();
  updateValue(field.value.id, field.value.value);
};

onMounted(() => {
  if (Array.isArray(field.value.value)) {
    field.value.value = splitByLastUnderscore(field.value.value[0] || "");
    selected.value = field.value.value;
  }
});
</script>

<style lang="scss" scoped>
.ccb-order-radio {
  .ccb-order-field__title {
    position: relative;
    width: fit-content;
  }

  .ccb-default-radio {
    padding: 6px 0;
    font-size: var(--ccb-field-size);
    font-weight: var(--ccb-field-weight);
    color: var(--ccb-text-color);

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
      line-height: 1;
      width: fit-content;

      input {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        outline: none;
        max-width: 20px;
        min-width: 20px;
        max-height: 20px;
        min-height: 20px;
        border-radius: 50%;
        position: relative;
        border: 2px solid var(--ccb-fields-border-color);
        background-color: var(--ccb-container-color);
        -webkit-appearance: none;
        margin-right: 7px;
        margin-top: 0;
        outline: none !important;
        box-shadow: none !important;
        box-sizing: border-box;
        padding: 0px;
        margin: 0px;
        margin-right: 7px;
        transition: 300ms ease;

        &[type="radio"]:checked {
          border-color: var(--ccb-accent-color);
          background-color: var(--ccb-accent-color);
        }

        &:before {
          content: "";
          width: 10px;
          min-width: 10px;
          height: 10px;
          display: block;
          border-radius: 50%;
          background-color: var(--ccb-container-color) !important;
        }
      }

      .ccb-radio-label {
        word-break: break-word;
      }
    }

    &.vertical {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    &.horizontal {
      display: flex;
      flex-direction: row;
      gap: 8px;
    }
  }
}
</style>
