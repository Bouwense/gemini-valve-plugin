<template>
  <div class="ccb-dashboard-card ccb-dashboard-card-chart ccb-bar-chart">
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
      <div class="ccb-dashboard-large-chart">
        <VueApexCharts
          type="bar"
          height="320"
          :options="barChartOptions"
          :series="barChartSeries"
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

const barChartOptions = ref({
  chart: {
    type: "bar",
    toolbar: { show: false },
  },
  plotOptions: {
    bar: {
      horizontal: true,
      barHeight: "16px",
      borderRadius: 6,
      distributed: false,
    },
  },
  dataLabels: {
    enabled: false,
  },
  xaxis: {
    categories: chart.value.categories,
    min: Math.min(...chart.value.series),
    max: Math.max(...chart.value.series),
    labels: {
      style: {
        colors: "#888",
        fontSize: "13px",
      },
    },
    axisBorder: { show: true, offsetX: -10 },
    axisTicks: { show: true },
  },
  yaxis: {
    labels: {
      style: {
        colors: "#222",
        fontSize: "14px",
      },
    },
  },
  grid: {
    borderColor: "#f1f1f1",
    xaxis: { lines: { show: false } },
    yaxis: { lines: { show: false } },
    padding: { left: 0, right: 0 },
  },
  colors: ["#27ae60"],
  tooltip: { enabled: true },
  legend: { show: false },
});

const barChartSeries = ref([
  {
    name: chart.value.title,
    data: chart.value.series,
  },
]);

const isLoading = computed(() => {
  const store = useAnalyticsStore();
  return store.isLoading;
});
</script>

<style lang="scss"></style>
