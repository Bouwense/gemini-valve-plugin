import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IUpdateOrderStatusRequestParams } from "@/orders/shared/types/api/request.type";
import { IUpdateOrderStatusResponse } from "../types/api/response.type";

export async function updateOrderStatus(
  params: IUpdateOrderStatusRequestParams,
): Promise<IUpdateOrderStatusResponse> {
  return await OrdersAPI.updateOrderStatus(params);
}
