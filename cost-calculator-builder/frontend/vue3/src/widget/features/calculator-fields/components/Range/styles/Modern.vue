<template>
  <div
    class="slider-wrapper ccb-modern-range-field"
    :class="{ jump: field.jump }"
  >
    <div class="slider-min-max">
      <div class="slider-min-max-item first" @click="handleClick(min)">
        <span class="label">{{ min }}</span>
      </div>
      <div
        class="slider-min-max-item"
        :class="{ done: value >= max }"
        @click="handleClick(max)"
      >
        <span class="label">{{ max }}</span>
      </div>
    </div>
    <Slider
      v-model="value"
      :min="min"
      :max="max"
      :step="step"
      :disabled="field.disabled"
      show-tooltip="focus"
      class="custom-slider"
      :format="getFormatValue"
    />
    <div class="custom-ticks">
      <div v-for="tick in 25" :key="tick" class="tick" />
    </div>
    <div class="custom-marks">
      <div class="stick-line">
        <div class="stick-line-item"></div>
      </div>
      <div
        v-for="point in scalePoints"
        :key="point"
        class="mark"
        :style="getMarkStyle(point)"
        v-if="field.scalePoints"
      >
        <div
          class="label"
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

const value = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val),
});

const min = computed(() => Number(field.value.min));
const max = computed(() => Number(field.value.max));
const step = computed(() =>
  field.value.jump ? 1 : Number(field.value.step || 1),
);
const jump = computed(() => field.value.jump);

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

const handleClick = (point: number) => {
  if (jump.value && scalePoints.value.includes(point)) {
    emit("update:modelValue", point);
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
  if (jump.value) {
    const closestPoint = scalePoints.value.reduce(
      (prev, curr) =>
        Math.abs(curr - val) < Math.abs(prev - val) ? curr : prev,
      scalePoints.value[0],
    );
    emit("update:modelValue", closestPoint);
  } else {
    emit("update:modelValue", val);
  }
});
</script>

<style lang="scss">
.ccb-modern-range-field {
  padding: 10px 3px 40px 3px;
  position: relative;

  .slider-origin {
    width: 96%;
    right: 8px;
  }

  .slider-min-max {
    display: flex;
    width: 100%;
    justify-content: space-between;
    align-items: center;
    position: absolute;
    top: 33px;

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
      padding-top: 8px;
      pointer-events: none;
    }
  }

  &.jump {
    .slider-handle {
      pointer-events: none;
    }

    .slider-min-max-item {
      .label {
        cursor: pointer;
        pointer-events: auto;
      }
    }

    .custom-marks {
      .label {
        cursor: pointer;
        pointer-events: auto;
      }
    }
  }

  .slider-handle {
    z-index: 1;

    &.slider-active {
      border: 1px solid var(--ccb-fields-border-color);
    }
  }

  .slider-base {
    height: 20px;
    width: 100%;
  }

  .slider-tooltip {
    margin-bottom: 16px !important;
  }

  .slider-handle {
    background-color: #fff !important;
    height: 31px !important;
    width: 28px !important;
    border-radius: 4px;
    box-shadow: 0px 5px 30px 0px rgba(0, 0, 0, 0.1);
    transform: translateX(7px);
  }

  .slider-connect {
    background: linear-gradient(
      90deg,
      rgba(238, 241, 247, 0) 0%,
      var(--ccb-accent-color) 100%
    ) !important;
  }

  .custom-marks {
    position: absolute;
    right: 8px;
    top: 30px;
    width: 96%;
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
      pointer-events: none;
    }
  }

  .custom-ticks {
    width: 98%;
    height: 0;
    pointer-events: none;
    display: flex;
    justify-content: space-between;
    top: 10px;
    position: absolute;
    padding: 0 10px;
    box-sizing: border-box;
  }

  .tick {
    width: 1px;
    height: 20px;
    background: rgba(0, 0, 0, 0.04);
    transform: translateX(-50%);
  }
}
</style>
