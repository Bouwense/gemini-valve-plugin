<template>
  <div class="slider-wrapper ccb-input-multi-range-field">
    <div class="slider-input">
      <input
        type="number"
        v-model="realValue[0]"
        :min="min"
        :max="max"
        @change="minValueInput"
      />
    </div>
    <Slider
      style="margin: 10px 0"
      :format="getFormatValue"
      :min="field.min"
      :max="field.max"
      :step="step"
      :disabled="field.disabled"
      :modelValue="modelValue"
      @update:modelValue="updateValue"
      show-tooltip="focus"
    ></Slider>
    <div class="slider-input">
      <input
        type="number"
        v-model="realValue[1]"
        :min="min"
        :max="max"
        @change="maxValueInput"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, ref, computed, watch } from "vue";
import Slider from "@vueform/slider";
import { IMultiRangeField } from "@/widget/shared/types/fields";

type Props = {
  field: IMultiRangeField;
  modelValue: number[];
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const step = computed(() =>
  field.value.jump ? 1 : Number(field.value.step || 1),
);
const min = computed(() => Number(field.value.min));
const max = computed(() => Number(field.value.max));
const realValue = ref(props.modelValue);

const emit = defineEmits<{
  (event: "update:modelValue", value: number[]): void;
}>();

const updateValue = (val: number | number[]) => {
  emit("update:modelValue", Array.isArray(val) ? val : [val]);
};

const minValueInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const value = Number(target.value);
  if (value < min.value) {
    target.value = min.value.toString();
    realValue.value[0] = min.value;
  }
};

const maxValueInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const value = Number(target.value);
  if (value > max.value) {
    target.value = max.value.toString();
    realValue.value[1] = max.value;
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

watch(
  realValue,
  (val) => {
    emit("update:modelValue", val);
  },
  { deep: true },
);
</script>

<style lang="scss">
.ccb-input-multi-range-field {
  position: relative;
  padding: 6px 0 10px 0;
  display: flex;
  justify-content: space-between;
  align-items: center;

  .slider-origin {
    width: 95%;
  }

  .slider-base {
    height: 8px;
  }

  .slider-handle {
    width: 32px !important;
    height: 32px !important;
    top: calc(
      (var(--slider-handle-height, 16px) - var(--slider-height, 6px)) / 2 * -1 +
        -8px
    ) !important;
    background-color: #fff !important;
    box-shadow: 0px 5px 30px 0px rgba(0, 0, 0, 0.1);

    &.slider-active {
      border: 1px solid var(--ccb-fields-border-color);
    }
  }

  .slider-target {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 1;
    padding: 0 20px;
    width: 100%;
  }

  .slider-tooltip {
    bottom: 40px;
  }

  .slider-input {
    width: 55px !important;
    input {
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
      box-sizing: border-box;

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
