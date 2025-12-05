<template>
  <div
    class="ccb-order-field ccb-order-time"
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
      <label class="ccb-order-field__label">
        {{ field.label }}
        <span
          class="ccb-order-required-mark"
          v-if="field.required"
          :format="timerPickerFormat"
          >*</span
        >
      </label>
    </div>
    <component
      :is="getCurrentComponent"
      :field="field"
      @update:model-value="updateValueHandler"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, toRefs } from "vue";
import "@vuepic/vue-datepicker/dist/main.css";
import { IFormField } from "@/widget/shared/types/fields";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IFormField;
};

const timerPickerFormat = computed(() => {
  return field.value?.attributes?.format || false;
});

const props = defineProps<Props>();
const { field } = toRefs(props);

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();

const getCurrentComponent = computed(() => {
  if (field.value?.attributes?.range) {
    return defineAsyncComponent(
      () =>
        import(
          "@/widget/features/submission/order-form/fields/OrderTime/styles/RangeTime.vue"
        ),
    );
  }

  return defineAsyncComponent(
    () =>
      import(
        "@/widget/features/submission/order-form/fields/OrderTime/styles/SingleTime.vue"
      ),
  );
});

const updateValueHandler = (value: Date) => {
  field.value.value = value;
  const { updateValue } = useOrderForm();
  updateValue(field.value.id, value);
};

const isRequired = computed(() => {
  if (!field.value.required) return false;
  return checkFieldRequired(field.value);
});
</script>

<style lang="scss">
.ccb-icon-timepicker-light-clock {
  color: var(--ccb-text-color);
}

.ccb-order-field__error-message {
  color: red;
  font-size: 12px;
}

.ccb-single-timePicker {
  max-width: 140px;
}

.ccb-order-time *:focus {
  outline-width: 0;
}
.ccb-order-time {
  input {
    padding: 10px 32px;
    color: var(--ccb-text-color);
    padding-left: max(30px, var(--ccb-field-side-indent)) !important;

    @media only screen and (max-width: 480px) {
      padding-left: max(30px, var(--ccb-mobile-field-side-indent)) !important;
    }

    &.dp__input_focus {
      border-color: var(--ccb-accent-color);
    }
  }

  .dp__overlay {
    height: 130px !important;
  }

  .dp__menu {
    color: var(--ccb-text-color);
    border: none;
  }

  .dp__action_cancel {
    padding: 10px;
    margin: 0;
    border: none;
    flex: 1;
    border-radius: var(--ccb-fields-border-radius);
    cursor: pointer;
    transition: 300ms ease;
    font-size: var(--ccb-fields-button-size);
    font-weight: var(--ccb-fields-button-weight);
    border: 1px solid (var(--ccb-fields-border-color));
    margin-right: 10px;
    background: var(--ccb-date-picker-day);
    color: var(--ccb-text-color);

    @media only screen and (max-width: 480px) {
      font-size: var(--ccb-mobile-fields-button-size);
      font-weight: var(--ccb-mobile-fields-button-weight);
    }

    &:hover {
      background: color-mix(in srgb, var(--ccb-accent-color), transparent 50%);
    }
  }

  .dp__action_select {
    padding: 10px;
    margin: 0;
    border: none;
    flex: 1;
    border-radius: var(--ccb-fields-border-radius);
    cursor: pointer;
    transition: 300ms ease;
    font-size: var(--ccb-fields-button-size);
    font-weight: var(--ccb-fields-button-weight);
    background: var(--ccb-accent-color);
    color: var(--ccb-fields-bg-color);

    @media only screen and (max-width: 480px) {
      font-size: var(--ccb-mobile-fields-button-size);
      font-weight: var(--ccb-mobile-fields-button-weight);
    }

    &:hover {
      background: hsl(from var(--ccb-accent-color) h s calc(l - 5));
    }
  }

  .dp__selection_preview {
    color: var(--ccb-text-color);
  }

  .dp__menu,
  .dp__overlay_row,
  .dp__arrow_top,
  .dp__arrow_bottom {
    background: var(--ccb-fields-bg-color);
    border: none;
  }
  .dp__outer_menu_wrap {
    box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.25);
  }

  .dp__pm_am_button,
  .dp__btn {
    background: var(--ccb-accent-color);
    color: var(--ccb-fields-bg-color);
  }

  .dp__overlay_cell {
    color: var(--ccb-text-color);
    &:hover {
      background: color-mix(in srgb, var(--ccb-accent-color), transparent 50%);
      color: var(--ccb-text-color);
    }
  }

  .dp__input_icon {
    padding: 10px;
    font-size: 16px;
    color: var(--dp-icon-color);
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .dp__time_col {
    color: var(--ccb-text-color);
    gap: 5px;
    button {
      color: var(--ccb-text-color);
      background: var(--ccb-date-picker-day);
      border-radius: 4px;
      min-width: 42px;
      border: 1px solid transparent;

      &:hover {
        background: color-mix(
          in srgb,
          var(--ccb-accent-color),
          transparent 50%
        );
        border-color: var(--ccb-accent-color);
        color: var(--ccb-text-color);
      }
    }
  }
}
</style>
