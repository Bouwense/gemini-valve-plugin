import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IOrdersRequestParams } from "@/orders/shared/types/api/request.type";

export async function fetchOrdersData<T>(
  params: IOrdersRequestParams,
): Promise<T> {
  return await OrdersAPI.fetchOrders(params);
}
