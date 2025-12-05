<template>
  <div
    class="ccb-field ccb-dropdown-field"
    :class="{
      active: isBodyVisible,
      required: isRequired,
      'ccb-field-disabled': field.disabled,
      [additionalClasses]: true,
    }"
  >
    <div class="ccb-field__label">
      <RequiredHint v-if="isRequired" :text="requiredWarningText" />
      <div class="ccb-field__title">
        {{ field.label
        }}<span v-if="field.required" class="ccb-field-required-mark">*</span>
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

    <div class="ccb-field__input-wrapper" ref="dropdownWrapper">
      <div class="ccb-dropdown">
        <div class="ccb-dropdown__input" @click="toggleBody">
          <span v-if="!selectedValue" class="ccb-dropdown__label">
            {{ translationsStore.getTranslations.selectValue }}
          </span>
          <span v-else class="ccb-dropdown__label">
            {{ selectedValue.optionText }}
          </span>
          <span class="ccb-dropdown__icon">
            <i class="ccb-icon-Path-3485"></i>
          </span>
        </div>
        <div class="ccb-dropdown__list">
          <ul>
            <li @click.prevent="() => selectValue(undefined, true)">
              {{ translationsStore.getTranslations.selectValue }}
            </li>
            <li
              v-for="(option, idx) in fieldOptions"
              :key="option.optionValue"
              @click.prevent="() => selectValue(option)"
              class="ccb-dropdown--option"
              :class="{
                'ccb-option-disabled': field.disableOptions.includes(idx),
              }"
            >
              <span class="ccb-dropdown--option-label">
                {{ option.optionText }}
              </span>
              <span
                class="ccb-dropdown--option-converted"
                v-if="field.showValueInOption"
              >
                {{ translationsStore.getTranslations.price }}:
                {{ option.optionConverted }}
              </span>
            </li>
          </ul>
        </div>
      </div>
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
import { toRefs, ref, computed } from "vue";
import { IOptions, ISingleOptionsField } from "@/widget/shared/types/fields";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

type Props = {
  field: ISingleOptionsField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const fieldStore = useFieldsStore();
const singleFieldInstance = useSingleField();
const appearanceStore = useAppearanceStore();
const conditionsStore = useConditionsStore();
const translationsStore = useTranslationsStore();
const pageBreakerStore = usePageBreakerStore();

const isBodyVisible = ref<boolean>(false);

const dropdownWrapper = ref<HTMLElement | null>(null);

const requiredWarningText = computed(() => {
  const settingStore = useSettingsStore();
  return settingStore.getWarningTexts?.requiredMsg || "";
});

const toggleBody = () => {
  if (!isBodyVisible.value) {
    document.addEventListener("click", handleClickOutside);
  } else {
    document.removeEventListener("click", handleClickOutside);
  }
  isBodyVisible.value = !isBodyVisible.value;
};

const handleClickOutside = (event: MouseEvent) => {
  if (
    dropdownWrapper.value &&
    "contains" in dropdownWrapper.value &&
    !dropdownWrapper.value.contains(event.target as Node)
  ) {
    isBodyVisible.value = false;
    document.removeEventListener("click", handleClickOutside);
  }
};

const selectValue = (option: IOptions | undefined, toggle: boolean = true) => {
  if (option?.optionValue !== selectedValue.value?.optionValue) {
    let value: number = 0;

    if (
      option?.optionValue &&
      field.value.summaryView !== "show_label_not_calculable"
    ) {
      const optionValue: string = option?.optionValue;
      value = +optionValue.split("_")[0] || 0;
    }

    field.value.selectedOption = option;
    field.value.value = value;

    field.value.displayValue =
      singleFieldInstance.getMultipleOptionsFieldDisplayView(
        field.value,
      ) as string;

    fieldStore.updateField(field.value.alias, field.value);
    conditionsStore.applyConditionForField(field.value.alias);

    if (
      fieldStore.getPageBreakEnabled &&
      fieldStore.getActivePage.action === "not_skip"
    ) {
      pageBreakerStore.checkPageFieldsConditions();
    }
  }

  if (toggle) {
    toggleBody();
  }
};

const selectedValue = computed(() => field.value.selectedOption);

const fieldOptions = computed(() => {
  return field.value.options?.map((f: IOptions) => {
    return {
      ...f,
    };
  });
});

const isRequired = computed(() => {
  return fieldStore.checkFieldRequired(field.value);
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss" scoped>
@use "@/styles/widget/_mixins.scss" as mixins;

.ccb-dropdown {
  position: relative;
  &--option-converted {
    font-size: calc(var(--ccb-summary-text-size) - 2px);
    color: var(--ccb-text-color);

    @media only screen and (max-width: 480px) {
      font-size: calc(var(--ccb-mobile-summary-text-size) - 2px);
    }
  }

  &__input {
    @include mixins.field;
  }

  &__input {
    padding-right: 10px;

    display: grid;
    grid-template-columns: 1fr auto;
    justify-content: space-between;
    cursor: pointer;

    @media only screen and (max-width: 480px) {
      padding: 12px var(--ccb-mobile-field-side-indent);
      min-height: var(--ccb-mobile-field-button-height);
    }
  }

  &__icon {
    font-size: 8px;
    display: grid;
    place-items: center;
    transition: 300ms ease;
  }

  &__list {
    margin-top: 5px;
    border-radius: 4px;
    background: #ffffff;
    border-color: transparent;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    max-height: 261px;
    position: absolute;
    top: 25px;
    left: 0;
    width: 100%;
    z-index: 100;
    visibility: hidden;
    opacity: 0;
    transition: 200ms ease;
    overflow-y: auto;

    ul {
      list-style: none;
      padding: 0;
      margin: 0;
      background: var(--ccb-fields-bg-color);

      li {
        display: flex;
        flex-direction: column;
        align-items: flex-start !important;
        row-gap: 0px;
        padding: 10px 18px !important;
        margin: 0;
        background: var(--ccb-fields-bg-color);
        color: var(--ccb-text-color);

        border-bottom: 1px solid var(--ccb-fields-border-color);
        cursor: pointer;
        font-size: 15px;
        font-weight: var(--ccb-field-weight);

        .ccb-dropdown--option-label {
          font-size: 14px;
          font-weight: 500;
        }

        .ccb-dropdown--option-converted {
          font-size: 12px;
          font-weight: 400;
        }

        &:hover {
          background: var(--ccb-fields-hover-color) !important;
        }

        @media only screen and (max-width: 480px) {
          font-weight: var(--ccb-mobile-field-weight);
        }
      }
    }
  }
}

.ccb-dropdown-field {
  &.active {
    .ccb-dropdown__input {
      border-color: var(--ccb-accent-color);
    }
    .ccb-dropdown__list {
      visibility: visible;
      opacity: 1;
      top: 45px;
      z-index: 9999;
    }

    .ccb-dropdown__icon {
      transform: rotate(180deg);
    }
  }
}
</style>
