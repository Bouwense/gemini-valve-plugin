<template>
  <div class="ccb-singlepage-content">
    <template v-for="chart in getSingleCharts" :key="chart.key">
      <component
        :is="getChartComponent(chart.type)"
        :key="chart.key"
        :chart="chart"
        page="single"
        v-if="getChartsSolidPageSettings[chart.key]"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from "vue";
import { useAnalyticsStore } from "@/admin/store/analytics/useAnalyticsStore";

const analyticsStore = useAnalyticsStore();

const getSingleCharts = computed(() => {
  return analyticsStore.getSingleCharts;
});

const getChartComponent = computed(() => {
  return (type: string) => {
    if (type === "bar") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/BarChart.vue"),
      );
    } else if (type === "columns") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/ColumnsChart.vue"),
      );
    } else if (type === "line") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/LinearChart.vue"),
      );
    } else if (type === "pie") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/PieChart.vue"),
      );
    }
  };
});

const getChartsSolidPageSettings = computed(() => {
  return analyticsStore.getChartsPageSettings;
});
</script>

<style scoped lang="scss">
.ccb-singlepage-content {
  flex: 1 1 0;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
</style>
