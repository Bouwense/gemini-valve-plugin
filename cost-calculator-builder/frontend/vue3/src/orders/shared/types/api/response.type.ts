import {
  IOrders,
  IStatuses,
  IOrderNote,
} from "@/orders/shared/types/orders.type";
import { ITableSettings } from "@/orders/shared/types/table.settings";
import { IOrdersSettings } from "@/orders/shared/types/orders.settings";

export interface IFetchOrdersResponse {
  success: boolean;
  message: string;
  data: {
    orders: IOrders[];
    total: number;
    statuses: IStatuses[];
    table_settings: ITableSettings;
    details_settings: IOrdersSettings;
  };
}

export interface IUpdateStatusesResponse {
  success: boolean;
  message: string;
  data: {
    statuses: IStatuses[];
  };
}

export interface IUpdateOrderStatusResponse {
  success: boolean;
  message: string;
}

export interface ISaveOrdersSettingsResponse {
  success: boolean;
  message: string;
}

export interface ISaveTableSettingsResponse {
  success: boolean;
  message: string;
}

export interface IRestoreOrdersSettingsResponse {
  success: boolean;
  message: string;
  data: {
    settings: IOrdersSettings;
  };
}

export interface IRestoreTableSettingsResponse {
  success: boolean;
  message: string;
  data: {
    settings: ITableSettings;
  };
}

export interface IDeleteOrderStatusResponse {
  success: boolean;
  message: string;
  data: {
    statuses: IStatuses[];
  };
}

export interface IDeleteOrderResponse {
  success: boolean;
  message: string;
}

export interface ICreateOrderNoteResponse {
  success: boolean;
  message: string;
  data: {
    notes: IOrderNote[];
  };
}

export interface IUpdateOrderNoteResponse {
  success: boolean;
  message: string;
  data: {
    notes: IOrderNote[];
  };
}

export interface IDeleteOrderNoteResponse {
  success: boolean;
  message: string;
  data: {
    notes: IOrderNote[];
  };
}

export interface IUpdateOrdersStatusResponse {
  success: boolean;
  message: string;
}

export interface IDeleteOrdersResponse {
  success: boolean;
  message: string;
}
