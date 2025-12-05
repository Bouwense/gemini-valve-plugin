<template>
  <div class="ccb-summary-item">
    <div class="ccb-summary-item__title">
      <div class="ccb-summary-title">{{ summary.label }}</div>
      <template
        v-if="
          ['checkbox', 'toggle', 'checkbox_with_img'].includes(
            summary.fieldName,
          )
        "
      >
        <div
          class="description"
          v-if="
            'selectedOption' in summary && Array.isArray(summary.selectedOption)
          "
          v-for="selected in summary.selectedOption || []"
          style="margin-top: 5px"
        >
          <div
            class="ccb-summary-item__options"
            v-if="summary.summaryView === 'show_value'"
          >
            <span>{{ selected?.optionText }}</span>
            <span>{{ selected?.optionConverted }}</span>
          </div>
        </div>
      </template>
      <template
        v-if="
          ['dropDown', 'dropDown_with_img', 'radio', 'radio_with_img'].includes(
            summary.fieldName,
          )
        "
      >
        <div
          class="description"
          v-if="
            'selectedOption' in summary &&
            summary.selectedOption &&
            'optionText' in summary.selectedOption
          "
          style="margin-top: 5px"
        >
          <div
            class="ccb-summary-item__options"
            v-if="summary.summaryView === 'show_value'"
          >
            <span>{{ summary?.selectedOption?.optionText }}</span>
          </div>
        </div>
      </template>
      <template
        v-else-if="
          ['file_upload', 'range', 'multi_range', 'quantity'].includes(
            summary.fieldName,
          )
        "
      >
        <div
          class="description"
          style="margin-top: 5px"
          v-if="'extraDisplayView' in summary && summary.extraDisplayView"
        >
          {{ summary.extraDisplayView }}
        </div>
      </template>
      <template
        v-else-if="['datePicker', 'geolocation'].includes(summary.fieldName)"
      >
        <div
          class="description"
          style="margin-top: 5px"
          v-if="
            'extraDisplayView' in summary &&
            Array.isArray(summary.extraDisplayView)
          "
          v-for="s in summary.extraDisplayView"
        >
          {{ s }}
        </div>
      </template>
    </div>
    <div
      class="ccb-summary-item__values"
      v-if="Array.isArray(summary.displayValue)"
    >
      <ul>
        <li v-for="option in summary.displayValue" :key="option">
          {{ option }}
        </li>
      </ul>
    </div>
    <div class="ccb-summary-item__value" v-else>
      {{ summary.displayValue }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
import { Field } from "@/widget/shared/types/fields";

type Props = {
  summary: Field;
};

const props = defineProps<Props>();
const { summary } = toRefs(props);
</script>

<style lang="scss">
.ccb-summary-item {
  padding-bottom: 5px;
  display: grid;
  grid-template-columns: 1fr auto;
  align-items: baseline;
  color: var(--ccb-text-color);
  font-size: var(--ccb-summary-text-size);
  font-weight: var(--ccb-summary-text-weight);
  text-transform: var(--ccb-summary-text-transform);
  border-bottom: 1px dashed #ddd;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-summary-text-size);
    font-weight: var(--ccb-mobile-summary-text-weight);
    text-transform: var(--ccb-mobile-summary-text-transform);
  }

  &__values {
    text-transform: none;
    ul {
      padding: 0;
      margin: 0;
      list-style: none;
      font-size: var(--ccb-summary-text-size);
      font-weight: var(--ccb-summary-text-weight);
      text-transform: var(--ccb-summary-text-transform);

      @media only screen and (max-width: 480px) {
        font-size: var(--ccb-mobile-summary-text-size);
        font-weight: var(--ccb-mobile-summary-text-weight);
      }

      li {
        text-transform: none;
      }
    }
  }

  &__value {
    text-transform: none;
  }

  &__title {
    .description {
      font-size: 0.9em;
      padding-left: 10px;
      color: var(--ccb-summary-description-color);
      text-transform: none;
      text-align: left;
    }
  }
}

.ccb-summary-item__options {
  display: flex;
  flex-wrap: nowrap;
  column-gap: 7px;
  text-transform: none;
}
</style>
