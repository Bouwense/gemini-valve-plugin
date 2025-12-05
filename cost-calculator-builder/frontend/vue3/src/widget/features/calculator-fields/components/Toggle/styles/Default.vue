<template>
  <div class="ccb-default-toggle">
    <div
      class="ccb-toggle-item"
      v-for="(option, idx) in field.options"
      :key="idx"
      :class="{
        'ccb-option-disabled': field.disableOptions.includes(idx),
      }"
    >
      <div class="ccb-toggle-item__label-wrap">
        <span class="ccb-toggle-item__label">{{ option.optionText }}</span>
      </div>
      <div class="ccb-toggle-item__postfix" v-if="option.optionHint">
        <Hint>
          <template #content>
            <span v-html="option.optionHint"></span>
          </template>
        </Hint>
      </div>
      <div class="ccb-toggle-wrapper">
        <input
          :id="getName + '_' + idx + '_' + generateId"
          type="checkbox"
          :name="getName"
          v-model="optionValues"
          :value="option.optionValue"
          @change="changeValue"
          :key="idx"
        />
        <label :for="getName + '_' + idx + '_' + generateId"></label>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IMultiOptionsField } from "@/widget/shared/types/fields";
import { useMultiOptionChildShared } from "@/widget/actions/fields/composable/useMultiOptionChildShared.ts";
import Hint from "@/widget/shared/ui/components/Hint";

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
.ccb-default-toggle {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  padding: 5px 0px;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  .ccb-toggle-item {
    display: flex;
    padding: 0;
    vertical-align: middle;
    align-items: center;

    &__postfix {
      color: var(--ccb-toggle-label-bg);
      cursor: pointer;
    }

    &__label-wrap {
      display: flex;
      align-items: center;
    }

    .ccb-toggle-wrapper {
      margin: 0;
      margin-left: 10px;
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
          background: var(--ccb-fields-bg-color);
          top: 3px;
          width: 18px;
          height: 18px;
          position: absolute;
          border-radius: 100%;
          left: 3px;
          z-index: 2;
          box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
          transition: left 0.3s ease !important;
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

.ccb-vertical-toggle {
  .ccb-default-toggle {
    flex-direction: column;
    .ccb-toggle-item {
      width: 100%;
    }
  }
}
</style>
