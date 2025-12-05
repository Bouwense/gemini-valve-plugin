import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IUpdateOrderNoteRequestParams } from "@/orders/shared/types/api/request.type";
import { IUpdateOrderNoteResponse } from "@/orders/shared/types/api/response.type";

export async function updateOrderNote(
  params: IUpdateOrderNoteRequestParams,
): Promise<IUpdateOrderNoteResponse> {
  return await OrdersAPI.updateOrderNote(params);
}
