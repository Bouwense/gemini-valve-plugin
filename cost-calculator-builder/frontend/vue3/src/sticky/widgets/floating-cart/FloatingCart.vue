<template>
  <div
    class="sticky-calculator-btn"
    :style="getBtnPosition"
    @click.prevent="clickAction"
    :class="classList"
  >
    <div
      class="sticky-calculator-btn--icon is-image"
      v-if="!appStore.getStickySettings?.hideIcon"
    >
      <template v-if="appStore.getStickySettings?.icon">
        <img :src="appStore.getStickySettings.icon" alt="icon svg" />
      </template>
      <template v-else>
        <i class="ccb-icon-Calculators-filled"></i>
      </template>
    </div>
    <div class="sticky-calculator-btn--text">
      <span class="sticky-calculator-btn--text__title">{{
        getShortTitle
      }}</span>
      <span class="sticky-calculator-btn--text__description" v-if="showTotal"
        >{{ translationsStore.getTranslations.total }}:
        {{ getFormattedTotal }}</span
      >
    </div>
    <div class="ccb-icon-arrow">
      <i class="ccb-icon-Down"></i>
    </div>
  </div>
</template>

<script lang="ts" setup>
import $ from "jquery";
import { computed, onMounted } from "vue";
import { useAppStore } from "@/sticky/app/providers/app.store.ts";
import { useSticky } from "@/sticky/actions/sticky/composable/useSticky.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const {
  classList,
  clickAction,
  getFormattedTotal,
  getShortTitle,
  showTotal,
  updateFormulas,
  initListeners,
} = useSticky();

const appStore = useAppStore();
const translationsStore = useTranslationsStore();

const getBtnPosition = computed(() => {
  const positions: string[] =
    appStore.getStickySettings?.btnPosition.split("_") || [];
  const vertical = positions[0] || "";
  const horizontal = positions[1] || "";

  let horizontalPosition = window.innerWidth > 1280 ? 40 : 20;
  const $prev = $(`.ccb-calc-id-${appStore.getCalcId}`)?.prevAll();

  $prev.each(function () {
    const el = $(this)[0];
    const pos = $(el).data("position");
    if (pos === appStore.getStickySettings?.btnPosition) {
      const horizontal =
        $(el).find(".sticky-calculator-btn")?.outerWidth() || 0;
      horizontalPosition += horizontal > 0 ? horizontal + 20 : horizontal;
    }
  });

  let verticalPosition = window.innerWidth > 1280 ? 40 : 20;
  const bannerQuery = ".sticky-calculator-banner";
  if (
    $(bannerQuery)?.length > 0 &&
    !$(bannerQuery).hasClass("ccb-hide-button")
  ) {
    const classListArr: string[] =
      $(bannerQuery)?.attr("class")?.split(/\s+/) || [];

    if (
      classListArr.includes(vertical) &&
      !$(bannerQuery)?.hasClass("ccb-hide-button")
    ) {
      verticalPosition = 20;
      let height = $(".sticky-calculator-banner").height() || 0;
      let extraHeight = window.innerWidth > 600 ? 0 : 20;
      verticalPosition = verticalPosition + height + extraHeight;
    }
  }

  const $wpBanner = $("#wpadminbar");
  if ($wpBanner.length && vertical === "top") {
    verticalPosition += ($wpBanner?.height() || 0) - 10;
  }

  const data = {
    [vertical]: `${verticalPosition}px`,
    [horizontal]: `${horizontalPosition}px`,
  };

  if (horizontal === "center") {
    let gap = 0;
    const $prevAll = $(`.ccb-calc-id-${appStore.getCalcId}`)
      ?.prevAll()
      ?.filter(function () {
        return (
          $(this).data("position") === appStore.getStickySettings?.btnPosition
        );
      });

    $prevAll.each(function () {
      gap =
        ($(this)?.find(".sticky-calculator-btn")?.outerWidth() || 0) + 20 || 0;
    });

    data.left = `calc(40% + ${gap}px)`;
  }

  if (vertical === "center") {
    data.top = "50%";
    data.transform = "translateY(-50%)";
  }
  return data;
});

onMounted(() => {
  const action: string = appStore.getStickySettings?.oneClickAction || "";
  let formulas = appStore.getStickySettings?.formulas || [];
  if (
    [
      "woo_checkout",
      "woo_product_as_modal",
      "woo_product_with_redirect",
    ].includes(action)
  ) {
    formulas = appStore.getWooCheckoutSettings?.formulas || [];
  }

  updateFormulas(formulas);
  initListeners();
});
</script>

<style scoped lang="scss">
.sticky-calculator-btn {
  position: fixed !important;
  z-index: 111;
  cursor: pointer !important;
  transition: all 200ms ease-in-out;
  background: var(--ccb-container-color) !important;
  border-color: var(--ccb-container-border-color) !important;
  display: flex;
  align-items: center;
  justify-content: center;
  column-gap: 10px;
  padding: 7px 14px;
  border-radius: 8px;
  box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.15);
  font-size: 14px;

  &--text {
    display: flex;
    flex-direction: column;
    row-gap: 3px;
  }

  .ccb-icon-arrow {
    font-size: 12px;
    transform: rotate(180deg);
  }

  .sticky-calculator-btn--text__title {
    color: var(--ccb-text-color) !important;
    font-size: 15px;
    font-weight: 500;
    line-height: 1;
  }

  .sticky-calculator-btn--text__description {
    color: var(--ccb-fields-description-color) !important;
    font-size: 12px;
    font-weight: 500;
    line-height: 1;
  }

  i {
    color: var(--ccb-accent-color) !important;

    &.ccb-icon-Calculators-filled {
      font-size: 24px;
    }
  }

  &.ccb-hide-button {
    opacity: 0;
    visibility: hidden;
    z-index: -1111 !important;
  }
}
</style>
