<template>
  <a
    v-if="getEditCalcButton"
    :href="getEditCalcButton?.editCalcUrl"
    target="_blank"
    class="ccb-calc-edit"
  >
    <span><i class="ccb-icon-Path-3483"></i></span>
    <span class="ccb-calc-edit__text">{{
      translationsStore.getTranslations.edit
    }}</span>
  </a>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const settingsStore = useSettingsStore();
const translationsStore = useTranslationsStore();

const getEditCalcButton = computed(() => settingsStore.getEditCalcButton);
</script>

<style lang="scss">
a,
a:focus,
a:hover,
a:visited {
  text-decoration: none;
  outline: none;
}

.ccb-sticky-modal {
  .ccb-calc-edit {
    display: none;
  }
}

.ccb-calc-edit {
  transition: all 450ms cubic-bezier(0.4, 0.25, 0.3, 1.3);
  width: 40px;
  height: 40px;
  position: absolute;
  top: 10px;
  left: -3%;
  transform: translateX(-50%);
  display: flex;
  justify-content: center;
  align-items: center;
  border: var(--ccb-container-border) solid var(--ccb-container-border-color);
  background-color: var(--ccb-container-converted);
  color: var(--ccb-text-color);
  border-radius: 50%;
  cursor: pointer;
  overflow: hidden;
  box-shadow: none !important;

  @media (max-width: 1239px) {
    display: none;
  }

  span {
    transition: all 400ms cubic-bezier(0.4, 0.25, 0.3, 1.3) 100ms;
    position: absolute;
    top: 50%;
    left: 50%;
    font-size: 18px;
    font-weight: 500;
    line-height: 60px;
    vertical-align: middle;

    &:first-of-type {
      font-size: 20px;
      transform: translate(-50%, -50%);
    }

    &:last-of-type {
      transform: translate(-50%, 50%);
      text-transform: uppercase;
    }
  }

  &:hover {
    width: 70px;
    border-radius: 6px;

    span:first-of-type {
      transform: translate(-50%, -150%);
    }
    span:last-of-type {
      transform: translate(-50%, -50%);
    }
  }
}
</style>
