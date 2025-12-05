import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IUpdateStatusesRequestParams } from "@/orders/shared/types/api/request.type";
import { IUpdateStatusesResponse } from "@/orders/shared/types/api/response.type";

export async function updateStatuses(
  params: IUpdateStatusesRequestParams,
): Promise<IUpdateStatusesResponse> {
  return await OrdersAPI.updateStatuses(params);
}
