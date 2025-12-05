<template>
  <div class="ccb-box-toggle">
    <label
      class="ccb-toggle-item"
      v-for="(option, idx) in field.options"
      :key="idx"
      :class="{
        active: selectedOptions.includes(option.optionValue),
        'ccb-option-disabled': field.disableOptions.includes(idx),
      }"
      @click.prevent="toggleOption(option.optionValue)"
    >
      <div class="ccb-toggle-wrapper">
        <input
          :id="getName + '_' + idx + '_' + generateId"
          type="checkbox"
          :name="getName"
          v-model="optionValues"
          :value="option.optionValue"
          @click="toggleOption(option.optionValue)"
          :key="idx"
        />
        <label :for="getName + '_' + idx + '_' + generateId"></label>
      </div>
      <div class="ccb-toggle-item__label-wrap">
        <span class="ccb-toggle-item__label">{{ option.optionText }}</span>
        <span
          class="ccb-toggle-item__description"
          v-html="option.optionHint"
        ></span>
      </div>
    </label>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IMultiOptionsField } from "@/widget/shared/types/fields";
import { useMultiOptionChildShared } from "@/widget/actions/fields/composable/useMultiOptionChildShared";

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

const { optionValues, changeValue, getName, selectedOptions } =
  useMultiOptionChildShared(props, emit);

const toggleOption = (value: string) => {
  const index = optionValues.value.indexOf(value);

  if (index === -1) {
    optionValues.value.push(value);
  } else {
    optionValues.value.splice(index, 1);
  }
  changeValue();
};
</script>

<style lang="scss" scoped>
.ccb-box-toggle {
  display: flex;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  padding: 5px 0px;
  flex-direction: column;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  .ccb-toggle-item {
    display: flex;
    vertical-align: middle;
    align-items: center;
    border: 1px solid var(--ccb-fields-border-color);
    padding: 15px;
    border-radius: var(--ccb-fields-border-radius);
    background: var(--ccb-fields-color);
    cursor: pointer;

    &.active {
      border-color: var(--ccb-accent-color);
      background-color: color-mix(
        in srgb,
        var(--ccb-accent-color),
        transparent 75%
      );
    }

    &__postfix {
      color: var(--ccb-toggle-label-bg);
      cursor: pointer;
    }

    &__label-wrap {
      display: flex;
      flex-direction: column;
    }

    .ccb-toggle-wrapper {
      margin: 0;
      margin-right: 10px;
      position: relative;
      display: flex;
      align-items: center;

      input {
        display: none;
      }

      label {
        cursor: pointer;
        width: 45px;
        height: 24px;
        background: var(--ccb-toggle-label-bg);
        position: relative;
        border-radius: 46px;
        display: inline-block;
        margin: 0;
        padding: 0;

        &:after {
          content: "";
          background: #fff;
          top: 3px;
          width: 18px;
          height: 18px;
          position: absolute;
          border-radius: 100%;
          left: 3px;
          z-index: 2;
          box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
          transition: 0.4s;
        }
      }

      input:checked + label:after {
        left: 24px;
      }

      input:checked + label {
        background: var(--ccb-accent-color);
      }
    }
  }
}
</style>
