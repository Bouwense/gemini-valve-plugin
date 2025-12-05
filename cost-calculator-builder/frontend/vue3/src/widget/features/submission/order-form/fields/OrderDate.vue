<template>
  <div
    class="ccb-order-field ccb-order-date"
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
        <span class="ccb-order-required-mark" v-if="field.required">*</span>
      </label>
    </div>
    <div class="ccb-field__input-wrapper">
      <VueDatePicker
        :placeholder="
          field.placeholder
            ? field.placeholder
            : translationsStore.getTranslations.selectDate
        "
        text-input
        :enable-time-picker="false"
        :auto-apply="true"
        :range="field.attributes.range"
        v-model="date"
        :locale="currentLang"
        :format="format"
        @update:model-value="updateValueHandler"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, ref, computed } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { IFormField } from "@/widget/shared/types/fields";
import { useDatePickerFieldHelper } from "@/widget/actions/fields/composable/useDatePickerFieldHelper.ts";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();
const settingStore = useSettingsStore();

const isRequired = computed(() => {
  return checkFieldRequired(field.value);
});

const { displayValueHelper } = useDatePickerFieldHelper();

const currentLang = computed(() => {
  return settingStore.getLanguage;
});

const format = () => displayValueHelper(date.value, currentLang.value);
const date = ref<Date>();

const updateValueHandler = (modelData: Date | Date[]) => {
  const { updateValue } = useOrderForm();
  const value = displayValueHelper(modelData, currentLang.value);
  updateValue(field.value.id, value);
};
</script>

<style lang="scss">
.ccb-order-date *:focus {
  outline-width: 0;
}

.ccb-order-date {
  input {
    padding: 10px 32px;
    padding-left: max(30px, var(--ccb-field-side-indent)) !important;

    @media only screen and (max-width: 480px) {
      padding-left: max(30px, var(--ccb-mobile-field-side-indent)) !important;
    }

    &.dp__input_focus {
      border-color: var(--ccb-accent-color);
    }

    &::placeholder {
      color: var(--ccb-text-color);
    }
  }

  .dp__input_icon {
    color: var(--ccb-text-color);
  }

  .dp--menu-wrapper {
    width: 100%;
    left: 0 !important;
    color: var(--ccb-text-color) !important;
  }

  .dp__active_date {
    background: var(--ccb-accent-color) !important;
  }

  .dp__arrow_bottom,
  .dp__arrow_top {
    border: none;
    background-color: var(--ccb-fields-bg-color);
  }

  .dp__menu {
    border: none;
    box-shadow: 0 10px 30px 0 rgba(0, 0, 0, 0.25);
    border-radius: 4px;
    position: absolute;
  }

  .dp__menu_inner {
    background: var(--ccb-fields-bg-color);
    border-radius: 4px;
  }

  .dp__overlay_action {
    background: var(--ccb-fields-bg-color);

    &:hover {
      background: var(--ccb-accent-color);
      color: var(--ccb-text-color);
    }
  }

  .dp__overlay {
    background: var(--ccb-fields-bg-color);
    color: var(--ccb-text-color);

    .dp__overlay_cell {
      &:hover {
        background: color-mix(
          in srgb,
          var(--ccb-accent-color),
          transparent 50%
        );
        color: var(--ccb-text-color);
      }
    }
  }

  .dp__overlay_cell_active {
    background: var(--ccb-accent-color);
    color: var(--ccb-text-color);
  }

  .dp__action_row {
    background: var(--ccb-fields-bg-color);

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
        background: color-mix(
          in srgb,
          var(--ccb-accent-color),
          transparent 50%
        );
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
  }

  .dp__menu-inner {
    padding: 10px;
  }

  .dp__pointer {
    background: var(--ccb-date-picker-day);
  }

  .dp__inner_nav {
    background: var(--ccb-date-picker-day);
    border-radius: 4px;
  }

  .dp__month_year_wrap {
    gap: 4px;
    .dp__month_year_select {
      background: var(--ccb-date-picker-day);
      border-radius: 4px;
      height: 26px;
      font-size: 14px;
      font-weight: 700;
    }
  }

  .dp__cell_inner {
    width: 54px !important;
    height: 41px !important;
    border-radius: 0px;
    font-size: 12px !important;
    font-weight: 500;
  }

  .dp__range_start,
  .dp__range_end {
    background: var(--ccb-accent-color) !important;
  }

  .dp__range_between {
    background: var(--ccb-date-picker-picket-day) !important;
    color: var(--ccb-accent-color) !important;
  }

  .dp__calendar_item,
  .dp__calendar_header_item {
    color: var(--ccb-text-color) !important;
  }

  .dp__calendar_header_item {
    height: 22px;
  }

  .dp__calendar_item {
    border: 2px solid transparent;
    &:hover {
      border: 2px solid var(--ccb-accent-color);
      background: color-mix(in srgb, var(--ccb-accent-color), transparent 50%);
      color: var(--ccb-text-color) !important;
    }
  }

  .dp__date_hover {
    &:hover {
      color: var(--ccb-text-color) !important;
    }
  }

  .dp__cell_offset {
    opacity: 0.3;
  }

  .dp__today {
    border: none;
  }

  .dp__calendar_row {
    margin: 0;
  }

  .dp__calendar_header_separator {
    display: none;
  }

  .dp__calendar_header {
    font-size: 12px;
    font-weight: 500;
    opacity: 0.3;
  }
}
</style>
