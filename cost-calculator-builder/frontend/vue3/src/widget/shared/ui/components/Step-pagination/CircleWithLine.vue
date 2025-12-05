<template>
  <div class="ccb-circle-with-line" :class="{ 'hide-title': hideTitle }">
    <div class="ccb-calc-title">
      {{ calcTitle }}
    </div>
    <div class="tabs">
      <span
        class="bar-progress"
        :style="{ width: progressBarWidth + 'px' }"
      ></span>
      <div class="bar-overlay" :style="{ width: overLayBarWidth + 'px' }"></div>
      <div
        class="tab"
        :class="[{ active: step === page.id }, { done: step > page.id }]"
        v-for="page in pages"
        :key="page.id"
        :ref="'tab-' + page.id"
      >
        <span class="tab__item" :id="String(page.id)">
          <span class="count">{{ page.id }}</span>
          <span class="complete">
            <i class="ccb-icon-radius-check" style="font-size: 12px"></i>
          </span>
        </span>
        <span class="tab__title" :title="page.label">
          {{ page.title }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, toRefs, defineProps } from "vue";

type Page = {
  id: number;
  title: string;
  label: string;
};

type Props = {
  pages: Page[];
  step: number;
  hideTitle: boolean;
  calcTitle: string;
};

const props = defineProps<Props>();

const { pages, step, hideTitle } = toRefs(props);

const pageCount = computed(() => pages.value.length);

const progressBarWidth = computed(() => {
  const circleDiameter = 81;
  const circleMargin = 10;
  const totalWidth =
    (pageCount.value - 1) * (circleDiameter + 2 * circleMargin);
  const result =
    ((Number(step.value) - 1) / (Number(pageCount.value) - 1)) * totalWidth;

  if (result > overLayBarWidth.value) {
    return overLayBarWidth;
  } else {
    return result;
  }
});

const overLayBarWidth = computed(() => {
  const circleDiameter = 81;
  const circleMargin = 10;
  const totalWidth =
    (pageCount.value - 1) * (circleDiameter + 2 * circleMargin);
  return ((pageCount.value - 1) / (pageCount.value - 1)) * totalWidth;
});
</script>

<style lang="scss">
.ccb-circle-with-line {
  position: relative;

  .ccb-calc-title {
    top: -55px;
    left: -20px;
  }

  .tabs-wrapper {
    display: flex;
  }

  .tabs {
    display: flex;
    justify-content: flex-start;

    .tab {
      z-index: 9;
      flex-direction: column;
      min-width: 81px;

      &.done {
        .tab__item {
          background: var(--ccb-container-color);
          color: var(--ccb-accent-color);
          border-color: var(--ccb-accent-color);
        }

        .tab__title {
          color: var(--ccb-text-color);
          opacity: 1;
        }
      }

      .complete {
        display: none;
      }

      &.active {
        .tab__title {
          opacity: 1;
          color: var(--ccb-accent-color);
        }

        .tab__item {
          background-color: var(--ccb-accent-color);
          color: var(--ccb-fields-bg-color);
        }
      }
    }

    .tab__item {
      margin-right: 0px;
      border-radius: 50%;
      background-color: var(--ccb-container-color);
      border: 2px solid #0019311a;
      color: var(--ccb-fields-description-color);
    }

    .tab__title {
      margin-top: 10px;
      opacity: 0.5;
    }
  }

  .bar-overlay {
    position: absolute;
    left: 32px;
    top: 17px;
    z-index: 1;
    background: var(--ccb-fields-bg-color);
    height: 2px;
  }

  .bar-progress {
    position: absolute;
    left: 32px;
    top: 17px;
    z-index: 2;
    background: var(--ccb-accent-color);
    height: 2px;
    transition: width 0.5s;
  }
}
</style>
