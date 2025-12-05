import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IRestoreOrdersAndTablesRequestParams } from "@/orders/shared/types/api/request.type";
import { IRestoreOrdersSettingsResponse } from "@/orders/shared/types/api/response.type";

export async function restoreOrdersSettings(
  params: IRestoreOrdersAndTablesRequestParams,
): Promise<IRestoreOrdersSettingsResponse> {
  return await OrdersAPI.restoreOrdersSettings(params);
}
