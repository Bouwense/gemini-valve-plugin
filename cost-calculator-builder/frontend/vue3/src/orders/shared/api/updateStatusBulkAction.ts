import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IUpdateOrdersStatusRequestParams } from "@/orders/shared/types/api/request.type";
import { IUpdateOrdersStatusResponse } from "@/orders/shared/types/api/response.type";

export async function updateOrdersStatus(
  params: IUpdateOrdersStatusRequestParams,
): Promise<IUpdateOrdersStatusResponse> {
  return await OrdersAPI.updateOrdersStatus(params);
}
