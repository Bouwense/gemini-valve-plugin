<template>
  <div class="ccb-field__input-wrapper" ref="dropdownWrapper">
    <div class="ccb-dropdown multi-select">
      <div class="ccb-order-dropdown__input" @click="toggleBody">
        <i class="ccb-icon-Path-3485 ccb-select-arrow"></i>

        <span
          v-if="
            field.attributes &&
            field.attributes.options &&
            selectedValues.length > 0
          "
          class="anchor ccb-heading-5 ccb-light-3 ccb-selected"
        >
          <span
            v-if="selectedValues.length && selectedValues.length < 3"
            class="selected"
            v-for="(option, index) in selectedValues"
            :key="index"
          >
            {{ field.attributes.options[option].optionText }}
            <i class="ccb-icon-close" @click="removeValue(option)"></i>
          </span>
        </span>
        <span
          v-if="selectedValues.length >= 3"
          class="anchor ccb-heading-5 ccb-light ccb-selected"
        >
          {{ selectedValues.length }}
          {{ translationsStore.getTranslations.optionsSelected }}
        </span>
        <span
          v-if="selectedValues.length === 0"
          class="anchor ccb-heading-5 ccb-light-3"
        >
          {{ translationsStore.getTranslations.selectValues }}
        </span>
      </div>
      <div class="ccb-order-dropdown__list">
        <ul>
          <template v-if="field.attributes?.options">
            <li v-for="(option, idx) in field.attributes.options" :key="idx">
              <div class="ccb-default-checkbox">
                <label :for="field.id + 'order_dropdown_checkbox_' + idx"
                  ><input
                    type="checkbox"
                    :id="field.id + 'order_dropdown_checkbox_' + idx"
                    :name="getName"
                    :checked="selectedValues.includes(idx)"
                    :value="option.optionText"
                    @change="() => selectValue(idx)"
                  /><span class="ccb-checkbox-label">{{
                    option.optionText
                  }}</span></label
                >
              </div>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, toRefs, ref } from "vue";
import { IFormField } from "@/widget/shared/types/fields";
import { useOrderFormDropdown } from "@/widget/actions/pro-features/order-form/composable/useOrderFormDropdown.ts";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const emit = defineEmits<{
  (event: "toggle", value: boolean): void;
}>();

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const dropdownWrapper = ref<HTMLElement | null>(null);
const { toggleBody } = useOrderFormDropdown(props, emit, dropdownWrapper);
const translationsStore = useTranslationsStore();

const removeValue = (idx: number): void => {
  const newValues =
    field.value.selectedIdx?.filter((innerIdx) => innerIdx !== idx) || [];
  field.value.selectedIdx = newValues;
};

const selectedValues = computed((): number[] => {
  return field.value.selectedIdx || [];
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

const getName = computed(() => {
  return Math.random().toString(36).substring(2, 15);
});

onMounted(() => {
  if (Array.isArray(field.value?.value)) {
    field.value.value = field.value.value[0];
  }
});
</script>

<style lang="scss">
.ccb-dropdown {
  &.multi-select {
    .ccb-order-dropdown__input {
      padding: 5px 14px;
      display: flex;
      flex-direction: row-reverse;
      align-items: center;
      justify-content: flex-end;

      .ccb-select-arrow {
        position: absolute;
        right: 15px;
        top: 50%;
        font-size: 8px;
        z-index: 2;
        opacity: 0.5;
        transform: translateY(-50%) rotate(0deg);
        transition: all 200ms ease-in-out;
      }
      .selected {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: color-mix(
          in srgb,
          var(--ccb-fields-bg-color) 95%,
          black
        );
        border-radius: 4px;
        padding: 7px 10px;
        margin-right: 10px;
        text-transform: capitalize;

        i {
          margin-left: 5px;
          font-size: 10px;
        }
      }
    }

    .ccb-order-dropdown__list {
      li {
        padding: 0;
      }
      .ccb-default-checkbox {
        display: flex;
        gap: 10px;
        font-size: var(--ccb-field-size);
        font-weight: var(--ccb-field-weight);
        color: var(--ccb-text-color);
        padding: 5px 0px;
        width: 100%;

        @media only screen and (max-width: 480px) {
          font-size: var(--ccb-mobile-field-size);
          font-weight: var(--ccb-mobile-field-weight);
        }

        label {
          display: flex;
          flex-direction: row;
          justify-content: flex-start;
          align-items: center;
          padding: 5px 30px;
          padding-left: 45px;
          margin: 0;
          cursor: pointer;
          line-height: 1.4;
          position: relative;
          width: 100%;

          &:before {
            left: 14px;
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
            left: 19px;
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
    }
  }
}

.ccb-order-dropdown {
  &.active {
    .ccb-select-arrow {
      transform: translateY(-50%) rotate(180deg) !important;
    }
  }
}
</style>
