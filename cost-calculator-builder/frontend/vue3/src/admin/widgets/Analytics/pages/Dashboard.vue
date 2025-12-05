<template>
  <div class="ccb-dashboard-row">
    <div
      class="ccb-dashboard-card"
      v-for="(cart, i) in dashboardCarts"
      :key="i"
    >
      <Skeleton v-if="isLoading" />
      <div
        class="ccb-dashboard-card-wrapper"
        :class="{ 'is-loading': isLoading }"
      >
        <div class="ccb-dashboard-content">
          <div class="ccb-dashboard-content-header">
            <div class="ccb-dashboard-title">{{ cart.title }}</div>
            <div class="ccb-dashboard-sub" v-if="cart.sub">
              {{ cart.sub }}
            </div>
          </div>
          <div class="ccb-dashboard-content-footer">
            <div class="ccb-dashboard-value">{{ formatValue(cart.value) }}</div>
            <div
              class="ccb-dashboard-extra"
              v-if="cart.extra"
              v-html="cart.extra"
            ></div>
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
  </div>

  <div class="ccb-dashboard-charts-container">
    <template v-for="chart in dashboardCharts" :key="chart.key">
      <component
        :is="currentComponent(chart.type)"
        :chart="chart"
        page="dashboard"
      />
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { useAnalyticsStore } from "@/admin/store/analytics/useAnalyticsStore";
import { Skeleton } from "@/admin/shared/ui/components/Skeleton";

const analyticsStore = useAnalyticsStore();

const dashboardCarts = computed(() => analyticsStore.getDashboardCarts);
const dashboardCharts = computed(() => analyticsStore.getDashboardCharts);

const currentComponent = computed(() => {
  return (chartType: string) => {
    if (chartType === "bar") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/BarChart.vue"),
      );
    } else if (chartType === "columns") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/ColumnsChart.vue"),
      );
    } else if (chartType === "line") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/LinearChart.vue"),
      );
    } else if (chartType === "pie") {
      return defineAsyncComponent(
        () => import("@/admin/shared/ui/components/charts/PieChart.vue"),
      );
    }
  };
});

const formatValue = computed(() => {
  return (number: string) => {
    const num = parseFloat(number);
    if (num > 1000) {
      return (num / 1000).toFixed(1) + "k";
    }
    return num;
  };
});

const isLoading = computed(() => analyticsStore.isLoading);

onMounted(() => {
  setTimeout(() => {
    // analyticsStore.setLoading(false);
  }, 3000);
});
</script>

<style lang="scss">
.ccb-dashboard-row {
  display: flex;
  gap: 16px;
  justify-content: flex-start;
  width: 100%;
  box-sizing: border-box;
  padding: 0 8px;
}

.ccb-dashboard-card {
  flex: 1 1 0;
  width: 100px;
  height: 140px;
  background: #f7f9fb;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  padding: 16px;
  box-sizing: border-box;
  position: relative;
  display: flex;
  flex-direction: column;
}

.ccb-dashboard-card-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.ccb-dashboard-chart {
  width: 100%;
  height: 24px;
  padding: 0;
}

.ccb-dashboard-card-wrapper {
  width: 100%;
}

.ccb-dashboard-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  width: 100%;
  justify-content: flex-start;
  row-gap: 10px;
}

.ccb-dashboard-content-header {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  width: 100%;
  align-items: center;
}

.ccb-dashboard-content-footer {
  display: flex;
  justify-content: right;
  justify-content: space-between;
}

.ccb-dashboard-title {
  font-size: 13px;
  color: #888;
  margin-bottom: 2px;
}

.ccb-dashboard-value {
  display: flex;
  align-items: center;
  font-size: 32px;
  font-weight: bold;
  color: #222;
  margin-bottom: 2px;
}
.ccb-dashboard-sub {
  font-size: 13px;
  color: #888;
  margin-bottom: 2px;
}
.ccb-dashboard-extra {
  font-size: 15px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  row-gap: 3px;

  * {
    text-align: right;
  }
}

.ccb-dashboard-chart-container {
  margin-top: 24px;
  background: #f7f9fb;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  padding: 16px;
  box-sizing: border-box;
}

.ccb-dashboard-chart-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
  width: 100%;
}

.ccb-dashboard-chart-controls {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: space-between;
  width: 100%;

  select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    color: #333;
    font-size: 14px;
    cursor: pointer;

    &:focus {
      outline: none;
      border-color: #27ae60;
    }
  }

  .vs-text {
    color: #888;
    font-size: 14px;
  }
}

.ccb-dashboard-chart-type {
  select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    color: #333;
    font-size: 14px;
    cursor: pointer;

    &:focus {
      outline: none;
      border-color: #27ae60;
    }
  }
}

.ccb-dashboard-large-chart {
  width: 100%;
  height: 350px;
}

.ccb-dashboard-row-charts {
  margin-top: 24px;
  gap: 24px;
}

.ccb-dashboard-card-chart {
  flex: 1 1 0;
  min-width: 0;
  background: #f7f9fb;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  padding: 32px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  height: 100%;
}

.ccb-dashboard-large-chart {
  width: 100%;
  height: 320px;
}

.ccb-dashboard-large-chart-pie {
  display: flex;
  align-items: center;
  justify-content: center;
}

.ccb-dashboard-large-chart-table {
  width: 100%;
  height: 260px;
  display: flex;
  align-items: flex-start;
  justify-content: flex-start;
  overflow-x: auto;
  padding-top: 8px;
}

.ccb-dashboard-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: transparent;
  font-size: 15px;
  color: #222;
}
.ccb-dashboard-table th {
  text-align: left;
  font-size: 14px;
  color: #888;
  font-weight: 600;
  background: #f7f9fb;
  padding: 6px 12px;
  border-bottom: 1px solid #e5e7eb;
}
.ccb-dashboard-table td {
  padding: 6px 12px;
  border-bottom: 1px solid #f1f1f1;
  white-space: nowrap;
}

.ccb-dashboard-card-chart {
  width: 100%;
  min-width: 100%;
}

.ccb-dashboard-charts-container {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 0 8px;
  margin-top: 16px;
}

.ccb-dashboard-chart-periods-select {
  display: flex;
  align-items: center;
  width: max-content !important;
  min-width: unset !important;
  max-width: unset !important;
  padding: 0px 15px !important;
  border: 1px solid #ddd !important;
  border-radius: 4px !important;
  background: white !important;
  color: #222 !important;
  font-size: 14px !important;
  height: 40px !important;
  position: relative;
  z-index: 9;
  outline: none !important;
  box-shadow: none !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  appearance: none !important;
  cursor: pointer !important;
}

.ccb-dashboard-charts-container-inner {
  position: relative;
  width: 100%;
  height: 100%;
}
</style>
