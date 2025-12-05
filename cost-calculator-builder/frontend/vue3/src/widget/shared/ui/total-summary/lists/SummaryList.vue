<template>
  <div class="ccb-summary-list">
    <div class="ccb-summary-list__header">
      <HeaderTitle :title="settingsStore.getGeneralSettings?.headerTitle" />

      <div
        v-if="settingsStore.general?.descriptions"
        class="ccb-summary-list__accordion-btn"
        @click="toggleBody"
        :aria-expanded="isBodyVisible"
        :class="{ rotated: !isBodyVisible }"
      >
        <i class="ccb-icon-Path-3485"></i>
      </div>
    </div>
    <div
      class="ccb-summary-list__body"
      :class="{ hidden: !isBodyVisible }"
      v-if="settingsStore.general?.descriptions"
    >
      <div class="ccb-summary-list__titles">
        <div>{{ translationsStore.getTranslations.name }}</div>
        <div>{{ translationsStore.getTranslations.total }}</div>
      </div>
      <div
        class="ccb-summary-list__wrapper"
        :class="{
          'ccb-summary-list__wrapper--disable-option-unit': disableOptionUnit,
        }"
      >
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import HeaderTitle from "@/widget/shared/ui/wrappers/components/HeaderTitle.vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const settingsStore = useSettingsStore();
const translationsStore = useTranslationsStore();

const isBodyVisible = ref<boolean>(
  settingsStore.general?.showDetailsAccordion || false,
);

const disableOptionUnit = computed(() => {
  return !settingsStore.general?.showOptionUnit;
});

const toggleBody = () => {
  isBodyVisible.value = !isBodyVisible.value;
};
</script>

<style lang="scss">
.ccb-summary-list {
  &__header {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    margin-bottom: 20px;
  }

  &__body {
    display: grid;
    gap: 5px;
    transition: all 0.6s;
    max-height: 2000px;
    overflow: hidden;

    &.hidden {
      max-height: 0;
    }
  }

  &__wrapper {
    padding-top: 10px;
    display: grid;
    gap: var(--ccb-summary-spacing);
    @media only screen and (max-width: 480px) {
      gap: var(--ccb-mobile-summary-spacing);
    }

    &--disable-option-unit {
      .description {
        display: none;
      }
    }
  }

  &__titles {
    display: grid;
    grid-template-columns: 1fr auto;
    color: var(--ccb-text-color);
    font-size: var(--ccb-summary-header-size);
    font-weight: var(--ccb-summary-header-weight);
    background-color: var(--ccb-container-dark-color);
    padding: 2px 10px;
    border-radius: var(--ccb-fields-border-radius);
    text-transform: var(--ccb-summary-text-transform);
    text-align: left;

    @media only screen and (max-width: 480px) {
      font-size: var(--ccb-mobile-summary-header-size);
      font-weight: var(--ccb-mobile-summary-header-weight);
    }
  }

  .ccb-summary-list__accordion-btn {
    cursor: pointer;
    width: 20px;
    height: 20px;
    display: grid;
    place-items: center;
    background: var(--ccb-container-dark-color);
    color: var(--ccb-text-color);
    border-radius: 20px;
    font-size: 8px;

    transition: transform 0.4s;

    &.rotated {
      transform: rotate(180deg);
    }
  }
}
</style>
