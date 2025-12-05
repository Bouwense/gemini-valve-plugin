<template>
  <div class="ccb-default-image-checkbox-withicon">
    <template v-for="(option, idx) in field.options">
      <label
        :for="getName + '_' + idx + '_' + generateId"
        class="ccb-checkbox-image"
        :class="{
          selected: selectedOptions.includes(option.optionValue),
          'ccb-option-disabled': field.disableOptions.includes(idx),
        }"
        ><div class="ccb-checkbox-image__box">
          <img :src="option.icon" alt="field-img" :style="getImageStyles" />
        </div>
        <div class="ccb-checkbox-image__info">
          <div class="ccb-checkbox-image__title-box">
            <span class="ccb-checkbox-image__label">
              {{ option.optionText }}
            </span>
          </div>
        </div>
        <div class="ccb-checkbox-image__item">
          <input
            type="checkbox"
            :id="getName + '_' + idx + '_' + generateId"
            :name="getName"
            :value="option.optionValue"
            v-model="optionValues"
            @change="changeValue"
          />
          <label :id="getName + '_' + idx"></label></div
      ></label>
    </template>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IMultiOptionsField } from "@/widget/shared/types/fields";
import { useMultiOptionChildShared } from "@/widget/actions/fields/composable/useMultiOptionChildShared.ts";

const emit = defineEmits<{
  (event: "update", value: string): void;
}>();

type Props = {
  field: IMultiOptionsField;
  values: string[];
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const generateId = computed(() => {
  return Math.random().toString(36).substring(2, 15);
});

const { optionValues, changeValue, getName, selectedOptions, getImageStyles } =
  useMultiOptionChildShared(props, emit);
</script>

<style lang="scss" scoped>
.ccb-default-image-checkbox-withicon {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  flex-direction: flex-start;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  .ccb-checkbox-image {
    border: 1px solid var(--ccb-fields-border-color);
    background: var(--ccb-fields-color);
    display: flex;
    flex-direction: column;
    position: relative;
    border-radius: 4px;
    cursor: pointer;
    padding: 10px 18px;
    row-gap: 7px;

    &.selected {
      border-color: var(--ccb-accent-color);
      background: var(--ccb-fields-bg-color-alpha);
    }

    &__box {
      line-height: 0.7;
      display: flex;
      justify-content: center;
      flex: 1;
      width: 100%;
      height: 100%;
      box-sizing: border-box;

      img {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        object-fit: cover;
        aspect-ratio: 1 / 1;
      }
    }

    &__info {
      position: relative;
      word-break: break-word;
    }

    &__title-box {
      display: flex;
      justify-content: center;
      row-gap: 10px;
    }

    &__label {
      font-size: var(--ccb-field-size);
      font-weight: var(--ccb-field-weight);
      color: var(--ccb-text-color);
      word-break: break-word;

      @media only screen and (max-width: 480px) {
        font-size: var(--ccb-mobile-field-size);
        font-weight: var(--ccb-mobile-field-weight);
      }
    }

    &__price {
      font-size: var(--ccb-field-size-2);
      color: var(--ccb-fields-description-color);

      @media only screen and (max-width: 480px) {
        font-size: var(--ccb-mobile-field-size);
      }
    }
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
      display: none;
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

      &[type="checkbox"]:checked {
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

    .ccb-checkbox-label {
      word-break: break-word;
    }
  }
}

.ccb-vertical-checkbox {
  .ccb-default-checkbox-image {
    flex-direction: column;
  }

  .ccb-checkbox-image {
    width: 100%;
  }
}

.ccb-horizontal {
  .ccb_field_with_checkbox_with_img {
    grid-column: 1 / span 2;
    .ccb-checkbox-image {
      max-width: 200px !important;
      width: fit-content !important;
    }
  }
}
</style>
