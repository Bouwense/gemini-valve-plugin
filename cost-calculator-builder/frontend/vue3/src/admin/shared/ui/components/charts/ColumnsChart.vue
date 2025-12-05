<template>
  <div class="ccb-dashboard-card ccb-dashboard-card-chart ccb-columns-chart">
    <Skeleton v-if="isLoading"></Skeleton>
    <div
      class="ccb-dashboard-card-wrapper"
      :class="{ 'ccb-is-loading': isLoading }"
    >
      <div class="ccb-dashboard-chart-header">
        <div class="ccb-dashboard-chart-controls">
          <span>{{ chart.title }}</span>
        </div>
        <div class="ccb-dashboard-chart-periods" v-if="showGrouping">
          <CustomSelect
            :items="periods"
            :current="period"
            placeholder=""
            @update="handlePeriodChange"
            :class="{ 'ccb-is-loading': isLoading }"
          />
        </div>
      </div>
      <div class="ccb-dashboard-large-chart">
        <VueApexCharts
          type="bar"
          height="100%"
          width="100%"
          :options="daysBarChartOptions"
          :series="daysBarChartSeries"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, toRefs, onMounted } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { IChartData } from "@/admin/shared/types/analytics/api/response.type";
import { useAnalyticsStore } from "@/admin/store/analytics/useAnalyticsStore";
import {
  getPeriods,
  getAllowedOption,
  AllowedGroupingsKeys,
} from "@/admin/shared/utils/periods";
import { Skeleton } from "@/admin/shared/ui/components/Skeleton";
import { CustomSelect } from "@/admin/shared/ui/components/CustomSelect";

type Props = {
  chart: IChartData;
  page: "dashboard" | "single";
};

const props = defineProps<Props>();
const { chart, page } = toRefs(props);
const period = ref<AllowedGroupingsKeys>("week");

const daysBarChartOptions = ref({
  chart: {
    type: "bar",
    height: 260,
    toolbar: { show: false },
  },
  plotOptions: {
    bar: {
      horizontal: false,
      columnWidth: "37px",
      borderRadius: 6,
      distributed: false,
      dataLabels: {
        position: "center",
      },
    },
  },
  dataLabels: {
    enabled: true,
    style: {
      fontSize: "13px",
      colors: ["#fff"],
      fontWeight: 500,
    },
  },
  xaxis: {
    categories: chart.value.categories,
    labels: {
      style: {
        colors: "#888",
        fontSize: "13px",
      },
    },
    axisBorder: { show: true, offsetY: 5 },
    axisTicks: { show: false },
  },
  yaxis: {
    show: false,
  },
  grid: {
    borderColor: "#f1f1f1",
    xaxis: { lines: { show: false } },
    yaxis: { lines: { show: true } },
    padding: { left: 22, right: 22 },
  },
  colors: ["#27ae60"],
  tooltip: { enabled: true },
  legend: { show: false },
});

const daysBarChartSeries = ref([
  {
    name: chart.value.title,
    data: chart.value.series,
  },
]);

const periods = computed(() => {
  const store = useAnalyticsStore();
  const innerPeriods = getPeriods(store.getPeriod);
  if (!innerPeriods.map((item) => item.value).includes(period.value)) {
    period.value = getAllowedOption(innerPeriods);
  }

  return innerPeriods;
});

const handlePeriodChange = (key: string) => {
  const store = useAnalyticsStore();
  const selectedPeriod = key;

  store.updateChartsGrouping(
    chart.value.key,
    selectedPeriod as AllowedGroupingsKeys,
  );
};

const showGrouping = computed(() => {
  const store = useAnalyticsStore();
  return (
    periods.value.length > 0 &&
    chart.value.key in store.getDashboardChartsGrouping
  );
});

const isLoading = computed(() => {
  const store = useAnalyticsStore();
  return store.isLoading;
});

onMounted(() => {
  const store = useAnalyticsStore();
  period.value =
    page.value === "dashboard"
      ? store.getDashboardChartsGrouping[chart.value.key]
      : store.getSingleChartsGrouping[chart.value.key];
});
</script>

<style lang="scss"></style>
