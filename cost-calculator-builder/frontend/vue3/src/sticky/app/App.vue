<template>
  <component :is="currentComponent" v-if="showSticky"></component>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, inject, onMounted, ref } from "vue";
import { IncomingStickyData } from "@/sticky/shared/types/app";
import { useAppStore } from "@/sticky/app/providers/app.store.ts";
import { convertKeysToCamelCase } from "@/sticky/shared/utils/convert-case-to-camel-case.utils.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

declare global {
  interface Window {
    ccb_sticky_data?: Record<number, IncomingStickyData>;
  }
}

const appStore = useAppStore();
const translationsStore = useTranslationsStore();

const showSticky = ref<boolean>(false);

const calcId = inject("calc_id") as number;
const calcData = window["ccb_sticky_data"]?.[
  calcId
] as unknown as IncomingStickyData;

if (calcData && "stickySettings" in calcData) {
  const stickySettings: IncomingStickyData =
    convertKeysToCamelCase<IncomingStickyData>(calcData);

  appStore.setIsProActive(stickySettings.is_pro_active);
  appStore.initTitle(stickySettings.title);
  appStore.initCalcId(stickySettings.calcId);
  appStore.initStickySettings(stickySettings.stickySettings);
  appStore.initWooCheckoutSettings(stickySettings.wooCheckoutSettings);
  appStore.initCurrencySettings(stickySettings.currency);
  translationsStore.initTranslations(calcData.translations);
}

const showActionHandler = (): void => {
  let timeout = 0;
  if (appStore.getStickySettings?.displayType !== "banner") {
    timeout = 100;
  }

  setTimeout(() => {
    showSticky.value = true;

    setTimeout(() => {
      const $wrapper: HTMLElement | null = document.querySelector(
        "#ccb-sticky-floating-wrapper",
      );

      if (!$wrapper?.classList?.contains("ccb-show")) {
        $wrapper?.classList?.add("ccb-show");
      }
    }, 500);
  }, timeout);
};

onMounted(() => {
  if (appStore.getStickySettings?.oneClickAction === "scroll_to") {
    const $wrapper: NodeList = document.querySelectorAll(
      `.ccb-main-widget[data-calc-id="${appStore.getCalcId}"]`,
    );

    if ($wrapper?.length) showActionHandler();
  } else {
    showActionHandler();
  }
});

const currentComponent = computed(() => {
  return appStore.getStickySettings?.displayType === "banner"
    ? defineAsyncComponent(() => import("@/sticky/widgets/floating-bar"))
    : defineAsyncComponent(() => import("@/sticky/widgets/floating-cart"));
});
</script>

<style lang="scss">
body {
  .pac-container {
    z-index: 9999;
  }
}

#ccb-sticky-floating-wrapper {
  display: flex;

  transition: all 200ms linear;
  opacity: 0;
  visibility: hidden;
  z-index: -1111;

  &.ccb-show {
    opacity: 1;
    visibility: visible;
    z-index: 111;
  }
}

.ccb-sticky-modal {
  &.modal {
    &.show {
      &.fade {
        opacity: 1;
      }
    }
  }
}

.ccb-sticky-modal {
  padding: 0 !important;
  margin: 0 !important;
  height: 100% !important;
  max-width: 100% !important;
  overflow: hidden !important;

  .modal-dialog {
    top: 0 !important;
    margin-top: -20px !important;
    display: flex;
    align-items: center;
    max-width: 983px !important;
    height: 100% !important;

    .modal-content {
      max-height: calc(100% - 200px) !important;
      overflow-x: hidden;
      overflow-y: scroll;
    }
  }

  &.show {
    .modal-dialog {
      max-width: 983px;
    }
  }

  @media screen and (max-width: 1024px) {
    .modal-dialog {
      margin: 20px auto !important;
      max-width: calc(100% - 60px) !important;

      .modal-content {
        max-height: calc(100% - 60px) !important;

        .calc-container {
          grid-template-columns: repeat(1, 1fr) !important;
        }
      }
    }
  }

  &.pro_features {
    .ccb-vertical {
      display: flex !important;
    }

    .ccb-fields-block {
      display: none !important;
    }

    .ccb-subtotals-block {
      width: 100%;
    }

    .modal-dialog {
      max-width: 500px !important;
    }

    @media screen and (max-width: 600px) {
      .modal-dialog {
        margin: 20px auto !important;
        max-width: calc(100% - 60px) !important;

        .modal-content {
          max-height: calc(100% - 60px) !important;
        }
      }
    }

    .calc-container {
      grid-template-columns: repeat(1, 1fr) !important;
    }
  }
}

@media screen and (max-width: 1280px) {
  .ccb-sticky-calc {
    display: none;

    &.is-banner,
    &.allow-on-mobile {
      display: block !important;
    }
  }
}

.ccb-sticky-calc {
  &[data-position="hidden"] {
    display: none !important;
  }
}

.modal-content {
  background:
    linear-gradient(0deg, rgba(0, 0, 0, 0.12) 0%, rgba(0, 0, 0, 0.12) 100%),
    var(--ccb-container-color, #fff) !important;
  border-radius: var(--ccb-container-border-radius, 20px) !important;
}
</style>
