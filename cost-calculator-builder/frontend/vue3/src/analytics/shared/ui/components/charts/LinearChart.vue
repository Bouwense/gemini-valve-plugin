<template>
  <div class="ccb-dashboard-card ccb-dashboard-card-chart ccb-dashboard-col-12">
    <Skeleton v-if="isLoading"></Skeleton>
    <div
      class="ccb-dashboard-card-wrapper"
      :class="{ 'ccb-is-loading': isLoading }"
    >
      <div
        class="ccb-dashboard-chart-header"
        :class="{ 'ccb-is-loading': isLoading }"
      >
        <div class="ccb-dashboard-chart-controls">
          <span>{{ chart.title }}</span>
        </div>
        <div class="ccb-dashboard-chart-periods" v-if="showGrouping">
          <CustomSelect
            :items="periods"
            :current="period"
            placeholder=""
            @update="handlePeriodChange"
          />
        </div>
      </div>
      <div class="ccb-dashboard-large-chart">
        <VueApexCharts
          type="line"
          height="100%"
          :options="lineChartOptions"
          :series="lineChartSeries"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, toRefs } from "vue";
import VueApexCharts from "vue3-apexcharts";
import { IChartData } from "@/analytics/shared/types/analytics/api/response.type";
import { useAnalyticsStore } from "@/analytics/store/analytics/useAnalyticsStore";
import {
  getPeriods,
  getAllowedOption,
  AllowedGroupingsKeys,
} from "@/analytics/shared/utils/periods";
import { Skeleton } from "@/analytics/shared/ui/components/Skeleton";
import { CustomSelect } from "@/analytics/shared/ui/components/CustomSelect";
import { useAdminTranslationsStore } from "@/analytics/store/analytics/translationsStore";

const props = defineProps<{
  chart: IChartData;
  page: "dashboard" | "single";
}>();

const { chart, page } = toRefs(props);
const period = ref<AllowedGroupingsKeys>("hour");

const minValues = computed(() => {
  return Math.min(
    ...(chart.value.series as number[]),
    ...(chart.value.extraSeries as number[]),
  );
});

const maxValues = computed(() => {
  return Math.max(
    ...(chart.value.series as number[]),
    ...(chart.value.extraSeries as number[]),
  );
});

const lineChartOptions = ref({
  chart: {
    type: "line",
    height: 350,
    toolbar: {
      show: true,
      tools: {
        download: false,
        selection: false,
        zoom: false,
        zoomin: false,
        zoomout: false,
        pan: false,
        reset: false,
      },
    },
  },
  stroke: {
    width: [2, 2],
    curve: "smooth",
  },
  colors: ["#27ae60", "#2980b9"],
  grid: {
    borderColor: "#f1f1f1",
    padding: {
      top: 10,
      right: 0,
      bottom: 0,
      left: 10,
    },
  },
  markers: {
    size: 4,
    colors: ["#27ae60", "#2980b9"],
    strokeColors: "#fff",
    strokeWidth: 2,
    hover: {
      size: 6,
    },
  },
  xaxis: {
    categories: chart.value.categories,
    labels: {
      style: {
        colors: "#888",
        fontSize: "12px",
      },
    },
    axisBorder: {
      show: true,
    },
    axisTicks: {
      show: true,
    },
  },
  yaxis: [
    {
      title: {
        style: {
          color: "#27ae60",
        },
      },
      labels: {
        style: {
          colors: "#888",
        },
      },
      min: minValues.value,
      max: maxValues.value,
    },
  ],
  legend: {
    position: "top",
    horizontalAlign: "center",
    floating: true,
    offsetY: -50,
    offsetX: 0,
  },
});

const lineChartSeries = computed(() => {
  const translations = useAdminTranslationsStore();
  if (chart.value.extraSeries && chart.value.extraSeries.length > 0) {
    return [
      {
        name: translations.getTranslations.interactions,
        type: "line",
        data: chart.value.series,
      },
      {
        name: translations.getTranslations.orders,
        type: "line",
        data: chart.value.extraSeries,
      },
    ];
  }
  return [
    {
      name: translations.getTranslations.revenue,
      type: "line",
      data: chart.value.series,
    },
  ];
});

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

<style scoped lang="scss"></style>
