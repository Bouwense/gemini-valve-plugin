<template>
  <div class="ccb-progress-with-circle" :ref="'progress-with-circle'">
    <div class="ccb-progress-with-circle__title">
      {{ calcTitle.length > 25 ? calcTitle.slice(0, 22) + "..." : calcTitle }}
    </div>
    <div class="ccb-progress-with-circle__circle">
      <div class="progress-ring">
        <svg>
          <circle class="background" cx="10" cy="10" r="8"></circle>
          <circle
            class="progress"
            cx="10"
            cy="10"
            r="8"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="offset"
          ></circle>
        </svg>
      </div>
      <div class="count">
        {{ translationsStore.getTranslations.step }} {{ step }}/{{ count }}
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { toRefs, defineProps, computed } from "vue";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

type Props = {
  pages: [];
  step: number;
  hideTitle: boolean;
  calcTitle: string;
};

const props = defineProps<Props>();
const translationsStore = useTranslationsStore();

const { pages, step, calcTitle } = toRefs(props);

const count = computed(() => pages.value.length);

const progress = computed(() => {
  return (Number(step.value) / Number(count.value)) * 100;
});

const radius = 8;
const circumference = 2 * Math.PI * radius;

const offset = computed(() => {
  return circumference - (progress.value / 100) * circumference;
});
</script>
<style lang="scss">
.ccb-progress-with-circle {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  font-style: normal;
  font-weight: 500;
  line-height: 20px;
  width: 100%;
  box-sizing: border-box;
  color: var(--ccb-text-color);

  &__title {
    width: 45%;
    color: var(--ccb-fields-description-color);
  }

  &__circle {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    width: 50%;

    .progress-ring {
      position: relative;
      width: 20px;
      height: 20px;
      margin-right: 8px;

      svg {
        width: 100%;
        height: 100%;
        transform: rotate(180deg);

        .background {
          stroke: var(--ccb-fields-border-color);
        }

        .progress {
          stroke: var(--ccb-accent-color);
          stroke-linecap: round;
          transition: stroke-dashoffset 0.35s;
          transform: rotate(90deg);
          transform-origin: 50% 50%;
        }

        circle {
          fill: transparent;
          stroke-width: 3;
        }
      }
    }

    .count {
      line-height: 1;
      color: var(--ccb-fields-description-color);
    }
  }
}
</style>
