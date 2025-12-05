<template>
  <div class="ccb-summary-repeater">
    <template v-for="(elements, idx) in summary.groupElements">
      <div
        class="ccb-summary-repeater__header"
        @click="collapseStore[idx] = !collapseStore[idx]"
        v-if="getFieldsFromMap(elements).length > 0"
      >
        <div
          class="ccb-summary-repeater__btn"
          :aria-expanded="!collapseStore[idx]"
          :class="{ rotated: collapseStore[idx] }"
        >
          <i class="ccb-icon-Path-3514"></i>
        </div>
        <div class="ccb-summary-repeater__title">{{ summary.label }}</div>
        <div class="ccb-summary-repeater__count">#{{ idx + 1 }}</div>
      </div>
      <div
        class="ccb-summary-repeater__list"
        :class="{ hidden: collapseStore[idx] }"
      >
        <template
          v-if="getFieldsFromMap(elements).length > 0"
          v-for="element in getFieldsFromMap(elements)"
        >
          <Transition name="fade">
            <TotalSummaryItem
              item-type="summary"
              :summary="element"
            ></TotalSummaryItem>
          </Transition>
        </template>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, toRefs } from "vue";
import { Field, IRepeaterField } from "@/widget/shared/types/fields";
import TotalSummaryItem from "@/widget/shared/ui/total-summary/TotalSummaryItem.vue";

type Props = {
  summary: IRepeaterField;
};

const props = defineProps<Props>();
const { summary } = toRefs(props);

const collapseStore = ref<Record<number, boolean>>({});

const getFieldsFromMap = computed(() => {
  return (data: Map<string, Field>): Field[] => {
    return Array.from(data.values()).filter((item: Field) => item.addToSummary);
  };
});
</script>

<style lang="scss">
.ccb-summary-repeater {
  padding-left: 15px;

  &__header {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 10px;
    cursor: pointer;

    &:not(:first-child) {
      margin-top: 12px;
    }

    .ccb-summary-repeater__btn {
      cursor: pointer;
      font-size: 10px;
      margin-right: 4px;
      color: var(--ccb-text-color);
      transition: transform 0.4s;

      &.rotated {
        transform: rotate(180deg);
      }
    }

    .ccb-summary-repeater__title {
      font-size: var(--ccb-repeater-title-font-size);
      font-weight: 700;
      margin-right: 4px;
      color: var(--ccb-text-color);
    }

    .ccb-summary-repeater__count {
      font-size: var(--ccb-repeater-title-font-size);
      font-weight: 600;
      color: var(--ccb-summary-description-color);
    }
  }

  &__list {
    padding: 0 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.4s;
    max-height: 2000px;
    overflow: hidden;

    &.hidden {
      max-height: 0;
    }
  }
}
</style>
