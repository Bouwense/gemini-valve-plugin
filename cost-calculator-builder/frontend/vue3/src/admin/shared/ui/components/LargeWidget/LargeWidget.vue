<template>
  <div class="ccb-dashboard-card card-wrapper">
    <Skeleton v-if="analyticsStore.isLoading" />
    <div
      class="ccb-dashboard-card-wrapper"
      :class="{ 'is-loading': analyticsStore.isLoading }"
    >
      <div class="ccb-dashboard-content">
        <div class="ccb-dashboard-content-header">
          <div class="ccb-dashboard-title">
            {{ cart.title }}
            <span
              class="ccb-dashboard-currency"
              v-if="currency && cart.key === 'revenue'"
            >
              ({{ currency }})
            </span>
          </div>
          <div class="ccb-dashboard-sub">
            <WidgetSettings
              :items="items"
              @update="updateWidgetSettings"
              :current="cart.key"
            />
          </div>
        </div>
        <div class="ccb-dashboard-content-footer">
          <div class="ccb-dashboard-value">
            {{ formatValue(cart.value) }}
          </div>
        </div>
      </div>
      <div class="ccb-dashboard-chart">
        <VueApexCharts
          :options="cart.options"
          :series="cart.series"
          width="100%"
          height="24"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { useAnalyticsStore } from "@/admin/store/analytics/useAnalyticsStore";
import { WidgetSettings } from "@/admin/shared/ui/components/WidgetSettings";
import { Skeleton } from "@/admin/shared/ui/components/Skeleton";
import { IAnalyticsCarts } from "@/admin/shared/types/analytics/analyticsCarts.type";
import VueApexCharts from "vue3-apexcharts";

const analyticsStore = useAnalyticsStore();

const emit = defineEmits<{
  (e: "change", action: string, key: string): void;
}>();

const props = defineProps<{
  cart: IAnalyticsCarts;
  items: { value: string; label: string }[];
  currency?: string;
}>();

const { cart, items } = toRefs(props);

const updateWidgetSettings = (action: string, key: string) => {
  emit("change", action, key);
};

const formatValue = computed(() => {
  return (number: string) => {
    const num = parseFloat(number);
    if (num > 1000) {
      return (num / 1000).toFixed(1) + "k";
    }
    return num;
  };
});
</script>

<style lang="scss" scoped>
.ccb-dashboard-card {
  min-height: 130px !important;
  height: 130px !important;
}
</style>
