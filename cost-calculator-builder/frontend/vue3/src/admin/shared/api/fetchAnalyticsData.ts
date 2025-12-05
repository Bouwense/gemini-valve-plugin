import { AnalyticsAPI } from "./AnalyticsAPI";
import { IAnalyticsRequestParams } from "../types/analytics/api/request.type";

export async function fetchAnalyticsData<T>(
  params: IAnalyticsRequestParams,
  status: "get" | "post" = "get",
): Promise<T> {
  return await AnalyticsAPI.fetchAnalyticsData(params, status);
}
