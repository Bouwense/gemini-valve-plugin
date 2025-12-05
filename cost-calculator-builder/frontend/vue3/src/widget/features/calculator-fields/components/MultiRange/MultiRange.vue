<template>
  <div
    class="ccb-field ccb-multi-range-field"
    :class="{
      required: isRequired,
      'ccb-field-disabled': field.disabled,
      [additionalClasses]: true,
    }"
  >
    <div class="ccb-field__label">
      <RequiredHint v-if="isRequired" :text="requiredWarningText" />
      <div class="ccb-field__title">
        <span
          class="ccb-field__title--title-box"
          style="display: flex; width: 100%; justify-content: space-between"
        >
          <span
            >{{ field.label
            }}<span v-if="field.required" class="ccb-field-required-mark"
              >*</span
            ></span
          >
          <span> {{ getSignValue }} </span>
          <ProBadge />
        </span>
      </div>
    </div>

    <div
      v-if="appearanceStore.getAppearanceDescriptionPosition === 'before'"
      class="ccb-field__descriptions ccb-before"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>

    <div class="ccb-field-input__wrapper">
      <component
        :is="currentComponents"
        :field="field"
        @update:modelValue="updateValue"
        v-model="values"
      />
    </div>

    <div
      v-if="appearanceStore.getAppearanceDescriptionPosition === 'after'"
      class="ccb-field__descriptions ccb-after"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, ref, onMounted, defineAsyncComponent } from "vue";
import { IMultiRangeField } from "@/widget/shared/types/fields";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useCallbackStore } from "@/widget/app/providers/stores/callbackStore.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

type Props = {
  field: IMultiRangeField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const currencyInstance = useCurrency();
const fieldStore = useFieldsStore();
const appearanceStore = useAppearanceStore();
const singleFieldMixins = useSingleField();
const callbackStore = useCallbackStore();
const conditionsStore = useConditionsStore();
const pageBreakerStore = usePageBreakerStore();

const values = ref<number[]>(field.value.values || [0, 50]);
const originalValue = ref<number>(0);

const requiredWarningText = computed(() => {
  const settingStore = useSettingsStore();
  return settingStore.getWarningTexts?.requiredMsg || "";
});

const currentComponents = computed(() => {
  const style = field.value?.styles?.style || "default";
  if (style === "default") {
    return defineAsyncComponent(() => import("./styles/Default.vue"));
  } else if (style === "small") {
    return defineAsyncComponent(() => import("./styles/Small.vue"));
  } else if (style === "flat-minimal") {
    return defineAsyncComponent(() => import("./styles/Flat.vue"));
  } else if (style === "modern") {
    return defineAsyncComponent(() => import("./styles/Modern.vue"));
  } else if (style === "input") {
    return defineAsyncComponent(() => import("./styles/Input.vue"));
  } else if (style === "multi-point") {
    return defineAsyncComponent(() => import("./styles/MultiPoint.vue"));
  }

  return "";
});

const updateValue = (
  inputValues: number[],
  alias?: string,
  fromCondition?: boolean,
) => {
  if (alias && alias !== field.value.alias) {
    return;
  }

  let value = 0;
  const [left, right] = inputValues;

  values.value = [left, right];

  if (right - left > 0) {
    value = right - left;
  }

  originalValue.value = value;

  if (field.value.multiply) {
    value = value * field.value.unit;
  }

  value = field.value.round ? Math.round(value) : value;

  field.value.value = value || 0;
  field.value.values = inputValues;

  field.value.displayValue = singleFieldMixins.getCommonFieldDisplayView(
    field.value,
    (right - left).toString(),
  );
  fieldStore.updateField(field.value.alias, field.value, fromCondition);
  conditionsStore.applyConditionForField(field.value.alias);

  if (
    fieldStore.getPageBreakEnabled &&
    fieldStore.getActivePage.action === "not_skip"
  ) {
    pageBreakerStore.checkPageFieldsConditions();
  }
};

const getSignValue = computed(() => {
  if (field.value.multipliedTotal) {
    return field.value.useCurrency || field.value.fieldCurrency
      ? currencyInstance.formatCurrency(
          field.value.value,
          currencyInstance.getCurrencyOptions(field.value),
        )
      : singleFieldMixins.parseCommonFieldSign(
          field.value.value.toString(),
          field.value.sign,
          field.value.unitPosition,
        );
  }

  if (field.value.useCurrency || field.value.fieldCurrency) {
    if (field.value.unitPosition === "left") {
      return `${field.value.sign} ${originalValue.value}`;
    }

    return `${originalValue.value} ${field.value.sign}`;
  }

  return singleFieldMixins.parseCommonFieldSign(
    field.value.round
      ? originalValue.value.toString()
      : getFormatValue(originalValue.value),
    field.value.sign,
    field.value.unitPosition,
  );
});

const isRequired = computed(() => {
  return fieldStore.checkFieldRequired(field.value);
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});

const getFormatValue = (num: number): string => {
  const rounded = num.toFixed(2);
  return parseFloat(rounded).toString();
};

callbackStore.add("updateMultiRange", updateValue);

onMounted(() => {
  const [left, right] = field.value.values;
  originalValue.value = right - left;
});
</script>

<style lang="scss">
.ccb-multi-range-field {
  display: flex;
  flex-direction: column;

  .slider-base {
    background-color: var(--ccb-fields-border-color) !important;
  }

  .slider-tooltip,
  .slider-handle,
  .slider-connect {
    background-color: var(--ccb-accent-color) !important;
  }

  .slider-connects {
    cursor: pointer;
  }

  .slider-tooltip {
    color: var(--ccb-fields-color) !important;
    border-color: var(--ccb-accent-color) !important;
    @media (max-width: 1024px) {
      display: block !important;
    }
  }

  .slider-handle:focus {
    box-shadow: none !important;
  }
}
</style>
