import { IStatuses, IOrdersParams } from "@/orders/shared/types/orders.type";
import { IOrdersSettings } from "@/orders/shared/types/orders.settings";
import { ITableSettings } from "@/orders/shared/types/table.settings";

export interface IOrdersRequestParams {
  action: string;
  nonce: string;
  limit: number;
  page: number;
  params: IOrdersParams;
}

export interface IUpdateStatusesRequestParams {
  action: string;
  nonce: string;
  statuses: IStatuses[];
}

export interface IUpdateOrderStatusRequestParams {
  action: string;
  nonce: string;
  order_id: number;
  status_id: number;
}

export interface ISaveOrdersSettingsRequestParams {
  action: string;
  nonce: string;
  settings: IOrdersSettings;
}

export interface ISaveTableSettingsRequestParams {
  action: string;
  nonce: string;
  settings: ITableSettings;
}

export interface IRestoreOrdersAndTablesRequestParams {
  action: string;
  nonce: string;
}

export interface IDeleteOrderStatusRequestParams {
  action: string;
  nonce: string;
  ids: number[];
}

export interface IDeleteOrderRequestParams {
  action: string;
  nonce: string;
  id: number;
}

export interface ICreateOrderNoteRequestParams {
  action: string;
  nonce: string;
  order_id: number;
  content: string;
}

export interface IUpdateOrderNoteRequestParams {
  action: string;
  nonce: string;
  note_id: number;
  content: string;
}

export interface IDeleteOrderNoteRequestParams {
  action: string;
  nonce: string;
  note_id: number;
}

export interface IUpdateOrdersStatusRequestParams {
  action: string;
  nonce: string;
  order_ids: number[];
  status_id: number;
}

export interface IDeleteOrdersRequestParams {
  action: string;
  nonce: string;
  order_ids: number[];
}
