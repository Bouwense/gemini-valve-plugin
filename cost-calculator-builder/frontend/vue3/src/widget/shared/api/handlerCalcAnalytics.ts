import { WidgetAPI } from "@/widget/shared/api/WidgetAPI";
import { IWidgetAnalyticsRequestParams } from "@/widget/shared/types/analytics/analytics.type";

export async function handleCalcAnalyticsRequest(
  params: IWidgetAnalyticsRequestParams,
): Promise<void> {
  return await WidgetAPI.calcAnalytics(params);
}
