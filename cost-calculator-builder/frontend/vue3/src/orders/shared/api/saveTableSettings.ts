import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { ISaveTableSettingsRequestParams } from "@/orders/shared/types/api/request.type";
import { ISaveTableSettingsResponse } from "@/orders/shared/types/api/response.type";

export async function saveTableSettings(
  params: ISaveTableSettingsRequestParams,
): Promise<ISaveTableSettingsResponse> {
  return await OrdersAPI.saveTableSettings(params);
}
