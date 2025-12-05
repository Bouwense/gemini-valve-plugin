import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IDeleteOrderRequestParams } from "@/orders/shared/types/api/request.type";
import { IDeleteOrderResponse } from "@/orders/shared/types/api/response.type";

export async function deleteOrder(
  params: IDeleteOrderRequestParams,
): Promise<IDeleteOrderResponse> {
  return await OrdersAPI.deleteOrder(params);
}
