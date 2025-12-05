<template>
  <div class="ccb-progress-with-bar" :ref="'progress-with-bar'">
    <div class="ccb-progress-with-bar__title">
      {{ calcTitle.length > 25 ? calcTitle.slice(0, 22) + "..." : calcTitle }}
    </div>
    <div class="ccb-progress-with-bar__bar">
      <div class="count">
        {{ translationsStore.getTranslations.step }} {{ step }}/{{ count }}
      </div>
      <div class="bar">
        <span :style="calculateProgressWith"></span>
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
  return (step.value / count.value) * 100;
});

const calculateProgressWith = computed(() => {
  return `width: ${progress.value}%`;
});
</script>

<style lang="scss">
.ccb-progress-with-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  font-style: normal;
  font-weight: 500;
  line-height: 20px;
  width: 100%;
  box-sizing: border-box;

  &__title {
    width: 50%;
    color: var(--ccb-fields-description-color);
  }

  &__bar {
    display: flex;
    flex-direction: column;
    max-width: 270px;
    width: 50%;
    color: var(--ccb-fields-description-color);

    .count {
      margin-bottom: 4px;
    }

    .bar {
      width: 100%;
      height: 6px;
      border-radius: 4px;
      background: var(--ccb-fields-border-color);

      span {
        display: block;
        background-color: var(--ccb-accent-color);
        height: 100%;
        width: 50%;
        border-radius: 4px;
        transition: width 0.5s ease-in-out;
      }
    }
  }
}
</style>
