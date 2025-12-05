<template>
  <div class="ccb-box-checkbox">
    <template v-for="(option, idx) in field.options">
      <label
        :for="getName + '_' + idx"
        :class="{
          'ccb-option-disabled': field.disableOptions.includes(idx),
        }"
      >
        <input
          type="checkbox"
          :id="getName + '_' + idx"
          :name="getName"
          :value="option.optionValue"
          v-model="optionValues"
          @change="changeValue"
        />
        <span class="ccb-checkbox-label">{{ option.optionText }} </span>
      </label>
    </template>
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
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
    justify-content: center;
    align-items: center;
    margin: 0;
    cursor: pointer;
    line-height: 1.4;
    padding: 12px 30px;
    position: relative;
    background: var(--ccb-fields-bg-color);
    border: 1px solid var(--ccb-fields-border-color);
    border-radius: var(--ccb-fields-border-radius);

    input {
      display: none;
    }

    &::after,
    &:before {
      display: none !important;
    }
  }

  label:has(input[type="checkbox"]:checked) {
    background: var(--ccb-accent-color);
    border-color: var(--ccb-accent-color);
    color: #fff;
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
