<template>
  <div
    class="ccb-page-breaker"
    :class="[{ 'summary-last-page': summaryLastPage }, currentPageStyle]"
    ref="pageBreakerRef"
    v-if="!hideThankYouPage"
  >
    <Layout>
      <EditButton />
      <Wrapper wrapper="fields">
        <PaginationItem v-if="hidePagination" />
        <Grid grid="list">
          <template
            v-for="(page, index) in fieldsStore.getPages"
            :key="page.alias"
          >
            <template
              v-if="
                index === activePageIndex && 'groupElements' in page && 'alias'
              "
            >
              <template v-for="inPage in page.groupElements">
                <template
                  v-for="field in fieldsStore.getFields"
                  :key="field.alias"
                >
                  <Transition name="fade">
                    <CalculatorField
                      v-if="
                        isFieldHidden(field) &&
                        'alias' in inPage &&
                        inPage.alias === field.alias
                      "
                      :name="field.fieldName"
                      :field="field"
                    />
                  </Transition>
                </template>
              </template>
            </template>
          </template>

          <template v-if="activePageIndex === fieldsStore.getPages.length">
            <Wrapper
              wrapper="subtotal"
              class="ccb-subtotals-block"
              :id="getStickyId"
            >
              <template v-slot:default>
                <template v-if="formDisplaySummaryStatus">
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
                    :title="
                      settingsStore.getFormSettings?.summaryDisplay?.formTitle
                    "
                  />
                </template>

                <Promocode v-if="hasPromocodes()" />

                <WooRedirectCart v-if="showWooRedirectCart" />

                <div
                  class="ccb-actions"
                  :class="activeActionCount"
                  :style="
                    !orderFormStore.getNextButtonStatus
                      ? 'flex-direction: column;'
                      : ''
                  "
                  ref="actionsRef"
                >
                  <Submission class="ccb-actions__item" />
                  <PdfInvoice class="ccb-actions__item" />
                </div>
              </template>

              <template v-slot:notifications>
                <Notifications
                  v-if="notificationsStore.notificationStatus"
                  :type="notificationsStore.notificationType"
                  :message="notificationsStore.message"
                />
              </template>
            </Wrapper>
          </template>
        </Grid>
        <div
          class="ccb-page-navigation"
          :class="{ 'page-formulas': totalsInNavigation }"
        >
          <div class="ccb-page-navigation__formulas" v-if="totalsInNavigation">
            <div class="ccb-page-navigation__totals">
              <template v-for="summary in fieldsStore.getTotalsList">
                <Transition name="fade">
                  <TotalSummaryItem
                    v-if="
                      !summary.hidden && pageFormulas.includes(summary.alias)
                    "
                    :key="summary.alias"
                    item-type="total"
                    :summary="summary"
                  ></TotalSummaryItem>
                </Transition>
              </template>
            </div>
            <div class="ccb-page-navigation__popup-action">
              <span @click="showPopup">{{
                translationsStore.getTranslations.showSummary
              }}</span>
              <CCBPopup ref="popup">
                <div class="ccb-page-popup">
                  <div class="ccb-page-popup__header">
                    <div class="ccb-page-popup__title">
                      {{ settingsStore.getGeneralSettings?.headerTitle }}
                    </div>
                    <div class="ccb-page-popup__close" @click="hidePopup">
                      <i class="ccb-icon-close"></i>
                    </div>
                  </div>
                  <div class="ccb-page-popup__body">
                    <Wrapper wrapper="subtotal">
                      <template v-slot:default>
                        <TotalSummaryList list-type="summary">
                          <template
                            v-for="summary in fieldsStore.getSummaryList"
                            :key="summary.alias"
                          >
                            <Transition name="fade">
                              <TotalSummaryItem
                                v-if="
                                  !summary.hidden &&
                                  activePageFieldsAliases.includes(
                                    summary.alias,
                                  )
                                "
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
                    </Wrapper>
                  </div>
                </div>
              </CCBPopup>
            </div>
          </div>
          <div class="ccb-page-navigation__actions">
            <Button
              type="success-outlined"
              :text="prevBtnText"
              @click="prevPage"
              icon="ccb-icon-Arrow-Previous"
              iconPosition="before"
              v-if="hideBackButton"
            ></Button>
            <Button
              type="success"
              :text="nextBtnText"
              @click="nextPage"
              icon="ccb-icon-Arrow-Previous"
              iconPosition="after"
              class="next-btn"
              v-if="hideNextButton"
              :disabled="pageBreakerStore.getDisableNextButton"
            ></Button>
          </div>
        </div>
      </Wrapper>
      <Wrapper
        v-if="!summaryLastPage"
        wrapper="subtotal"
        class="ccb-subtotals-block"
        :id="getStickyId"
      >
        <template v-slot:default>
          <template v-if="formDisplaySummaryStatus">
            <TotalSummaryList list-type="summary">
              <template
                v-for="summary in fieldsStore.getSummaryList"
                :key="summary.alias"
              >
                <Transition name="fade">
                  <TotalSummaryItem
                    v-if="
                      !summary.hidden &&
                      activePageFieldsAliases.includes(summary.alias)
                    "
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

          <Promocode v-if="hasPromocodes()" />

          <WooRedirectCart v-if="showWooRedirectCart" />

          <div
            class="ccb-actions"
            :class="activeActionCount"
            ref="actionsRef"
            :style="
              !orderFormStore.getNextButtonStatus
                ? 'flex-direction: column;'
                : ''
            "
          >
            <Submission class="ccb-actions__item" />
            <PdfInvoice class="ccb-actions__item" />
          </div>
        </template>

        <template v-slot:notifications>
          <Notifications
            v-if="showNotifications"
            :type="notificationsStore.notificationType"
            :message="notificationsStore.message"
          />
        </template>
      </Wrapper>
      <CCBPopup size="medium" ref="popup" v-if="appStore.isProActive">
        <ThankYouPage />
      </CCBPopup>
    </Layout>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import Layout from "@/widget/shared/ui/layouts/Layout.vue";
import Wrapper from "@/widget/shared/ui/wrappers/Wrapper.vue";
import PaginationItem from "@/widget/shared/ui/components/Step-pagination/PaginationItem.vue";
import CalculatorField from "@/widget/features/calculator-fields/CalculatorField.vue";
import Grid from "@/widget/shared/ui/grids/Grid.vue";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import TotalSummaryList from "@/widget/shared/ui/total-summary/TotalSummaryList.vue";
import TotalSummaryItem from "@/widget/shared/ui/total-summary/TotalSummaryItem.vue";
import EditButton from "@/widget/shared/ui/components/Edit-button/EditButton.vue";
import Promocode from "@/widget/features/discounts/components";
import { useDiscounts } from "@/widget/actions/discounts/composable/useDiscounts.ts";
import { useNotificationsStore } from "@/widget/app/providers/stores/notificationsStore.ts";
import Submission from "@/widget/features/submission";
import Notifications from "@/widget/shared/ui/components/Notifications";
import Button from "@/widget/shared/ui/components/Button/Button.vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import CCBPopup from "@/widget/shared/ui/components/Popup/Popup.vue";
import { Field } from "@/widget/shared/types/fields";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import HeaderTitle from "@/widget/shared/ui/wrappers/components/HeaderTitle.vue";
import PdfInvoice from "@/widget/features/pdf-invoice/send-quote";
import WooRedirectCart from "@/widget/shared/ui/wrappers/components/WooRedirectCart.vue";
import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore.ts";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";

const appStore = useAppStore();
const orderFormStore = useOrderFormStore();
const appearanceStore = useAppearanceStore();
const pageBreakerStore = usePageBreakerStore();

import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import ThankYouPage from "@/widget/features/thank-you-page";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";

const fieldsStore = useFieldsStore();
const settingsStore = useSettingsStore();
const submissionStore = useSubmissionStore();
const pageBreakerSettings = settingsStore.getPageBreakerSettings;

const { hasPromocodes } = useDiscounts();
const notificationsStore = useNotificationsStore();
const translationsStore = useTranslationsStore();

const getThankYouPageSettings = computed(() => settingsStore.thankYouPage);

const pageHistory: number[] = [];

const showThankYouPage = computed((): boolean => {
  return !(
    settingsStore.hasThankYouPage &&
    notificationsStore.notificationType === "finish"
  );
});

const showNotifications = computed((): boolean => {
  return (
    notificationsStore.notificationStatus &&
    (notificationsStore.notificationType !== "finish" ||
      !settingsStore.hasThankYouPage) &&
    !getWooStayOnPage.value
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

watch(showThankYouPage, (newValue) => {
  const isModal = getThankYouPageSettings.value?.type === "modal";

  if (!newValue) {
    appStore.updateThankYouPageStatus(!isModal);
    isModal ? popup.value?.showPopup() : null;
  } else {
    popup.value?.hidePopup();
  }
});

const activePageIndex = computed(() => {
  return fieldsStore.getActivePageIndex;
});

const actionsRef = ref<HTMLElement | null>(null);

const pageBreakerRef = ref<HTMLElement | null>(null);

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
  return settingsStore.getInvoice?.emailButton;
});

const formDisplaySummaryStatus = computed(() => {
  const summaryDisplay = settingsStore.getFormSettings?.summaryDisplay;
  if (summaryDisplay?.enable) {
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

const popup = ref();

const showPopup = () => {
  popup.value.showPopup();
};

const hidePopup = () => {
  popup.value.hidePopup();
};

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

const getStickyId = computed(() => {
  const stickyStatus = settingsStore.general?.sticky;
  if (appearanceStore.getAppearanceBoxStyle === "horizontal" || !stickyStatus) {
    if (window.$ccbSticky) window.$ccbSticky.destroy();
    return null;
  }

  if (window.$ccbSticky) window.$ccbSticky.initialize();

  return `ccb_summary_sticky_${appStore.getCalcId}`;
});

const prevBtnText = computed(() => {
  const page = fieldsStore.getActivePage;
  return page ? page.previousBtnLabel : "Back";
});

const nextBtnText = computed(() => {
  const page = fieldsStore.getActivePage;
  return page.nextBtnLabel;
});

const pageFormulas = computed(() => {
  return (
    pageBreakerSettings?.formulas?.map((formula) => {
      return formula.alias;
    }) || []
  );
});

const totalsInNavigation = computed(() => {
  return pageBreakerSettings?.totalInPage;
});

const hidePagination = computed(() => {
  return pageBreakerSettings?.paginationType !== "hidden";
});

const summaryLastPage = computed(() => {
  return pageBreakerSettings?.summaryAfterLastPage;
});

const hideBackButton = computed(() => {
  return activePageIndex.value !== 0;
});

const activePageFieldsAliases = computed(() => {
  const allAliases: string[] = [];

  for (let i = 0; i <= activePageIndex.value; i++) {
    const page = fieldsStore.getPages[i];
    if (page && "groupElements" in page) {
      if (page?.groupElements) {
        page.groupElements.forEach((element) => {
          if ("alias" in element && typeof element.alias === "string") {
            if (
              "groupElements" in element &&
              Array.isArray(element.groupElements)
            ) {
              if (element.alias.includes("repeater")) {
                allAliases.push(element.alias);
              }
              element.groupElements.forEach((groupElement: any) => {
                if (
                  "alias" in groupElement &&
                  typeof groupElement.alias === "string"
                ) {
                  allAliases.push(groupElement.alias);
                }
              });
            } else {
              allAliases.push(element.alias);
            }
          }
        });
      }
    }
  }

  return allAliases;
});

const activePageConditionAction = computed(() => {
  return fieldsStore.getActivePage.action;
});

const hideNextButton = computed(() => {
  let length = summaryLastPage.value
    ? fieldsStore.getPages.length
    : fieldsStore.getPages.length - 1;
  return activePageIndex.value !== length;
});

const pageBreakFields = computed(() => {
  const activePage = fieldsStore.getActivePage;
  if (!activePage?.groupElements) {
    return [];
  }

  const fields = Array.from(activePage.groupElements.values());
  return fieldsStore.getFields.filter((field: Field) => {
    return fields.some((groupElement: any) => {
      return groupElement.alias === field.alias;
    });
  });
});

const nextPage = () => {
  const pageConditionResult = pageBreakerStore.checkPageFieldsConditions();

  if (fieldsStore.checkPageRequiredFields(pageBreakFields.value)) {
    if (pageConditionResult) {
      if (activePageConditionAction.value === "not_skip") {
        return;
      } else if (activePageConditionAction.value === "skip") {
        let length = summaryLastPage.value
          ? fieldsStore.getPages.length + 1
          : fieldsStore.getPages.length;

        const nextPageIndex = activePageIndex.value + 2;
        if (nextPageIndex < length) {
          pageHistory.push(activePageIndex.value);
          fieldsStore.updateActivePageIndex(nextPageIndex);
        }
      } else if (activePageConditionAction.value === "jump_to") {
        const pageIndex = fieldsStore.getPageIndex(
          fieldsStore.getActivePage.pageTo,
        );
        if (pageIndex !== undefined) {
          pageHistory.push(activePageIndex.value);
          fieldsStore.updateActivePageIndex(pageIndex);
        }
      }
    } else {
      let length = summaryLastPage.value
        ? fieldsStore.getPages.length + 1
        : fieldsStore.getPages.length;

      const nextPageIndex = activePageIndex.value + 1;
      if (nextPageIndex < length) {
        pageHistory.push(activePageIndex.value);
        fieldsStore.updateActivePageIndex(nextPageIndex);
      }
    }
  } else {
    return;
  }
};

const currentPageStyle = computed(() => {
  const page = fieldsStore.getPages[activePageIndex.value];
  let result = page && "boxStyle" in page ? page.boxStyle : "horizontal";

  if (
    summaryLastPage.value &&
    activePageIndex.value === fieldsStore.getPages.length
  ) {
    result = "vertical";
  }

  return result;
});

const prevPage = () => {
  if (pageHistory.length > 0) {
    const prevPageIndex = pageHistory.pop();
    if (prevPageIndex !== undefined) {
      fieldsStore.updateActivePageIndex(prevPageIndex);
    }
  }
};

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

watch(activePageIndex, () => {
  const pageBreakerElement = pageBreakerRef.value;
  if (pageBreakerElement) {
    const rect = pageBreakerElement.getBoundingClientRect();
    const isVisible = rect.top >= 0 && rect.bottom <= window.innerHeight;

    if (!isVisible) {
      setTimeout(() => {
        pageBreakerElement.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }, 100);
    }
  }
});
</script>

<style lang="scss">
.ccb-page-breaker {
  &.summary-last-page {
    .ccb-vertical {
      grid-template: none;
    }
  }

  .ccb-page-popup {
    background: var(--ccb-fields-bg-color);
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    color: var(--ccb-text-color);
    min-width: 490px;

    &__header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding: 20px;
      border-bottom: 1px solid var(--ccb-fields-border-color);
    }

    &__title {
      font-size: 18px;
      font-weight: 700;
      width: 80%;
    }

    &__close {
      cursor: pointer;
      font-size: 12px;
      color: var(--ccb-text-color);
      background-color: var(--ccb-container-dark-color);
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    &__body {
      .ccb-subtotal-wrapper {
        background: transparent !important;
        border: none !important;
        padding-top: 0px;
      }

      .ccb-summary-list__header {
        display: none;
      }

      .ccb-summary-list {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px;
        &::-webkit-scrollbar {
          width: 4px;
          margin-left: 10px;
        }

        &::-webkit-scrollbar-track {
          background: var(--ccb-fields-border-color);
          border-radius: 2px;
        }

        &::-webkit-scrollbar-thumb {
          background: var(--ccb-accent-color);
          border-radius: 2px;
        }
      }
    }
  }

  .ccb-fields-wrapper {
    padding: 0px !important;
  }

  .ccb-fields-list {
    padding: 20px;
  }

  .ccb-page-navigation {
    display: flex;
    justify-content: flex-end;
    padding: 20px;
    background: var(--ccb-fields-bg-color);

    &__popup-action {
      color: var(--ccb-accent-color);
      font-size: 13px;
      font-weight: 500;

      span {
        cursor: pointer;

        &:hover {
          text-decoration: underline;
        }
      }
    }

    &.page-formulas {
      justify-content: space-between;

      .ccb-page-navigation__actions {
        width: fit-content;
      }
    }

    .ccb-page-navigation__totals {
      .ccb-total-row__item {
        gap: 12px;
        font-size: 13px;
        font-weight: 800;
      }
    }

    button {
      padding: 11px 20px;
      min-width: 110px;
    }

    &__actions {
      width: 100%;
      display: flex;
      justify-content: space-between;
      gap: 20px;

      .next-btn {
        i {
          transform: rotate(180deg);
        }
      }
    }
  }

  &.horizontal {
    .ccb-fields-list {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }
  }
}
</style>
