export type SeriesType = {
  name: string;
  data: number[];
};

export type ChartType = "pie" | "line" | "bar" | "area" | "columns";

export interface IAnalyiticsBaseOptions {
  chart: {
    height: number;
    toolbar: { show: boolean };
    type?: string;
  };
  colors: string[];
  tooltip: { enabled: boolean };
  grid: { show: boolean };
  xaxis?: {
    labels: { show: boolean };
    axisBorder?: { show: boolean };
    axisTicks?: { show: boolean };
    categories?: string[];
  };
  yaxis?: {
    show?: boolean;
    labels?: { show: boolean };
  };
}

export interface IChartData {
  title: string;
  type: ChartType;
  series: number[];
  categories: string[];
  minValue?: number;
  extraSeries?: number[];
  key: string;
}

export interface IAnalyticsCarts {
  id: string;
  title: string;
  value: number;
  options: IAnalyiticsBaseOptions;
  sub?: string;
  extra?: string;
  series: { name: string; data: number[] }[];
}

export interface IFetchCalculatorsResponse {
  success: boolean;
  data: {
    calculators: {
      id: string;
      label: string;
      icon: string;
      deleted: boolean;
      component: any;
    }[];
  };
}

export interface IFetchDashboardResponse {
  success: boolean;
  data: {
    carts: Omit<IAnalyticsCarts, "options">[];
    charts: IChartData[];
    settings: Record<string, Record<string, boolean>>;
    available_widgets: Omit<IAnalyticsCarts, "options">[];
    currency: string;
  };
}
