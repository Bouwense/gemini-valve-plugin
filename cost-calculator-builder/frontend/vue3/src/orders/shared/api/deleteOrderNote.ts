import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { IDeleteOrderNoteRequestParams } from "@/orders/shared/types/api/request.type";
import { IDeleteOrderNoteResponse } from "@/orders/shared/types/api/response.type";

export async function deleteOrderNote(
  params: IDeleteOrderNoteRequestParams,
): Promise<IDeleteOrderNoteResponse> {
  return await OrdersAPI.deleteOrderNote(params);
}
