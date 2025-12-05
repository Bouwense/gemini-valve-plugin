<template>
  <div class="slider-wrapper ccb-input-range-field">
    <div class="slider-input">
      <input
        type="number"
        v-model="realValue"
        :min="min"
        :max="max"
        @change="minValueInput"
      />
    </div>
    <Slider
      v-model="realValue"
      :min="min"
      :max="max"
      :step="step"
      :disabled="field.disabled"
      show-tooltip="focus"
      class="custom-slider"
      :format="getFormatValue"
    />
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, watch, ref } from "vue";
import Slider from "@vueform/slider";
import { IRangeField } from "@/widget/shared/types/fields";

type Props = {
  field: IRangeField;
  modelValue: number;
};

const props = defineProps<Props>();
const emit = defineEmits<{
  (event: "update:modelValue", value: number): void;
}>();

const realValue = ref(props.modelValue);

const { field } = toRefs(props);

const min = computed(() => Number(field.value.min));
const max = computed(() => Number(field.value.max));
const step = computed(() =>
  field.value.jump ? 1 : Number(field.value.step || 1),
);

const minValueInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const value = Number(target.value);
  if (value < min.value) {
    target.value = min.value.toString();
    realValue.value = min.value;
  }

  if (value > max.value) {
    target.value = max.value.toString();
    realValue.value = max.value;
  }
};

const getFormatValue = (num: number): string => {
  const rounded = num.toFixed(2);
  return parseFloat(rounded).toString();
};

watch(
  () => props.modelValue,
  (val) => {
    realValue.value = val;
  },
);

watch(realValue, (val) => {
  emit("update:modelValue", val);
});
</script>

<style lang="scss">
.ccb-input-range-field {
  position: relative;
  padding: 6px 8px 10px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;

  .slider-base {
    height: 8px;
  }

  .slider-tooltip {
    margin-bottom: 16px !important;
  }

  .slider-handle {
    width: 32px !important;
    height: 32px !important;
    top: calc(
      (var(--slider-handle-height, 16px) - var(--slider-height, 6px)) / 2 * -1 +
        -8px
    ) !important;
    background-color: #fff;
    box-shadow: 0px 5px 30px 0px rgba(0, 0, 0, 0.1);
  }

  .slider-handle {
    z-index: 1;

    &.slider-active {
      border: 1px solid var(--ccb-fields-border-color);
    }
  }

  .custom-slider {
    flex: 1;
    min-width: 0;
    padding-left: 26px;
  }

  .slider-input {
    width: 55px !important;
    flex-shrink: 0;
    input {
      box-sizing: border-box;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      border: none;
      outline: none;
      background: none;
      padding: 0;
      margin: 0;
      color: var(--ccb-text-color);

      border-radius: 4px;
      border: 1px solid var(--ccb-fields-border-color);
      width: 55px !important;
      padding: 10px;

      &::-webkit-outer-spin-button,
      &::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

      &[type="number"] {
        -moz-appearance: textfield;
      }
    }
  }
}
</style>
