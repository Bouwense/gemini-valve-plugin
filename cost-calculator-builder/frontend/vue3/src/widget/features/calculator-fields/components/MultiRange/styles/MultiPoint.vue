<template>
  <div
    class="slider-wrapper ccb-multi-point-multi-range-field"
    :class="{ jump: field.jump }"
  >
    <div class="slider-min-max">
      <div
        class="slider-min-max-item"
        :class="{ first: modelValue[0] == min }"
        @click="handleMinClick(min)"
      >
        <span class="tick"></span>
        <span class="label">{{ min }}</span>
      </div>
      <div
        class="slider-min-max-item"
        :class="{ done: modelValue[0] >= max }"
        @click="handleClick(max)"
      >
        <span class="tick"></span>
        <span class="label">{{ max }}</span>
      </div>
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
    <div class="custom-marks">
      <div
        v-for="point in scalePoints"
        :key="point"
        class="mark"
        :style="getMarkStyle(point)"
        v-if="field.scalePoints"
      >
        <div
          class="tick"
          v-if="point !== min && point !== max"
          :class="{
            'tick-passed': point >= modelValue[0] && point <= modelValue[1],
          }"
        />
        <div
          class="label"
          :class="{ 'label-passed': point <= modelValue[0] }"
          @click="handleClick(point)"
          v-if="point !== min && point !== max"
        >
          {{ point }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, ref, computed, watch } from "vue";
import { IMultiRangeField } from "@/widget/shared/types/fields";
import Slider from "@vueform/slider";

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
const jump = computed(() => field.value.jump);

const emit = defineEmits<{
  (event: "update:modelValue", value: number[]): void;
}>();

const updateValue = (val: number | number[]) => {
  emit("update:modelValue", Array.isArray(val) ? val : [val]);
};

const getFormatValue = (num: number): string => {
  const rounded = num.toFixed(2);
  return parseFloat(rounded).toString();
};

const scalePoints = computed(() => {
  const raw = field.value.scalePoints?.trim() || "";
  return raw
    .split(",")
    .map((p) => Number(p.trim()))
    .filter((p) => !isNaN(p) && p >= min.value && p <= max.value);
});

const getMarkStyle = (point: number) => {
  const percentage = ((point - min.value) / (max.value - min.value)) * 100;
  return {
    left: `${percentage}%`,
  };
};

watch(
  () => props.modelValue,
  (val) => {
    realValue.value = val;
  },
);

const handleClick = (point: number) => {
  if (jump.value && scalePoints.value.includes(point)) {
    emit("update:modelValue", [realValue.value[0], point]);
  }
};

const handleMinClick = (point: number) => {
  if (jump.value && scalePoints.value.includes(point)) {
    emit("update:modelValue", [point, realValue.value[1]]);
  }
};

watch(realValue, (val) => {
  if (jump.value) {
    const firstPoint = scalePoints.value.reduce(
      (prev, curr) =>
        Math.abs(curr - val[0]) < Math.abs(prev - val[0]) ? curr : prev,
      scalePoints.value[0],
    );
    const secondPoint = scalePoints.value.reduce(
      (prev, curr) =>
        Math.abs(curr - val[1]) < Math.abs(prev - val[1]) ? curr : prev,
      scalePoints.value[0],
    );
    const snapped = [firstPoint, secondPoint];

    if (JSON.stringify(snapped) !== JSON.stringify(props.modelValue)) {
      emit("update:modelValue", snapped);
    }
  } else {
    if (JSON.stringify(val) !== JSON.stringify(props.modelValue)) {
      emit("update:modelValue", val);
    }
  }
});
</script>

<style lang="scss">
.ccb-multi-point-multi-range-field {
  padding: 10px 0 25px 0 !important;
  position: relative;

  &.jump {
    .custom-marks {
      z-index: 2;
    }

    .slider-min-max-item {
      .label {
        cursor: pointer;
        pointer-events: auto;
      }
    }

    .slider-handle {
      pointer-events: none;
    }

    .custom-marks {
      .label {
        cursor: pointer;
        pointer-events: auto;
      }
    }
  }
  .slider-min-max {
    display: flex;
    width: 102%;
    justify-content: space-between;
    align-items: center;
    position: absolute;
    top: 18px;
    left: 0;

    &-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      &.first {
        .tick {
          background: var(--ccb-accent-color);
        }
      }

      &.done {
        .tick {
          background-color: var(--ccb-accent-color);
        }
      }
    }

    .tick {
      height: 8px;
      width: 8px;
      border-radius: 50%;
      background: color-mix(in srgb, var(--ccb-container-color), black 10%);
      margin: 0 auto 4px;
    }

    .label {
      color: var(--ccb-fields-description-color);
      font-size: 12px;
      font-weight: 500;
      line-height: 100%;
      padding-top: 10px;
    }
  }

  .slider-origin {
    width: 105%;
    right: -13px;
  }

  .slider-base {
    transform: scale(0.94);
    background-color: color-mix(
      in srgb,
      var(--ccb-container-color),
      black 10%
    ) !important;
  }
  .slider-horizontal {
    height: 4px !important;
    border-radius: 10px;
  }

  .slider-tooltip {
    border-radius: 15px;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 10px;
    line-height: 100%;

    &::after,
    &::before {
      display: none;
    }
  }

  .custom-marks {
    position: absolute;
    right: -13px;
    top: 18px;
    width: 105%;
    transform: scaleX(0.94);
  }

  .mark {
    position: absolute;
    transform: translateX(-50%);
    text-align: center;

    .label {
      color: var(--ccb-fields-description-color);
      font-size: 12px;
      font-weight: 500;
      line-height: 100%;
      padding-top: 10px;
    }
  }

  .tick {
    height: 8px;
    width: 8px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--ccb-container-color), black 10%);
    margin: 0 auto 4px;

    &.tick-passed {
      background: var(--ccb-accent-color) !important;
    }
  }
}
</style>
