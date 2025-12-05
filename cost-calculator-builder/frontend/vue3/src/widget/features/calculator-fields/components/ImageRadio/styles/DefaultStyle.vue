<template>
  <div class="ccb-default-radio-image">
    <div
      v-for="(option, idx) in options"
      :key="idx"
      class="ccb-radio-image"
      @click="() => addOrRemove(option)"
      :class="{
        selected: idx === getCurrentIndex,
        'ccb-option-disabled': field.disableOptions.includes(idx),
      }"
    >
      <div class="ccb-radio-image__box">
        <img :src="option.src" :alt="'radio-image-field-' + idx" />
      </div>
      <div class="ccb-radio-image__info">
        <div class="ccb-radio-image__title-box">
          <span class="ccb-radio-image__label">{{ option.optionText }}</span>
          <span class="ccb-radio-image__price" v-if="showValueInOption"
            >{{ translationsStore.getTranslations.price }}:
            {{ option.optionConverted }}</span
          >
        </div>
      </div>
      <label>
        <input
          v-model="optionValues"
          type="radio"
          :name="getName"
          :value="option.optionValue"
        />
      </label>
    </div>
  </div>
</template>

<script setup lang="ts">
import { IOptions, ISingleOptionsField } from "@/widget/shared/types/fields";
import { toRefs } from "vue";
import { useSingleOptionChildShared } from "@/widget/actions/fields/composable/useSingleOptionChildShared.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const emit = defineEmits<{
  (event: "update", option: IOptions): void;
}>();

type Props = {
  options: IOptions[];
  alias: string;
  current?: IOptions;
  repeater?: number;
  showValueInOption?: boolean;
  field: ISingleOptionsField;
};

const props = defineProps<Props>();
const { options, field } = toRefs(props);

const translationsStore = useTranslationsStore();

const { optionValues, changeValue, getName, current, getCurrentIndex } =
  useSingleOptionChildShared(props, emit);

const addOrRemove = (option: IOptions): void => {
  if (option && current?.value?.optionValue !== option.optionValue) {
    optionValues.value = option.optionValue;
    changeValue();
  }
};
</script>

<style scoped lang="scss">
.ccb-default-radio-image {
  display: flex;
  flex-wrap: wrap;
  gap: 9px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  flex-direction: flex-start;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  .ccb-radio-image {
    border: 2px solid var(--ccb-fields-border-color);
    display: flex;
    flex-direction: column;
    position: relative;
    border-radius: 4px;
    cursor: pointer;
    max-width: unset;
    width: 48%;

    &.selected {
      border-color: var(--ccb-accent-color);
    }

    &__box {
      padding: 18px;
      line-height: 0.7;
      display: flex;
      flex: 1;
      width: 100%;
      height: 100%;
      box-sizing: border-box;

      img {
        width: 100%;
        height: auto;
        border-radius: 4px;
        object-fit: cover;
        aspect-ratio: 1 / 1;
      }
    }

    &__info {
      width: 90%;
      padding: 5px 10px 12px 10px;
      position: relative;
    }

    &__title-box {
      display: flex;
      flex-direction: column;
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
      font-size: calc(var(--ccb-summary-text-size) - 2px);
      color: var(--ccb-fields-description-color);

      @media only screen and (max-width: 480px) {
        font-size: calc(var(--ccb-mobile-summary-text-size) - 2px);
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
    position: absolute;
    bottom: 10px;
    right: 2px;

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
      background-color: var(--ccb-fields-bg-color);
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
        background-color: var(--ccb-fields-bg-color) !important;
      }
    }

    .ccb-radio-label {
      word-break: break-word;
    }
  }
}

.ccb-vertical-radio {
  .ccb-default-radio-image {
    flex-direction: column;
    .ccb-radio-image {
      width: 100%;
    }
  }
}

.ccb-horizontal {
  .ccb_field_with_radio_with_img {
    grid-column: 1 / span 2;
    .ccb-radio-image {
      max-width: 200px !important;
      min-height: 220px;
    }
    .ccb-default-radio-image {
      flex-direction: row;
    }
  }
}
</style>
