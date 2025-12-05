import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { ISaveOrdersSettingsRequestParams } from "@/orders/shared/types/api/request.type";
import { ISaveOrdersSettingsResponse } from "@/orders/shared/types/api/response.type";

export async function saveOrdersSettings(
  params: ISaveOrdersSettingsRequestParams,
): Promise<ISaveOrdersSettingsResponse> {
  return await OrdersAPI.saveOrdersSettings(params);
}
