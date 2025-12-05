<template>
  <div class="ccb-rectangle-tab" :class="{ 'hide-title': hideTitle }">
    <div class="ccb-calc-title">
      {{ calcTitle }}
    </div>
    <div class="tabs">
      <div
        class="tab"
        :class="[{ active: step === page.id }, { done: step > page.id }]"
        v-for="page in pages"
        :key="page.id"
        :ref="'tab-' + page.id"
      >
        <span class="tab__item" :id="page.id.toString()">
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
import { toRefs, defineProps } from "vue";

type Page = {
  id: number;
  label: string;
  title: string;
};

type Props = {
  pages: Page[];
  step: number;
  hideTitle: boolean;
  calcTitle: string;
};

const props = defineProps<Props>();

const { pages, step, hideTitle, calcTitle } = toRefs(props);
</script>

<style lang="scss">
.ccb-rectangle-tab {
  overflow-x: auto;

  .ccb-calc-title {
    top: -40px;
    left: 0px;
  }

  .tabs {
    display: flex;
    justify-content: flex-start;
    width: max-content;
  }

  .tab {
    margin: 0 10px;
    display: flex;
    align-items: center;

    &.done {
      .complete {
        display: flex;
        color: var(--ccb-accent-color);
      }

      .tab__item {
        background: var(--ccb-page-done-bg-color);
      }

      .tab__title {
        color: var(--ccb-text-color);
        opacity: 1;
      }

      .count {
        display: none;
      }
    }

    &.active {
      .tab__item {
        background: var(--ccb-accent-color);
        color: var(--ccb-container-color);
      }
      .tab__title {
        font-weight: 600;
        opacity: 1;
      }
    }

    &__item {
      margin-right: 6px;
      width: 36px;
      height: 36px;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-shrink: 0;
      max-width: 140px;
      border-radius: 6px;
      background-color: var(--ccb-fields-bg-color);
      color: var(--ccb-fields-description-color);

      .complete {
        display: none;
      }
    }

    &__title {
      font-size: 13px;
      font-style: normal;
      font-weight: 500;
      line-height: 16px;
      opacity: 0.5;
    }
  }

  &.hide-title {
    .tab__title {
      display: none !important;
    }
  }
}
</style>
