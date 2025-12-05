<template>
  <div
    class="sticky-calculator-banner"
    :style="getBannerPosition"
    :class="classList"
  >
    <div class="sticky-calculator-banner--container ccb-desktop">
      <div class="sticky-calculator-banner--left">
        <div
          class="sticky-bar-icon is-image"
          v-if="!appStore.getStickySettings?.hideIcon"
        >
          <template v-if="appStore.getStickySettings?.icon">
            <img :src="appStore.getStickySettings.icon" alt="icon svg" />
          </template>
          <template v-else>
            <i class="ccb-icon-Calculators-filled"></i>
          </template>
        </div>
        <div class="sticky-bar-text">
          <span class="sticky-bar-text__title">{{ getTitle }}</span>
          <span
            class="sticky-bar-text__description"
            v-if="appStore.getStickySettings?.customDesc.trim()?.length"
            >{{ appStore.getStickySettings.customDesc }}</span
          >
        </div>
      </div>
      <div class="sticky-calculator-banner--right">
        <div class="sticky-bar-totals" v-if="showTotal">
          <span
            >{{ translationsStore.getTranslations.total }}:
            {{ getFormattedTotal }}</span
          >
        </div>
        <div class="sticky-bar-action">
          <button
            @click.prevent="clickAction"
            style="height: 40px"
            class="calc-btn-action success"
            v-text="appStore.getStickySettings?.btnText"
          ></button>
        </div>
      </div>
    </div>
    <div class="sticky-calculator-banner--container ccb-mobile">
      <div class="sticky-calculator-banner--top">
        <div
          class="sticky-bar-icon"
          v-if="!appStore.getStickySettings?.hideIcon"
        >
          <template v-if="appStore.getStickySettings?.icon">
            <img :src="appStore.getStickySettings.icon" alt="icon svg" />
          </template>
          <template v-else>
            <i class="ccb-icon-Calculators-filled"></i>
          </template>
        </div>
        <div class="ccb-sticky-banner-content">
          <div class="sticky-bar-text">
            <span class="sticky-bar-text__title">{{ getTitle }}</span>
            <span
              class="sticky-bar-text__description"
              v-if="appStore.getStickySettings?.customDesc?.trim()?.length"
              >{{ appStore.getStickySettings.customDesc }}</span
            >
          </div>
          <div class="sticky-bar-totals" v-if="showTotal">
            <span
              >{{ translationsStore.getTranslations.total }}:
              {{ getFormattedTotal }}</span
            >
          </div>
        </div>
      </div>
      <div class="sticky-calculator-banner--bottom">
        <div class="sticky-bar-action">
          <button
            @click.prevent="clickAction"
            style="height: 40px"
            class="calc-btn-action success"
            v-text="appStore.getStickySettings?.btnText"
          ></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import $ from "jquery";
import { computed, onMounted } from "vue";
import { useAppStore } from "@/sticky/app/providers/app.store.ts";
import { useSticky } from "@/sticky/actions/sticky/composable/useSticky.ts";
import { StickyFormulas } from "@/sticky/shared/types/app";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const appStore = useAppStore();
const translationsStore = useTranslationsStore();

const {
  classList,
  clickAction,
  getFormattedTotal,
  getTitle,
  showTotal,
  updateFormulas,
  initListeners,
  scrollHandler,
  updateClassList,
  customBannerEvent,
} = useSticky();

const getBannerPosition = computed(() => {
  let value = 0;
  const $wpBanner = $("#wpadminbar");
  if (
    $wpBanner?.length &&
    appStore.getStickySettings?.bannerPosition === "top" &&
    window.innerWidth > 600
  ) {
    value += $wpBanner?.height() || 0;
  }

  return { [appStore.getStickySettings?.bannerPosition || ""]: `${value}px` };
});

onMounted(() => {
  const bannerPosition = appStore.getStickySettings?.bannerPosition || "";
  const oneClickAction = appStore.getStickySettings?.oneClickAction || "";

  if (!classList.value.includes(bannerPosition)) {
    updateClassList([...classList.value, bannerPosition]);
  }

  if (!classList.value.includes("modal-action")) {
    updateClassList([...classList.value, "modal-action"]);
  }

  if (
    ["open_modal", "pro_features", "woo_product_as_modal"].includes(
      oneClickAction,
    )
  ) {
    customBannerEvent(true);
  } else if (oneClickAction === "scroll_to") {
    scrollHandler();
  }

  let formulas: StickyFormulas[] = appStore.getStickySettings?.formulas || [];
  if (
    [
      "woo_checkout",
      "woo_product_as_modal",
      "woo_product_with_redirect",
    ].includes(oneClickAction)
  ) {
    formulas = appStore.getWooCheckoutSettings?.formulas || [];
  }

  updateFormulas(formulas);
  initListeners();
});
</script>

<style scoped lang="scss">
.sticky-calculator-banner {
  position: fixed !important;
  box-sizing: border-box;
  transition: all 1s ease-in-out;
  display: flex !important;
  justify-content: center !important;
  z-index: 998;
  border-top: 1px solid !important;
  box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.15);
  background: var(--ccb-container-color) !important;
  border-color: var(--ccb-container-border-color) !important;
  width: 100%;
  height: 56px;
  align-items: center;

  &--left {
    display: flex;
    column-gap: 10px;
    align-items: center;
  }

  &--right {
    display: flex;
    align-items: center;
    column-gap: 20px;

    .sticky-bar-totals {
      font-size: 16px;
      font-weight: 700;
      color: var(--ccb-text-color);
    }
  }

  &--container {
    max-width: 1170px;
    display: flex;
    justify-content: space-between;
    width: 100%;

    &.ccb-mobile {
      display: none;
    }
  }

  &.ccb-hide-button {
    &.bottom {
      bottom: -300px !important;
    }

    &.top {
      top: -300px !important;
    }

    z-index: -1111 !important;
  }

  .sticky-bar-icon {
    display: flex;

    i {
      font-size: 24px;
      color: var(--ccb-accent-color);
    }
  }

  .sticky-bar-totals {
    span {
      color: var(--ccb-text-color) !important;
    }
  }

  .sticky-bar-text {
    display: flex;
    align-items: center;
    row-gap: 2px !important;

    &__title {
      font-size: 15px;
      font-weight: 500;
      color: var(--ccb-text-color);
    }

    span {
      color: var(--ccb-text-color) !important;
    }
  }

  .calc-btn-action {
    cursor: pointer;
    font-size: 14px;
    font-weight: 700;
    border-radius: 4px;
    color: var(--ccb-container-color);
    background-color: var(--ccb-accent-color);
    transition: all 200ms linear;
    white-space: nowrap;
    line-height: 1;
    padding: 0 16px;
    border: none !important;
    margin: 0 !important;
    outline: none !important;
    box-shadow: none;
    height: 40px;
    display: flex;
    align-items: center;
  }

  @media screen and (max-width: 680px) {
    height: max-content !important;
    padding: 16px 20px !important;
    &--container {
      &.ccb-desktop {
        display: none !important;
      }

      &.ccb-mobile {
        display: flex;
        flex-direction: column;
        row-gap: 12px;
      }

      .sticky-calculator-banner--top {
        align-items: flex-start !important;
      }
    }

    .sticky-bar-action {
      button {
        height: 40px;
        width: 100%;
        text-align: center;
        justify-content: center;
      }
    }

    .ccb-sticky-banner-content {
      width: 100%;
      display: flex;
      justify-content: space-between;
    }
  }

  @media screen and (max-width: 480px) {
    .ccb-sticky-banner-content {
      flex-direction: column;
    }
  }
}
</style>
