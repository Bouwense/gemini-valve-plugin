import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IRestoreOrdersAndTablesRequestParams } from "@/orders/shared/types/api/request.type";
import { IRestoreTableSettingsResponse } from "@/orders/shared/types/api/response.type";

export async function restoreTablesSettings(
  params: IRestoreOrdersAndTablesRequestParams,
): Promise<IRestoreTableSettingsResponse> {
  return await OrdersAPI.restoreTableSettings(params);
}
