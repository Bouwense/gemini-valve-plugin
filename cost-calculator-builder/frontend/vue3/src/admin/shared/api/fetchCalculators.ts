import { AnalyticsAPI } from "./AnalyticsAPI";
import { ICalculatorsRequestParams } from "../types/analytics/api/request.type";

export async function fetchCalculators<T>(
  params: ICalculatorsRequestParams,
): Promise<T> {
  return await AnalyticsAPI.fetchCalculators(params);
}
