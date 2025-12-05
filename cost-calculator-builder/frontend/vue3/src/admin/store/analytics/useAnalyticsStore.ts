import { defineStore } from "pinia";
import { AnalyticsPages } from "@/admin/shared/types/analytics/analyticsPages.type";
import { defineAsyncComponent, markRaw } from "vue";
import { fetchCalculators } from "@/admin/shared/api/fetchCalculators";
import { fetchAnalyticsData } from "@/admin/shared/api/fetchAnalyticsData";
import { IAnalyticsCarts } from "@/admin/shared/types/analytics/analyticsCarts.type";
import { generateCart } from "@/admin/store/analytics/cart.factory";
import { generateChart } from "@/admin/store/analytics/chart.factory";
import {
  IFetchCalculatorsResponse,
  IFetchDashboardResponse,
  IChartData,
  ChartType,
} from "@/admin/shared/types/analytics/api/response.type";
import { AllowedGroupingsKeys } from "@/admin/shared/utils/periods";

export type Period =
  | "all"
  | "today"
  | "yesterday"
  | "last_7_days"
  | "last_30_days"
  | "last_90_days"
  | "last_year";

interface IAnalyticsStore {
  calculators: AnalyticsPages[];
  currentPage: AnalyticsPages | null;
  dashboard: {
    carts: IAnalyticsCarts[];
    charts: IChartData[];
  };
  single: {
    carts: IAnalyticsCarts[];
    charts: IChartData[];
  };
  period: Period;
  dashboardChartsGrouping: Record<string, AllowedGroupingsKeys>;
  singleChartsGrouping: Record<string, AllowedGroupingsKeys>;
  pageSettings: Record<string, Record<string, boolean>>;
  loading: boolean;
  availableWidgets: IAnalyticsCarts[];
  status: "all" | "complete" | "pending";
  currency: string;
}

export const useAnalyticsStore = defineStore("analytics", {
  state: (): IAnalyticsStore => ({
    dashboard: {
      carts: [],
      charts: [],
    },
    single: {
      carts: [],
      charts: [],
    },
    calculators: [],
    period: "all",
    dashboardChartsGrouping: {
      interactions_line_chart: "week",
      weekdays_columns_chart: "week",
      revenue_line_chart_chart: "week",
    },
    singleChartsGrouping: {
      interactions_line_chart: "week",
    },
    currentPage: null,
    pageSettings: {},
    loading: true,
    availableWidgets: [],
    status: "all",
    currency: "",
  }),

  getters: {
    isLoading: (state): boolean => {
      return state.loading;
    },

    getDashboardCarts: (state): IAnalyticsCarts[] => {
      return state.dashboard.carts;
    },

    getDashboardCharts: (state): IChartData[] => {
      return state.dashboard.charts;
    },

    getSingleCarts: (state): IAnalyticsCarts[] => {
      return state.single.carts;
    },

    getSingleCharts: (state): IChartData[] => {
      return state.single.charts;
    },

    getCalculators: (state): AnalyticsPages[] => {
      return state.calculators;
    },

    getPeriod: (state): Period => {
      return state.period;
    },

    getDashboardChartsGrouping: (
      state,
    ): Record<string, AllowedGroupingsKeys> => {
      return state.dashboardChartsGrouping;
    },

    getSingleChartsGrouping: (state): Record<string, AllowedGroupingsKeys> => {
      return state.singleChartsGrouping;
    },

    getCurrentPage: (state): AnalyticsPages | null => {
      return state.currentPage;
    },

    getSolidPageSettings: (state): Record<string, Record<string, boolean>> => {
      return state.pageSettings;
    },

    getChartsPageSettings: (state): Record<string, boolean> => {
      return state.pageSettings["charts"] || {};
    },

    getWidgetsPageSettings: (state): Record<string, boolean> => {
      return state.pageSettings["widgets"] || {};
    },

    getPageSettings: (
      state,
    ): Record<string, { key: string; title: string; value: boolean }[]> => {
      const result: Record<
        string,
        { key: string; title: string; value: boolean }[]
      > = {
        charts: [],
        widgets: [],
      };

      const chartsKeys = state.single.charts.map((chart) => chart.key);
      for (const key in state.pageSettings["charts"]) {
        if (chartsKeys.includes(key)) {
          const field = state.single.charts.find((chart) => chart.key === key);
          result["charts"].push({
            key: key,
            title: field?.title || "",
            value: state.pageSettings["charts"][key],
          });
        }
      }

      for (const key in state.pageSettings["widgets"]) {
        result["widgets"].push({
          key: key,
          title:
            state.availableWidgets.find((widget) => widget.key === key)
              ?.title || "",
          value: state.pageSettings["widgets"][key],
        });
      }

      return result;
    },

    getAvailableWidgets: (state): IAnalyticsCarts[] => {
      return state.availableWidgets;
    },

    getStatus: (state): "all" | "complete" | "pending" => {
      return state.status;
    },

    getCurrency: (state): string => {
      return state.currency;
    },
  },

  actions: {
    async init(page: AnalyticsPages) {
      this.loading = true;
      this.currentPage = page;
      await this.fetchCalculatorsHandler();
      await this.fetchDashboardDataHandler();
    },

    async fetchCalculatorsHandler() {
      fetchCalculators<IFetchCalculatorsResponse>({
        action: "ccb_analytics_get_calculators",
        nonce: window?.ccb_nonces?.ccb_analytics_get_calculators || "",
      }).then((res) => {
        if (res?.success && res?.data?.calculators) {
          this.calculators = res.data.calculators.map((calculator) => ({
            label: calculator.label,
            slug: calculator.id,
            icon: "",
            component: markRaw(
              defineAsyncComponent(
                () => import("@/admin/widgets/Analytics/pages/SinglePage.vue"),
              ),
            ),
            id: parseInt(calculator.id),
            deleted: calculator.deleted,
          }));
        }
      });
    },

    async fetchDashboardDataHandler(extra?: string) {
      this.loading = true;
      fetchAnalyticsData<IFetchDashboardResponse>({
        action: "ccb_get_analytics_data",
        nonce: window?.ccb_nonces?.ccb_get_analytics_data || "",
        data: {
          period: this.period,
          ...this.dashboardChartsGrouping,
          extra,
          status: this.status,
        },
      }).then((res) => {
        if (res?.success) {
          if (res?.data?.carts) {
            this.dashboard.carts = res.data.carts.map((item) =>
              generateCart({
                title: item.title,
                value: item.value.toString(),
                sub: item?.sub || "",
                extra: item.extra || "",
                series: item.series || [{ name: item.title, data: [] }],
                size: "large",
              }),
            );
          }

          if (res?.data?.charts) {
            this.dashboard.charts = res.data.charts.map((item) =>
              generateChart(item),
            );
          }
        }

        setTimeout(() => {
          this.loading = false;
        }, 1000);
      });
    },

    updateChartType(key: string, newType: ChartType) {
      this.dashboard.charts = this.dashboard.charts.map((c) => {
        if (c.key === key) {
          c.type = newType;
        }
        return c;
      });
    },

    updatePeriod(period: Period, extra?: string) {
      if (this.period !== period) {
        this.period = period;
        if (this.currentPage?.id === 0) {
          this.fetchDashboardDataHandler(extra);
        } else {
          this.fetchSingleDataHandler(this.currentPage?.id || 0, extra);
        }
      }
    },

    updateChartsGrouping(chartId: string, newGrouping: AllowedGroupingsKeys) {
      if (this.currentPage?.id !== 0) {
        this.singleChartsGrouping[chartId] = newGrouping;
        this.fetchSingleDataHandler(this.currentPage?.id || 0);
      } else {
        if (
          chartId in this.dashboardChartsGrouping &&
          this.dashboardChartsGrouping[chartId] !== newGrouping
        ) {
          this.dashboardChartsGrouping[chartId] = newGrouping;
          this.fetchDashboardDataHandler();
        }
      }
    },

    async fetchSingleDataHandler(calc_id: number, extra?: string) {
      this.loading = true;
      fetchAnalyticsData<IFetchDashboardResponse>({
        action: "ccb_single_calc_analytics",
        nonce: window?.ccb_nonces?.ccb_single_calc_analytics || "",
        data: {
          period: this.period,
          calc_id: calc_id,
          ...this.singleChartsGrouping,
          extra,
          status: this.status,
        },
      }).then((res) => {
        if (res?.success) {
          if (res?.data?.carts) {
            this.setCurrency(res.data.currency || "");
            this.updatePageSettings(res.data.settings);
            this.setAvailableWidgets(
              res.data.available_widgets.map((item) =>
                generateCart({
                  title: item.title,
                  value: item?.value?.toString() || "0",
                  sub: item?.sub || "",
                  extra: item?.extra || "",
                  series: item?.series || [{ name: item.title, data: [] }],
                  key: (item as unknown as IAnalyticsCarts).key || "",
                  size: (item as unknown as IAnalyticsCarts).size || "large",
                }),
              ),
            );

            this.single.carts = res.data.carts.map((item) =>
              generateCart({
                title: item.title,
                value: item.value.toString(),
                sub: item?.sub || "",
                extra: item.extra || "",
                series: item.series || [{ name: item.title, data: [] }],
                size: (item as unknown as IAnalyticsCarts).size || "large",
                key: (item as unknown as IAnalyticsCarts).key || "",
              }),
            );
          }

          if (res?.data?.charts) {
            this.single.charts = res.data.charts.map((item) =>
              generateChart(item),
            );
          }
        }

        setTimeout(() => {
          this.loading = false;
        }, 1000);
      });
    },

    async saveWidgets() {
      fetchAnalyticsData<IFetchDashboardResponse>(
        {
          action: "ccb_analytics_add_widget",
          nonce: window?.ccb_nonces?.ccb_analytics_add_widget || "",
          data: {
            calc_id: this.currentPage?.id || 0,
            widgets: JSON.stringify(this.single.carts),
          },
        },
        "post",
      );
    },

    clickOption(page: AnalyticsPages) {
      this.currentPage = page;
      if (page.id === 0) {
        this.fetchDashboardDataHandler();
      } else {
        this.fetchSingleDataHandler(page.id);
      }
    },

    updatePageSettings(settings: Record<string, Record<string, boolean>>) {
      this.pageSettings = settings;
    },

    savePageSettings() {
      fetchAnalyticsData<IFetchDashboardResponse>(
        {
          action: "ccb_analytics_page_settings",
          nonce: window?.ccb_nonces?.ccb_analytics_page_settings || "",
          data: {
            calc_id: this.currentPage?.id || 0,
            settings: JSON.stringify(this.pageSettings),
          },
        },
        "post",
      );
    },

    setLoading(loading: boolean) {
      this.loading = loading;
    },

    setAvailableWidgets(widgets: IAnalyticsCarts[] = []) {
      this.availableWidgets = widgets;
    },

    updateSingleCarts(carts: IAnalyticsCarts[]) {
      this.single.carts = carts;
    },

    updateStatus(status: "all" | "complete" | "pending") {
      this.status = status;
    },

    setCurrency(currency: string) {
      this.currency = currency;
    },
  },
});
