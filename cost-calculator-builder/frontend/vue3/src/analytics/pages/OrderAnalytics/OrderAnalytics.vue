<template>
  <div class="ccb-analytics-page">
    <AdminLayout>
      <template #header>
        <div class="ccb-analytics-page__header">
          <div class="ccb-analytics-page__header-title">
            {{ translations.analytics }}
            <span
              class="ccb-analytics-page__header-title-subpage"
              v-if="analyticsStore.getCurrentPage?.id"
            >
              / {{ analyticsStore.getCurrentPage.label }}
            </span>
          </div>
          <div class="ccb-analytics-page__header-actions">
            <SelectWithDate
              @update="handleDateUpdate"
              :items="getItems"
              defaultValue="all"
              :placeholder="translations.selectPeriod"
              :class="{
                'ccb-is-loading-for-settings': analyticsStore.isLoading,
              }"
            />

            <CustomSelect
              :items="getStatusItems"
              :current="status"
              :placeholder="translations.allStatuses"
              @update="updateStatusHandler"
              class="ccb-order-status-select"
              :class="{
                'ccb-is-loading-for-settings': analyticsStore.isLoading,
                'ccb-order-status-select': true,
              }"
            />

            <template v-if="getCurrentPage?.id !== 0">
              <PageSettings
                :tabs="getPageSettingsTabs"
                :placeholder="translations.pageSettings"
                :items="analyticsStore.getPageSettings"
                @update="updatePageSettings"
                :class="{
                  'ccb-is-loading-for-settings': analyticsStore.isLoading,
                }"
              />
            </template>
          </div>
        </div>
      </template>
      <template #content>
        <SettingsTab
          :page-options="analyticsOptions"
          @click-option="handleClickOption"
        />
      </template>
    </AdminLayout>
  </div>
</template>

<script setup lang="ts">
import { onMounted, computed, defineAsyncComponent, markRaw, ref } from "vue";
import SettingsTab from "@/analytics/shared/ui/components/SettingsTab";
import AdminLayout from "@/analytics/shared/ui/components/Layout/AdminLayout";
import { AnalyticsOptions } from "@/analytics/shared/types/analytics/analyticsOptions.type";
import { SelectWithDate } from "@/analytics/shared/ui/components/SelectWithDate";
import { PageSettings } from "@/analytics/shared/ui/components/PageSettings";
import { AnalyticsPages } from "@/analytics/shared/types/analytics/analyticsPages.type";
import {
  Period,
  useAnalyticsStore,
} from "@/analytics/store/analytics/useAnalyticsStore";
import { CustomSelect } from "@/analytics/shared/ui/components/CustomSelect";
import { useAdminTranslationsStore } from "@/analytics/store/analytics/translationsStore";

const analyticsStore = useAnalyticsStore();
const translationsStore = useAdminTranslationsStore();

const translations = computed(() => translationsStore.getTranslations);

const status = ref<string>("all");

const analyticsOptions = computed<AnalyticsOptions[]>(() => {
  return [
    {
      label: "",
      icon: "",
      pages: [
        {
          label: translations.value.dashboard,
          id: 0,
          slug: "dashboard",
          icon: "ccb-icon-new-calculator",
          component: markRaw(
            defineAsyncComponent(
              () => import("@/analytics/widgets/Analytics/pages/Dashboard.vue"),
            ),
          ),
        },
      ],
      deleted: false,
    },
    {
      label: translations.value.calculators,
      icon: "",
      pages: analyticsStore.getCalculators,
    },
  ];
});

const handleDateUpdate = (value: string, extra?: string) => {
  analyticsStore.updatePeriod(value as Period, extra);
};

const updateStatusHandler = (key: string) => {
  status.value = key;
  if (status.value !== analyticsStore.getStatus) {
    analyticsStore.updateStatus(status.value as "all" | "complete" | "pending");
    if (analyticsStore.getCurrentPage?.id === 0) {
      analyticsStore.fetchDashboardDataHandler();
    } else {
      analyticsStore.fetchSingleDataHandler(
        analyticsStore.getCurrentPage?.id || 0,
      );
    }
  }
};

const handleClickOption = (page: AnalyticsPages) => {
  analyticsStore.clickOption(page);
};

const updatePageSettings = (
  settings: Record<string, Record<string, boolean>>,
) => {
  analyticsStore.updatePageSettings(settings);
  analyticsStore.savePageSettings();
};

const getItems = computed<{ value: string; label: string }[]>(() => {
  return [
    { value: "all", label: translations.value.allTime },
    { value: "today", label: translations.value.today },
    { value: "yesterday", label: translations.value.yesterday },
    { value: "last_7_days", label: translations.value.last_7Days },
    { value: "last_30_days", label: translations.value.last_30Days },
    { value: "last_90_days", label: translations.value.last_90Days },
    { value: "last_year", label: translations.value.lastYear },
    { value: "custom", label: translations.value.custom },
  ];
});

const getPageSettingsTabs = computed<{ value: string; label: string }[]>(() => {
  return [
    { value: "charts", label: translations.value.charts },
    { value: "widgets", label: translations.value.widgets },
  ];
});

const getStatusItems = computed<{ value: string; label: string }[]>(() => {
  return [
    { value: "all", label: translations.value.allStatuses },
    { value: "complete", label: translations.value.completed },
    { value: "pending", label: translations.value.pending },
  ];
});

const getCurrentPage = computed(() => {
  return analyticsStore.getCurrentPage;
});

onMounted(() => {
  analyticsStore.init(analyticsOptions.value[0].pages[0]);
});
</script>

<style lang="scss">
body {
  background: #d0d5e1 !important;
}

#ccb-admin-app {
  margin-right: 20px;
}

.ccb-analytics-page {
  margin: 15px 0 0;
}

.ccb-analytics-page__header {
  width: 100%;
  height: 100%;
  padding: 0 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;

  &-title {
    color: #788593;
    font-size: 14px;
    font-weight: 700;
    line-height: 18px;
  }

  &-title-subpage {
    color: #00b163;
    font-size: 14px;
    font-weight: 500;
    line-height: 18px;
  }
}

.ccb-analytics-page__header-actions {
  display: flex;
  column-gap: 10px;

  &-status {
    position: relative;
    height: 40px;
    width: max-content;
    display: flex;
    align-items: center;

    i {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #001931 !important;
      font-size: 10px;
      font-weight: 400;
      opacity: 0.35;
    }

    select {
      background: transparent;
      color: #001931 !important;
      display: flex;
      height: 100%;
      width: 100%;
      padding: 0;
      margin: 0;
      box-sizing: border-box;
      box-shadow: none !important;
      border-radius: 4px;
      border: 1px solid #e3e6ea !important;
      padding: 0 20px;
      min-width: 220px;
      width: 100%;
      transition: 300ms ease;

      &:hover {
        border-color: #b2b8c6 !important;
        box-shadow: 0 2px 8px 0 rgba(16, 30, 54, 0.08) !important;
      }
    }

    &:hover {
      border-color: #00b163;
    }
  }
}

.ccb-is-loading-for-settings {
  opacity: 0.5;
  pointer-events: none;
}

.ccb-is-loading {
  opacity: 0 !important;
  pointer-events: none !important;
  z-index: -1 !important;
}

.ccb-select-with-date-content {
  & > div {
    padding: 0 !important;
  }

  .dp__menu_inner {
    flex-direction: column !important;
    row-gap: 15px;
  }

  .dp__menu {
    border: none !important;
  }

  .dp__main > div {
    width: 100%;

    &:first-child {
      display: none;
    }
  }

  .dp__month_year_wrap {
    font-size: 14px;
  }

  .dp__input_icon {
    fill: #001931;
  }

  .dp--menu-wrapper {
    width: 100%;
    left: 0 !important;
    color: #001931 !important;
    max-width: 430px;
  }

  .dp__active_date {
    background: #00b163 !important;
  }

  .dp__arrow_bottom,
  .dp__arrow_top {
    border: none;
    background-color: #f7f7f7;
  }

  .dp__menu {
    border: none;
    border-radius: 4px;
  }

  .dp__menu_inner {
    background: #f7f7f7;
    border-radius: 4px;
  }

  .dp__overlay_action {
    background: #f7f7f7;

    &:hover {
      background: #00b163;
      color: #001931;
    }
  }

  .dp__overlay {
    background: #f7f7f7;
    color: #001931;

    .dp__overlay_cell {
      &:hover {
        background: color-mix(in srgb, #00b163, transparent 50%);
        color: #001931;
      }
    }
  }

  .dp__overlay_cell_active {
    background: #00b163;
    color: #001931;
  }

  .dp__action_row {
    background: #f7f7f7;

    .dp__action_cancel {
      padding: 10px;
      margin: 0;
      border: none;
      flex: 1;
      border-radius: 4px;
      cursor: pointer;
      transition: 300ms ease;
      font-size: 14px;
      font-weight: 500;
      border: 1px solid #0019311a;
      margin-right: 10px;
      background: transparent;
      color: #001931;

      &:hover {
        background: #00b163;
      }
    }

    .dp__action_select {
      padding: 10px;
      margin: 0;
      border: none;
      flex: 1;
      border-radius: 4px;
      cursor: pointer;
      transition: 300ms ease;
      font-size: 14px;
      font-weight: 500;
      background: #00b163;
      color: #f7f7f7;

      &:hover {
        background: hsl(from #00b163 h s calc(l - 5));
      }
    }
  }

  .dp__menu-inner {
    padding: 10px;
  }

  .dp__pointer {
    background: #f7f7f7;
  }

  .dp__inner_nav {
    background: #f7f7f7;
    border-radius: 4px;
  }

  .dp__month_year_wrap {
    gap: 4px;
    .dp__month_year_select {
      background: transparent;
      border-radius: 4px;
      height: 26px;
      font-size: 14px;
      font-weight: 700;
      transition: 300ms ease;

      &:hover {
        background: #00b163 !important;
        color: #fff !important;
      }
    }
  }

  .dp__cell_inner {
    min-width: 100% !important;
    border-radius: 0px;
    font-size: 12px !important;
    font-weight: 500;
  }

  .dp__range_start,
  .dp__range_end {
    background: #00b163 !important;
  }

  .dp__range_between {
    background: #fff !important;
    color: #00b163 !important;
  }

  .dp--arrow-btn-nav {
    padding: 0px !important;
    transition: 300ms ease;

    &:hover {
      color: #001931 !important;
    }
  }

  .dp__calendar_item,
  .dp__calendar_header_item {
    color: #001931 !important;
  }

  .dp__calendar_header_item {
    height: 22px;
  }

  .dp__calendar_item {
    border: 2px solid transparent;
    transition: 300ms ease;
    max-width: 52px;
    border-radius: 4px;
    overflow: hidden;

    &:hover {
      border: 2px solid #00b163;
      background: transparent;
      color: #001931 !important;
    }
  }

  .dp__date_hover {
    transition: 300ms ease;

    &:hover {
      color: #001931 !important;
    }
  }

  .dp__today {
    border: none;
  }

  .dp__calendar_row {
    margin: 0;
  }

  .dp__calendar_header_separator {
    display: none;
  }

  .dp__calendar_header {
    font-size: 12px;
    font-weight: 500;
    opacity: 0.3;
    margin-bottom: 10px;
  }
}
</style>
