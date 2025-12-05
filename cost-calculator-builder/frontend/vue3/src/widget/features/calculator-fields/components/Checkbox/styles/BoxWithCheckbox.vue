<template>
  <div class="ccb-box-checkbox">
    <template v-for="(option, idx) in field.options">
      <label
        :for="getName + '_' + idx + '_' + generateId"
        :class="{
          'ccb-option-disabled': field.disableOptions.includes(idx),
        }"
      >
        <input
          type="checkbox"
          :id="getName + '_' + idx + '_' + generateId"
          :name="getName"
          :value="option.optionValue"
          v-model="optionValues"
          @change="changeValue"
        />
        <span class="ccb-checkbox-label">{{ option.optionText }}</span>
      </label>
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

const { optionValues, changeValue, getName } = useMultiOptionChildShared(
  props,
  emit,
);
</script>

<style lang="scss" scoped>
.ccb-box-checkbox {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  padding: 5px 0;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  label {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    margin: 0;
    cursor: pointer;
    line-height: 1.4;
    padding: 15px 15px 15px 45px;
    position: relative;
    background: var(--ccb-fields-bg-color);
    border: 1px solid var(--ccb-fields-border-color);
    border-radius: var(--ccb-fields-border-radius);

    &:before {
      left: 15px;
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
      left: 20px;
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
  }

  label:has(input[type="checkbox"]:checked) {
    background: var(--ccb-fields-bg-color-alpha);
    border-color: var(--ccb-accent-color);
  }

  label:has(input[type="checkbox"]:checked):before {
    background: var(--ccb-accent-color);
    border-color: var(--ccb-accent-color);
  }

  label:has(input[type="checkbox"]:checked):after {
    transform: rotate(-45deg) scale(1) translateY(-10%);
  }
}

.ccb-vertical-checkbox {
  .ccb-box-checkbox {
    flex-direction: column;
  }

  .ccb-checkbox-label {
    word-break: break-word;
  }
}
</style>
