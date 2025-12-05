<template>
  <div
    class="ccb-field ccb-range-field"
    :class="{
      required: isRequired,
      'ccb-field-disabled': field.disabled,
      [additionalClasses]: true,
    }"
  >
    <div class="ccb-field__label">
      <RequiredHint v-if="isRequired" :text="requiredWarningText" />
      <div class="ccb-field__title">
        <span class="ccb-field__title--title-box">
          <span
            >{{ field.label
            }}<span v-if="field.required" class="ccb-field-required-mark"
              >*</span
            ></span
          >
          <span>
            {{ getSignValue }}
          </span>
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
        v-model="rawInput"
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
import { IRangeField } from "@/widget/shared/types/fields";

import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useCallbackStore } from "@/widget/app/providers/stores/callbackStore.ts";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency.ts";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

type Props = {
  field: IRangeField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const fieldStore = useFieldsStore();
const appearanceStore = useAppearanceStore();
const conditionsStore = useConditionsStore();
const singleFieldMixins = useSingleField();
const callbackStore = useCallbackStore();
const currencyInstance = useCurrency();
const rawInput = ref<number>(0);
const pageBreakerStore = usePageBreakerStore();

onMounted(() => {
  rawInput.value = field.value.originalValue
    ? field.value.originalValue
    : field.value.min;

  updateValue(rawInput.value, undefined, true);
});

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
  value: number,
  alias?: string,
  fromCondition?: boolean,
) => {
  if (alias && alias !== field.value.alias) {
    return;
  }

  rawInput.value = value;
  field.value.originalValue = value;

  if (field.value.multiply) {
    value = value * field.value.unit;
  }

  field.value.value = field.value.round ? Math.round(value || 0) : value || 0;

  field.value.displayValue = singleFieldMixins.getCommonFieldDisplayView(
    field.value,
    rawInput.value.toString(),
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
      return `${field.value.sign} ${rawInput.value}`;
    }

    return `${rawInput.value} ${field.value.sign}`;
  }

  if (field.value.unitPosition === "left") {
    return `${field.value.sign} ${rawInput.value}`;
  }

  return `${rawInput.value} ${field.value.sign}`;
});

const isRequired = computed(() => {
  return fieldStore.checkFieldRequired(field.value);
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});

callbackStore.add("updateRange", updateValue);
</script>

<style lang="scss">
.ccb-range-field {
  display: flex;
  flex-direction: column;

  .slider-base {
    background-color: var(--ccb-fields-border-color) !important;
  }

  .slider-tooltip,
  .slider-handle,
  .slider-connect {
    background-color: var(--ccb-accent-color);
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

.ccb-field__title {
  &--title-box {
    width: 100%;
    display: flex;
    justify-content: space-between;
  }
}

:root {
  --slider-tooltip-min-width: 30px;
  --slider-connect-bg: var(--ccb-accent-color);
  --slider-bg: var();
  --slider-tooltip-bg: var(--ccb-accent-color);
  --slider-tooltip-color: var(--ccb-fields-text-color);
}
</style>
