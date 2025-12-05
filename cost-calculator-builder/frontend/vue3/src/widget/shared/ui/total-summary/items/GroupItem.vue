<template>
  <div class="ccb-summary-group">
    <template v-for="elements in summary.groupElements">
      <template v-for="element in getFieldsFromMap(elements)">
        <Transition name="fade">
          <TotalSummaryItem
            item-type="summary"
            :summary="element"
            v-if="!element.alias.includes('total')"
          ></TotalSummaryItem>
        </Transition>
      </template>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, toRefs } from "vue";
import { Field, IRepeaterField } from "@/widget/shared/types/fields";
import TotalSummaryItem from "@/widget/shared/ui/total-summary/TotalSummaryItem.vue";

type Props = {
  summary: IRepeaterField;
};

const props = defineProps<Props>();
const { summary } = toRefs(props);

const getFieldsFromMap = computed(() => {
  return (data: Map<string, Field>): Field[] => {
    return Array.from(data.values());
  };
});
</script>

<style lang="scss">
.ccb-summary-group {
  display: flex;
  flex-direction: column;
  gap: var(--ccb-summary-spacing);
  @media only screen and (max-width: 480px) {
    gap: var(--ccb-mobile-summary-spacing);
  }
}
</style>
