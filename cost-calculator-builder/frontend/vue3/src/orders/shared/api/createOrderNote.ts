import { OrdersAPI } from "@/orders/shared/api/OrdersAPI";
import { ICreateOrderNoteRequestParams } from "@/orders/shared/types/api/request.type";
import { ICreateOrderNoteResponse } from "@/orders/shared/types/api/response.type";

export async function createOrderNote(
  params: ICreateOrderNoteRequestParams,
): Promise<ICreateOrderNoteResponse> {
  return await OrdersAPI.createOrderNote(params);
}
