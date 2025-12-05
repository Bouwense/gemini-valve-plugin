<template>
  <div class="ccb-singlepage-sidebar">
    <div class="ccb-singlepage-add-widget-container">
      <div class="ccb-singlepage-widgets-options">
        <div class="ccb-singlepage-widget-option">
          <span>{{ translations.type }}</span>
          <div class="ccb-singlepage-widget-option-select">
            <select v-model="selectedWidget">
              <option value="" selected>{{ translations.selectWidget }}</option>
              <option :value="widget.key" v-for="widget in getAvailableWidgets">
                {{ widget.title }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <button
        class="ccb-singlepage-add-widget"
        :class="{
          'ccb-is-loading-for-settings':
            analyticsStore.isLoading || !selectedWidget,
        }"
        @click.prevent="addWidget"
      >
        {{ translations.addWidget }}
      </button>
    </div>
    <div class="ccb-dashboard-row">
      <template v-for="(cart, i) in getSingleCarts" :key="i">
        <LargeWidget
          v-if="
            getWidgetsSolidPageSettings[cart.key || ''] && cart.size === 'large'
          "
          :cart="cart"
          :items="items"
          @change="handleWidgetSettingsUpdate"
          :currency="analyticsStore.getCurrency"
        />
        <SmallWidget
          v-if="
            getWidgetsSolidPageSettings[cart.key || ''] && cart.size === 'small'
          "
          :cart="cart"
          :items="items"
          @change="handleWidgetSettingsUpdate"
          :currency="analyticsStore.getCurrency"
        />
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useAnalyticsStore } from "@/analytics/store/analytics/useAnalyticsStore";
import { LargeWidget } from "@/analytics/shared/ui/components/LargeWidget";
import { SmallWidget } from "@/analytics/shared/ui/components/SmallWidget";
import { useAdminTranslationsStore } from "@/analytics/store/analytics/translationsStore";

const analyticsStore = useAnalyticsStore();
const translationsStore = useAdminTranslationsStore();

const translations = computed(() => translationsStore.getTranslations);

const selectedWidget = ref<string>("");

const getSingleCarts = computed(() => {
  return analyticsStore.getSingleCarts;
});

const items = ref<{ value: string; label: string }[]>([
  { value: "hide", label: translations.value.hide },
  { value: "small/large", label: translations.value.smallLarge },
  { value: "delete", label: translations.value.deleted },
]);

const getWidgetsSolidPageSettings = computed(() => {
  return analyticsStore.getWidgetsPageSettings;
});

const getAvailableWidgets = computed(() => {
  const singlePageCartsWidgetKeys = analyticsStore.getSingleCarts.map(
    (widget) => widget.key,
  );

  return analyticsStore.getAvailableWidgets.filter(
    (widget) => !singlePageCartsWidgetKeys.includes(widget.key),
  );
});

const addWidget = () => {
  if (selectedWidget.value?.trim()) {
    const exists = analyticsStore.getSingleCarts.find((widget) => {
      return widget.key === selectedWidget.value.trim();
    });

    if (exists) {
      return;
    }

    const widget = getAvailableWidgets.value.find(
      (widget) => widget.key === selectedWidget.value,
    );

    if (widget) {
      widget.size = "large";
      widget.options = {
        ...widget.options,
        chart: {
          ...widget.options.chart,
        },
      };

      const allWidgets = [...analyticsStore.getSingleCarts, widget];
      const getPageSettings = analyticsStore.getSolidPageSettings;
      if (!getPageSettings["widgets"]) {
        getPageSettings["widgets"] = {};
      }

      getPageSettings["widgets"][widget.key || ""] = true;
      analyticsStore.updatePageSettings(getPageSettings);
      analyticsStore.updateSingleCarts(allWidgets);
      analyticsStore.saveWidgets();
      analyticsStore.savePageSettings();
    }
  }

  selectedWidget.value = "";
};

const handleWidgetSettingsUpdate = (action: string, key: string) => {
  const widget = analyticsStore.getSingleCarts.find(
    (widget) => widget.key === key,
  );

  if (!widget) {
    return;
  }

  if (action === "hide") {
    const singlePageSettings = analyticsStore.getSolidPageSettings;
    if (widget.key && widget.key in singlePageSettings["widgets"]) {
      singlePageSettings["widgets"][widget.key] = false;
      analyticsStore.updatePageSettings(singlePageSettings);
      analyticsStore.saveWidgets();
      analyticsStore.savePageSettings();
    }
  }

  if (action === "small/large") {
    widget.size = widget.size === "large" ? "small" : "large";
    analyticsStore.updateSingleCarts(analyticsStore.getSingleCarts);
    analyticsStore.saveWidgets();
  }

  if (action === "delete") {
    const singlePageSettings = analyticsStore.getSolidPageSettings;
    if (widget.key && widget.key in singlePageSettings["widgets"]) {
      delete singlePageSettings["widgets"][widget.key];
      const allWidgets = analyticsStore.getSingleCarts.filter(
        (widget) => widget.key !== key,
      );
      analyticsStore.updateSingleCarts(allWidgets);
      analyticsStore.updatePageSettings(singlePageSettings);
      analyticsStore.saveWidgets();
      analyticsStore.savePageSettings();
    }
  }
};
</script>

<style scoped lang="scss">
.ccb-dashboard-row {
  flex-wrap: wrap;
}

.ccb-singlepage-sidebar {
  width: 280px;
  min-width: 220px;
  max-width: 320px;
  display: flex;
  flex-direction: column;
  gap: 16px;

  .ccb-singlepage-add-widget-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
    box-sizing: border-box;
    padding: 0 8px;
  }

  .ccb-singlepage-widgets-options {
    padding: 26px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    display: flex;
    width: 100%;
    box-sizing: border-box;
    flex: 1;
    flex-direction: column;
    row-gap: 16px;

    .ccb-singlepage-widget-option {
      width: 100%;

      span {
        font-size: 14px;
        font-weight: 500;
        color: #333333;
        text-indent: 3px;
        display: block;
        margin-bottom: 4px;
      }

      select {
        width: 100%;
        display: flex;
        align-items: center;
        background: #ffffff;
        padding: 0 10px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        font-size: 14px;
        font-weight: 400;
        color: #333333;
        cursor: pointer;
        height: 40px;
        outline: none;
        box-shadow: none;
      }
    }
  }
}

.ccb-singlepage-add-widget {
  width: 100%;
  padding: 10px 0;
  color: rgba(22, 36, 50, 0.85);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
  border: none;
  outline: none;

  border-radius: 8px;
  background: rgba(22, 36, 50, 0.1);

  &:hover {
    background: rgba(22, 36, 50, 0.15);
  }
}

.ccb-dashboard-card-wrapper {
  min-height: 90px !important;
}

@media (max-width: 900px) {
  .ccb-singlepage-layout {
    flex-direction: column;
    gap: 16px;
  }
  .ccb-singlepage-sidebar {
    width: 100%;
    max-width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 8px;
  }
}

.ccb-singlepage-sidebar {
  .ccb-dashboard-card {
    row-gap: 16px !important;
    width: 100% !important;
  }
}
</style>
