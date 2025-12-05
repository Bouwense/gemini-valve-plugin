import {
  IAnalyticsCarts,
  IAnalyiticsBaseOptions,
} from "@/admin/shared/types/analytics/analyticsCarts.type";

export const baseCartsOptions: IAnalyiticsBaseOptions = {
  chart: {
    type: "area",
    height: 24,
    sparkline: { enabled: true },
    toolbar: { show: false },
  },
  stroke: { curve: "smooth", width: 2 },
  fill: { opacity: 0.2, type: "solid" },
  tooltip: { enabled: false },
  grid: { show: false },
  xaxis: {
    labels: { show: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: { show: false },
  colors: ["#27ae60"],
};

export const generateCart = (
  data: Omit<IAnalyticsCarts, "options">,
): IAnalyticsCarts => {
  return {
    ...data,
    options: baseCartsOptions,
    sub: data.sub || "",
    extra: data.extra || "",
    series: data.series,
    key: data.key || "",
  };
};
