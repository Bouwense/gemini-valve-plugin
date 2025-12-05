<template>
  <div
    class="slider-wrapper ccb-multi-point-range-field"
    :class="{ jump: field.jump }"
  >
    <div class="slider-min-max">
      <div class="slider-min-max-item first" @click="handleClick(min)">
        <span class="tick"></span>
        <span class="label">{{ min }}</span>
      </div>
      <div
        class="slider-min-max-item"
        :class="{ done: value >= max }"
        @click="handleClick(max)"
      >
        <span class="tick"></span>
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
          :class="{ 'tick-passed': point <= Number(DinamicLabel) }"
        />
        <div
          class="label"
          :class="{ 'label-passed': point <= Number(DinamicLabel) }"
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
import { toRefs, computed, watch, ref, onBeforeUnmount, onMounted } from "vue";
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

const handleClick = (point: number) => {
  if (jump.value && scalePoints.value.includes(point)) {
    DinamicLabel.value = point.toString();
    emit("update:modelValue", point);
  }
};

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
    DinamicLabel.value = val.toString();
  },
);

const getFormatValue = (num: number): string => {
  const rounded = num.toFixed(2);
  return parseFloat(rounded).toString();
};

let observer: MutationObserver;

let DinamicLabel = ref("0");

onMounted(() => {
  if (field.value.defaultValue) {
    DinamicLabel.value = field.value.defaultValue.toString();
  }

  const tooltipEl = document.querySelector(
    `[data-id="${field.value.alias}"] .slider-tooltip`,
  );

  if (tooltipEl) {
    observer = new MutationObserver((mutations) => {
      for (const mutation of mutations) {
        if (mutation.type === "childList") {
          setTimeout(() => {
            DinamicLabel.value = tooltipEl.textContent ?? "";
          }, 130);
        }
      }
    });

    observer.observe(tooltipEl, {
      childList: true,
      subtree: true,
      characterData: true,
    });
  }
});

onBeforeUnmount(() => {
  if (observer) observer.disconnect();
});

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
.ccb-multi-point-range-field {
  position: relative;
  padding: 15px 0px 35px 0px;

  &.jump {
    .custom-marks {
      z-index: 2;
    }

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

  .slider-min-max {
    display: flex;
    width: 102%;
    justify-content: space-between;
    align-items: center;
    position: absolute;
    top: 13px;
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
      pointer-events: none;
    }
  }

  .custom-marks {
    position: absolute;
    right: -13px;
    top: 13px;
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
}
</style>
