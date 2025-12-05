<template>
  <div class="ccb-box-radio">
    <label
      class="ccb-radio-label"
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
.ccb-vertical-radio {
  .ccb-box-radio {
    flex-direction: column;
  }
}

.ccb-box-radio {
  display: flex;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  padding: 5px 0;
  flex-wrap: wrap;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  &.ccb-vertical-radio {
    flex-direction: column;
  }

  label {
    margin: 0;
    padding: 12px 30px;
    background: var(--ccb-container-color);
    border: 1px solid var(--ccb-fields-border-color);
    border-radius: var(--ccb-fields-border-radius);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.25;

    input {
      display: none;
    }
  }

  label:has(input[type="radio"]:checked) {
    background: var(--ccb-accent-color);
    color: #fff;
  }
}
</style>
