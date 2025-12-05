<template>
  <div class="ccb-default-radio">
    <label
      v-for="(option, idx) in options"
      :class="{
        'ccb-option-disabled': field.disableOptions.includes(idx),
      }"
    >
      <input
        type="radio"
        :name="getName"
        :value="option.optionValue"
        v-model="optionValues"
        @change="changeValue"
      />
      <span class="ccb-radio-label">{{ option.optionText }}</span>
    </label>
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
import { IOptions, ISingleOptionsField } from "@/widget/shared/types/fields";
import { useSingleOptionChildShared } from "@/widget/actions/fields/composable/useSingleOptionChildShared.ts";

const emit = defineEmits<{
  (event: "update", option: IOptions): void;
}>();

type Props = {
  options: IOptions[];
  alias: string;
  current?: IOptions;
  repeater?: number;
  field: ISingleOptionsField;
};

const props = defineProps<Props>();
const { options, field } = toRefs(props);

const { optionValues, changeValue, getName } = useSingleOptionChildShared(
  props,
  emit,
);
</script>

<style lang="scss" scoped>
.ccb-default-radio {
  display: flex;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  padding: 5px 0px;
  flex-direction: flex-start;
  flex-wrap: wrap;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  &.ccb-vertical-radio {
    flex-direction: column;
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
        content: "" !important;
        width: 10px !important;
        min-width: 10px !important;
        height: 10px !important;
        display: block !important;
        margin: 0 !important;
        border-radius: 50% !important;
        background-color: var(--ccb-fields-bg-color) !important;
        line-height: unset !important;
      }
    }

    .ccb-radio-label {
      word-break: break-word;
    }
  }
}

.ccb-vertical-radio {
  .ccb-default-radio {
    flex-direction: column;
  }
}

.ccb-horizontal {
  .ccb-default-radio {
    flex-direction: row;
  }
}
</style>
