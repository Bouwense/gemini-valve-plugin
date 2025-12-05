import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IDeleteOrdersRequestParams } from "@/orders/shared/types/api/request.type";
import { IDeleteOrdersResponse } from "@/orders/shared/types/api/response.type";

export async function deleteOrders(
  params: IDeleteOrdersRequestParams,
): Promise<IDeleteOrdersResponse> {
  return await OrdersAPI.deleteOrders(params);
}
