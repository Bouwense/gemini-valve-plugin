import { IChartData } from "@/admin/shared/types/analytics/api/response.type";

export const generateChart = (data: IChartData): IChartData => {
  return {
    title: data.title,
    type: data.type,
    series: data.series,
    categories: data.categories,
    minValue: data.minValue || 0,
    extraSeries: data.extraSeries || [],
    key: data.key,
  };
};
