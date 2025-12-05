<template>
  <div class="ccb-dashboard-card ccb-dashboard-card-chart ccb-pie-chart">
    <Skeleton v-if="isLoading"></Skeleton>
    <div
      class="ccb-dashboard-card-wrapper"
      :class="{ 'ccb-is-loading': isLoading }"
    >
      <div class="ccb-dashboard-chart-header">
        <div class="ccb-dashboard-chart-controls">
          <span>{{ chart.title }}</span>
        </div>
      </div>
      <div class="ccb-dashboard-large-chart ccb-dashboard-large-chart-pie">
        <VueApexCharts
          type="pie"
          height="100%"
          :options="pieChartOptions"
          :series="pieChartSeries"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, toRefs } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { IChartData } from "@/admin/shared/types/analytics/api/response.type";
import { useAnalyticsStore } from "@/admin/store/analytics/useAnalyticsStore";
import { Skeleton } from "@/admin/shared/ui/components/Skeleton";

type Props = {
  chart: IChartData;
  page: "dashboard" | "single";
};

const props = defineProps<Props>();
const { chart } = toRefs(props);

const pieLabels = chart.value.categories;
const pieData = chart.value.series;

const pieChartOptions = ref({
  chart: {
    type: "pie",
    width: 250,
  },
  labels: pieLabels,
  legend: {
    show: true,
    position: "left",
  },
  dataLabels: {
    enabled: true,
    formatter(val: string, opt: any) {
      const idx = opt.seriesIndex;
      return `${parseInt(val)}% (${pieData[idx]})`;
    },
    style: {
      fontSize: "14px",
      fontWeight: 500,
      position: "center",
    },
  },
  tooltip: { enabled: true },
  responsive: [
    {
      breakpoint: 1440,
      options: {
        chart: {
          width: 300,
          position: "center",
        },
        legend: {
          position: "top",
        },
      },
    },
  ],
});

const pieChartSeries = ref(pieData);

const isLoading = computed(() => {
  const store = useAnalyticsStore();
  return store.isLoading;
});
</script>

<style>
.ccb-pie-chart {
  .vue-apexcharts {
    width: 100%;
    .apexcharts-canvas {
      width: 100% !important;
      display: flex;
      justify-content: center;
    }
  }
}
</style>
