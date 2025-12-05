<template>
  <div class="ccb-legacy" v-if="!hideThankYouPage">
    <Component
      :is="isLiveDemoLayout ? LiveDemoLayout : Layout"
      class="ccb-layout-container"
    >
      <EditButton />

      <Wrapper wrapper="fields" class="ccb-fields-block">
        <HeaderTitle :title="appStore.getCalcTitle" />
        <Grid grid="list">
          <template v-for="field in getFields" :key="field.alias">
            <template v-if="field.fieldName === 'repeater'">
              <CalculatorField
                v-if="isFieldHidden(field)"
                :name="field.fieldName"
                :field="field"
              />
            </template>
            <template v-else>
              <Transition name="fade">
                <CalculatorField
                  v-if="isFieldHidden(field)"
                  :name="field.fieldName"
                  :field="field"
                />
              </Transition>
            </template>
          </template>
        </Grid>
      </Wrapper>

      <Wrapper wrapper="subtotal" class="ccb-subtotals-block" :id="getStickyId">
        <template v-slot:default>
          <template v-if="formDisplaySummaryStatus || !appStore.isProActive">
            <TotalSummaryList list-type="summary">
              <template
                v-for="summary in fieldsStore.getSummaryList"
                :key="summary.alias"
              >
                <Transition name="fade">
                  <TotalSummaryItem
                    v-if="!summary.hidden"
                    :item-type="getFieldItemType(summary)"
                    :summary="summary"
                  ></TotalSummaryItem>
                </Transition>
              </template>
            </TotalSummaryList>

            <TotalSummaryList list-type="total">
              <template
                v-for="summary in fieldsStore.getTotalsList"
                :key="summary.alias"
              >
                <Transition name="fade">
                  <TotalSummaryItem
                    v-if="!summary.hidden"
                    item-type="total"
                    :summary="summary"
                  ></TotalSummaryItem>
                </Transition>
              </template>
            </TotalSummaryList>
          </template>
          <template v-else>
            <HeaderTitle
              :title="settingsStore.getFormSettings?.summaryDisplay?.formTitle"
            />
          </template>

          <template v-if="appStore.isProActive">
            <Promocode v-if="hasPromocodes()" />

            <WooRedirectCart v-if="showWooRedirectCart" />

            <div
              class="ccb-actions"
              :class="activeActionCount"
              ref="actionsRef"
              :style="{
                flexDirection: !orderFormStore.getNextButtonStatus
                  ? 'column'
                  : 'row',
                flexWrap: flexWrap ? 'nowrap' : 'wrap',
              }"
              v-if="appStore.isProActive"
            >
              <Submission class="ccb-actions__item" />
              <PdfInvoice class="ccb-actions__item" />
            </div>
          </template>
        </template>
        <template v-slot:notifications>
          <Notifications
            v-if="showNotifications && appStore.isProActive"
            :type="notificationsStore.notificationType"
            :message="notificationsStore.message"
            :description="notificationsStore.description"
          />
        </template>
      </Wrapper>
    </Component>
    <CCBPopup size="medium" ref="popup" v-if="appStore.isProActive">
      <ThankYouPage />
    </CCBPopup>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from "vue";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";

import Notifications from "@/widget/shared/ui/components/Notifications";
import CCBPopup from "@/widget/shared/ui/components/Popup/Popup.vue";
import ThankYouPage from "@/widget/features/thank-you-page/ThankYouPage.vue";
import Layout from "@/widget/shared/ui/layouts/Layout.vue";
import LiveDemoLayout from "@/widget/shared/ui/layouts/LiveDemoLaout.vue";
import Wrapper from "@/widget/shared/ui/wrappers/Wrapper.vue";
import Grid from "@/widget/shared/ui/grids/Grid.vue";
import CalculatorField from "@/widget/features/calculator-fields/CalculatorField.vue";
import HeaderTitle from "@/widget/shared/ui/wrappers/components/HeaderTitle.vue";
import WooRedirectCart from "@/widget/shared/ui/wrappers/components/WooRedirectCart.vue";
import TotalSummaryList from "@/widget/shared/ui/total-summary/TotalSummaryList.vue";
import TotalSummaryItem from "@/widget/shared/ui/total-summary/TotalSummaryItem.vue";
import EditButton from "@/widget/shared/ui/components/Edit-button/EditButton.vue";

import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore.ts";

const orderFormStore = useOrderFormStore();

import Submission from "@/widget/features/submission";
import PdfInvoice from "@/widget/features/pdf-invoice/send-quote";

import { Field } from "@/widget/shared/types/fields";
import Promocode from "@/widget/features/discounts/components";
import { useDiscounts } from "@/widget/actions/discounts/composable/useDiscounts.ts";
import { useNotificationsStore } from "@/widget/app/providers/stores/notificationsStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore";

const appStore = useAppStore();
const fieldsStore = useFieldsStore();
const notificationsStore = useNotificationsStore();
const submissionStore = useSubmissionStore();
const settingsStore = useSettingsStore();
const appearanceStore = useAppearanceStore();
const { hasPromocodes } = useDiscounts();

const popup = ref();
const pageBreakEnabled = computed(() => fieldsStore.getPageBreakEnabled);
const actionsRef = ref<HTMLElement | null>(null);

const isLiveDemoLayout = computed(() => {
  return appStore.getIsLive && !pageBreakEnabled.value;
});

const flexWrap = computed(() => {
  return settingsStore.getFormSettings?.accessEmail;
});

const activeActionCount = computed(() => {
  const getActionsCount = (): number => {
    if (actionsRef.value) {
      return actionsRef.value.children.length;
    }
    return 0;
  };

  return showShareBtn.value
    ? `button-${getActionsCount()} has-share`
    : `button-${getActionsCount()} `;
});

const showShareBtn = computed(() => {
  return (
    settingsStore.getInvoice?.useInAll && settingsStore.getInvoice?.emailButton
  );
});

const showWooRedirectCart = computed((): boolean => {
  return (
    notificationsStore.notificationType === "success" && getWooStayOnPage.value
  );
});

const getWooStayOnPage = computed((): boolean => {
  return (
    settingsStore.getWooCheckoutSettings?.redirectTo === "stay" &&
    notificationsStore.message === "Stay on page"
  );
});

const isFieldHidden = computed(() => {
  return (field: Field) => {
    if (field?.hidden) {
      return !field.hidden;
    }

    if (field?.hideAndLeaveTotal) {
      return !field.hideAndLeaveTotal;
    }

    return true;
  };
});

const showThankYouPage = computed((): boolean => {
  return !(
    settingsStore.hasThankYouPage &&
    notificationsStore.notificationType === "finish"
  );
});

const getFieldItemType = computed(() => {
  return (summary: Field): "summary" | "repeater" | "group" | "total" => {
    const types: { [key: string]: "repeater" | "group" | "total" } = {
      repeater: "repeater",
      group: "group",
      total: "total",
    };
    return types[summary.fieldName as keyof typeof types] || "summary";
  };
});

const showNotifications = computed((): boolean => {
  return (
    notificationsStore.notificationStatus &&
    (notificationsStore.notificationType !== "finish" ||
      !settingsStore.hasThankYouPage) &&
    !getWooStayOnPage.value
  );
});

const getThankYouPageSettings = computed(() => settingsStore.thankYouPage);

const hideThankYouPage = computed((): boolean => {
  const settings = getThankYouPageSettings.value;
  const type = settings?.type;
  const enable = settings?.enable;

  if (
    notificationsStore.notificationType === "finish" &&
    settingsStore.hasThankYouPage
  ) {
    return getThankYouPageSettings.value?.type === "same_page";
  }

  if (notificationsStore.notificationType === "finish" && enable) {
    if (type === "separate_page" && settings?.pageUrl) {
      let link = settings?.pageUrl;
      link = link.includes("?")
        ? `${link}&order_id=${submissionStore.orderId}`
        : `${link}?order_id=${submissionStore.orderId}`;

      localStorage.setItem("ccb_previous_page", window.location.href);
      notificationsStore.resetNotifications();
      window.location.replace(link);
    }

    if (type === "custom_page" && settings.customPageLink) {
      notificationsStore.resetNotifications();
      window.open(settings.customPageLink, "_blank");
    }
  }
  return false;
});

const getStickyId = computed(() => {
  const stickyStatus = settingsStore.general?.sticky;
  if (appearanceStore.getAppearanceBoxStyle === "horizontal" || !stickyStatus) {
    if (window.$ccbSticky) window.$ccbSticky.destroy();
    return null;
  }

  if (window.$ccbSticky) {
    window.$ccbSticky?.destroy();
    delete window.$ccbSticky;
    initSticky();
  }

  return `ccb_summary_sticky_${appStore.getCalcId}`;
});

const formDisplaySummaryStatus = computed(() => {
  const summaryDisplay = settingsStore.getFormSettings?.summaryDisplay;
  if (summaryDisplay?.enable && settingsStore.getFormSettings?.accessEmail) {
    if (
      ["show_summary_block", "show_summary_block_with_pdf"].includes(
        summaryDisplay?.actionAfterSubmit,
      )
    ) {
      return settingsStore.summaryDisplayShowSummary;
    }

    return false;
  }

  return true;
});

const getFields = computed(() => {
  return fieldsStore.getFields.filter((field) => field.fieldName !== "total");
});

watch(showThankYouPage, (newValue) => {
  const isModal = getThankYouPageSettings.value?.type === "modal";

  if (!newValue) {
    appStore.updateThankYouPageStatus(!isModal);
    isModal ? popup.value?.showPopup() : null;
  } else {
    popup.value?.hidePopup();
  }
});

const initSticky = () => {
  if (getStickyId.value && appStore.isProActive) {
    setTimeout(() => {
      const $selector = `#${getStickyId.value}` || "";
      const $sticky = document.querySelector($selector);
      if ($sticky) {
        window.$ccbSticky = new window.StickySidebar($selector, {
          topSpacing: 20,
          bottomSpacing: 20,
          containerSelector: ".ccb-layout-container",
          innerWrapperSelector: ".ccb-subtotals-block",
          minWidth: 748,
        });
      }
    }, 1000);
  }
};

onMounted(() => {
  initSticky();
});
</script>

<style lang="scss">
.ccb-legacy {
  max-width: 1250px;
  margin: 0 auto;

  .ccb-fields-wrapper {
    .ccb-header-title {
      margin-bottom: 20px;
    }
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.35s ease-in-out;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.ccb-actions {
  display: flex;
  flex-direction: row;
  gap: 10px;
  flex-wrap: wrap;

  &__item {
    width: 100%;

    button {
    }
  }

  &.button-2 {
    display: flex;
    gap: 10px;
    justify-content: space-between;

    &.has-share {
      flex-direction: column !important;
    }
  }
}
</style>
