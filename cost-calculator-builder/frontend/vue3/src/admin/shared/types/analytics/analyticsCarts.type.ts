export interface IAnalyiticsBaseOptions {
  chart: {
    type: string;
    height: number;
    sparkline: { enabled: boolean };
    toolbar: { show: boolean };
  };
  stroke: { curve: string; width: number };
  fill: { opacity: number; type: string };
  tooltip: { enabled: boolean };
  grid: { show: boolean };
  xaxis: {
    labels: { show: boolean };
    axisBorder: { show: boolean };
    axisTicks: { show: boolean };
  };
  yaxis: { show: boolean };
  colors?: string[];
}

export interface IAnalyticsCarts {
  title: string;
  value: string;
  sub: string;
  extra: string;
  options: IAnalyiticsBaseOptions;
  series: { name: string; data: number[] }[];
  key?: string;
  size: "small" | "large";
}
