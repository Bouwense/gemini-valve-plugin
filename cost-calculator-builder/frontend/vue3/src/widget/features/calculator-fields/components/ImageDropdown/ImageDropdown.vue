<template>
  <div
    class="ccb-field ccb-dropdown-img-field"
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
        }}<span v-if="field.required" class="ccb-field-required-mark">*</span
        ><ProBadge />
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
          <div v-if="!selectedValue" class="ccb-dropdown__label">
            <img :src="defaultImg" alt="#" />
            <span> {{ translationsStore.getTranslations.selectValue }} </span>
          </div>
          <div v-else class="ccb-dropdown__label">
            <img :src="selectedValue.src" alt="selected-image" />
            <span>{{ selectedValue.optionText }}</span>
          </div>
          <span class="ccb-dropdown__icon">
            <i class="ccb-icon-Path-3485"></i>
          </span>
        </div>

        <div class="ccb-dropdown__list">
          <ul>
            <li @click.prevent="() => selectValue(undefined, true)">
              <img :src="defaultImg" alt="" />
              <span>{{ translationsStore.getTranslations.selectValue }}</span>
            </li>
            <li
              v-for="(option, idx) in fieldOptions"
              :key="option.optionValue"
              @click.prevent="() => selectValue(option)"
              :class="{
                'ccb-option-disabled': field.disableOptions.includes(idx),
              }"
            >
              <img :src="option.src" alt="#" />
              <span class="ccb-dropdown--option">
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
import defaultImg from "@/images/static/close.png";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import { ISingleOptionsField, IOptions } from "@/widget/shared/types/fields";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";
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
const translationsStore = useTranslationsStore();

const isBodyVisible = ref<boolean>(false);
const conditionsStore = useConditionsStore();
const pageBreakerStore = usePageBreakerStore();

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

const selectedValue = computed(() => {
  return field.value.selectedOption;
});

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

  &--option {
    display: flex;
    flex-direction: column;
    align-items: flex-start !important;
    padding: 10px 7px !important;
  }

  &--option-label {
    font-size: 14px;
  }

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
    top: 55px;
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
        margin: 0;
        background: var(--ccb-fields-bg-color);
        color: var(--ccb-text-color);
        font-size: 15px;
        font-weight: var(--ccb-field-weight);

        padding: 10px;
        border-bottom: 1px solid var(--ccb-fields-border-color);
        cursor: pointer;

        display: flex;
        align-items: center;

        @media only screen and (max-width: 480px) {
          font-weight: var(--ccb-mobile-field-weight);
        }

        img {
          width: 50px;
          height: 50px;
          margin-right: 10px;
        }

        &:hover {
          background: var(--ccb-fields-hover-color) !important;
        }
      }
    }
  }
}

.ccb-dropdown-img-field {
  .ccb-dropdown__label {
    display: flex;
    align-items: center;

    img {
      width: 30px;
      height: 30px;
      margin-right: 10px;
    }
  }
  &.active {
    .ccb-dropdown__input {
      border-color: var(--ccb-accent-color);
    }
    .ccb-dropdown__list {
      visibility: visible;
      opacity: 1;
      top: 55px;
    }

    .ccb-dropdown__icon {
      transform: rotate(180deg);
    }
  }
}
</style>
