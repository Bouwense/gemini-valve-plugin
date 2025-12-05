<template>
  <div
    class="ccb-field ccb-timePicker-field"
    :class="{
      required: isRequired,
      'ccb-24-hours': getFormat,
      'ccb-field-disabled': field.disabled,
      [additionalClasses]: additionalClasses,
    }"
  >
    <div class="ccb-field__label">
      <RequiredHint v-if="isRequired" :text="requiredWarningText" />
      <div class="ccb-field__title">
        {{ field.label
        }}<span v-if="field.required" class="ccb-field-required-mark">*</span
        ><ProBadge />
      </div>
    </div>
    <div
      v-if="appearanceStore.descriptionPosition === 'before'"
      class="ccb-field__descriptions ccb-before"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>

    <div class="ccb-field__input-wrapper">
      <component
        :is="currentComponents"
        v-model="selectedTime"
        :alias="field.alias"
        :format="getFormat"
        :default="'23:05'"
        :field="field"
        :min-interval="getMinInterval"
        @update:model-value="updateValue"
      />
    </div>
    <div
      v-if="appearanceStore.descriptionPosition === 'after'"
      class="ccb-field__descriptions ccb-after"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, defineAsyncComponent, onMounted, ref } from "vue";
import { ITimePickerField } from "@/widget/shared/types/fields";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

type Props = {
  field: ITimePickerField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const appearanceStore = useAppearanceStore();
const fieldStore = useFieldsStore();
const conditionsStore = useConditionsStore();

const requiredWarningText = computed(() => {
  const settingStore = useSettingsStore();
  return settingStore.getWarningTexts?.requiredMsg || "";
});

const getRangeType = computed(() => {
  return typeof field.value.range === "string"
    ? field.value.range === "1"
    : field.value.range;
});

const getMinInterval = computed(() => {
  if (!field.value.useInterval) return "";
  return field.value.minInterval || "1h";
});

const getDefaultValue = computed(() => {
  const hours =
    field.value.placeholderHours.length > 0
      ? field.value.placeholderHours
      : new Date().getHours();
  const minutes =
    field.value.placeholderTime.length > 0
      ? field.value.placeholderTime
      : new Date().getMinutes();

  return { hours: hours, minutes: minutes, seconds: 0 };
});

const getFormat = computed(() => {
  return field.value.format || false;
});

const currentComponents = computed(() => {
  if (getRangeType.value) {
    return defineAsyncComponent(() => import("./styles/RangeTime.vue"));
  } else {
    return defineAsyncComponent(() => import("./styles/SingleTime.vue"));
  }
});

const isRequired = computed(() => {
  return fieldStore.checkFieldRequired(field.value);
});

const selectedTime = ref<{
  hours: number;
  minutes: number;
  seconds: number;
} | null>(null);

onMounted(() => {
  selectedTime.value = {
    hours: Number(getDefaultValue.value.hours),
    minutes: Number(getDefaultValue.value.minutes),
    seconds: 0,
  };
  field.value.displayValue = getDisplayValue.value;
  fieldStore.updateField(field.value.alias, field.value, true);
});

const getDisplayValue = computed(() => {
  if (!selectedTime.value) return "";

  if (Array.isArray(selectedTime.value)) {
    const from = selectedTime.value[0];
    const to = selectedTime.value[1];

    return `${formatTime(from)} - ${formatTime(to)}`;
  } else {
    return formatTime(selectedTime.value);
  }
});

const formatTime = (time: { hours: number; minutes: number }) => {
  const { hours, minutes } = time;
  const formattedMinutes = minutes.toString().padStart(2, "0");

  if (!getFormat.value) {
    const ampm = hours >= 12 ? "PM" : "AM";
    const formattedHours = hours % 12 || 12;
    return `${formattedHours}:${formattedMinutes} ${ampm}`;
  } else {
    const formattedHours = hours.toString().padStart(2, "0");
    return `${formattedHours}:${formattedMinutes}`;
  }
};

const updateValue = () => {
  field.value.displayValue = getDisplayValue.value;
  fieldStore.updateField(field.value.alias, field.value);
  conditionsStore.applyConditionForField(field.value.alias);
};

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
.ccb-timePicker-field *:focus {
  outline-width: 0;
}
.ccb-icon-timepicker-light-clock {
  color: var(--ccb-text-color);
}
.ccb-timePicker-field {
  input {
    color: var(--ccb-text-color);
    padding: 12px var(--ccb-field-side-indent);
    padding-left: max(30px, var(--ccb-field-side-indent));

    @media only screen and (max-width: 480px) {
      padding-left: max(30px, var(--ccb-mobile-field-side-indent));
    }

    &.dp__input_focus {
      border-color: var(--ccb-accent-color);
    }
  }

  .dp__input_icon {
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--ccb-text-color);
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
    background: var(--ccb-accent-color) !important;
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

  .dp__pm_am_button {
    background: var(--ccb-accent-color);
    color: var(--ccb-fields-bg-color);
  }

  .dp__btn {
    background: var(--ccb-fields-bg-color);
    transform: 300ms easy;

    &:hover {
      background: var(--ccb-accent-color);
      color: var(--ccb-text-color);
    }
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
