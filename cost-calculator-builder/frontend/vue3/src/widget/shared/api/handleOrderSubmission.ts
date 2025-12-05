import { WidgetAPI } from "@/widget/shared/api/WidgetAPI";
import { ISubmitsRequestParams } from "@/widget/shared/types/orders";

export async function handleSubmissionRequest<T>(
  params: ISubmitsRequestParams,
): Promise<T> {
  return await WidgetAPI.makeRequest(params);
}
