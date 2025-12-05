import axios from "axios";
import {
  IOrdersRequestParams,
  IUpdateOrderStatusRequestParams,
  IUpdateStatusesRequestParams,
  ISaveOrdersSettingsRequestParams,
  ISaveTableSettingsRequestParams,
  IRestoreOrdersAndTablesRequestParams,
  IDeleteOrderStatusRequestParams,
  IDeleteOrderRequestParams,
  IDeleteOrderNoteRequestParams,
  IUpdateOrderNoteRequestParams,
  ICreateOrderNoteRequestParams,
  IDeleteOrdersRequestParams,
  IUpdateOrdersStatusRequestParams,
} from "@/orders/shared/types/api/request.type";
import {
  IUpdateOrderStatusResponse,
  IUpdateStatusesResponse,
  ISaveOrdersSettingsResponse,
  ISaveTableSettingsResponse,
  IRestoreOrdersSettingsResponse,
  IRestoreTableSettingsResponse,
  IDeleteOrderStatusResponse,
  IDeleteOrderResponse,
  IDeleteOrderNoteResponse,
  IUpdateOrderNoteResponse,
  ICreateOrderNoteResponse,
  IDeleteOrdersResponse,
  IUpdateOrdersStatusResponse,
} from "@/orders/shared/types/api/response.type";

// @ts-ignore
const AJAX_URL = window.ajax_window?.ajax_url || "";

export class OrdersAPI {
  public static async fetchOrders(req_params: IOrdersRequestParams) {
    const { action, nonce, limit, page, params } = req_params;

    const ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";

    const urlParams = new URLSearchParams({
      action,
      nonce,
      limit: limit.toString(),
      page: page.toString(),
    });

    try {
      const response = await axios.get(`${ajaxUrl}?${urlParams.toString()}`, {
        params,
      });
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async updateStatuses(
    params: IUpdateStatusesRequestParams,
  ): Promise<IUpdateStatusesResponse> {
    const { action, nonce, statuses } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      statuses,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async updateOrderStatus(
    params: IUpdateOrderStatusRequestParams,
  ): Promise<IUpdateOrderStatusResponse> {
    const { action, nonce, order_id, status_id } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      order_id,
      status_id,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async saveOrdersSettings(
    params: ISaveOrdersSettingsRequestParams,
  ): Promise<ISaveOrdersSettingsResponse> {
    const { action, nonce, settings } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      settings,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async saveTableSettings(
    params: ISaveTableSettingsRequestParams,
  ): Promise<ISaveTableSettingsResponse> {
    const { action, nonce, settings } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      settings,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async restoreOrdersSettings(
    params: IRestoreOrdersAndTablesRequestParams,
  ): Promise<IRestoreOrdersSettingsResponse> {
    const { action, nonce } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    try {
      const response = await axios.post(ajaxUrl);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async restoreTableSettings(
    params: IRestoreOrdersAndTablesRequestParams,
  ): Promise<IRestoreTableSettingsResponse> {
    const { action, nonce } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    try {
      const response = await axios.post(ajaxUrl);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async deleteOrderStatus(
    params: IDeleteOrderStatusRequestParams,
  ): Promise<IDeleteOrderStatusResponse> {
    const { action, nonce, ids } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}&ids=${ids.join(",")}`;

    try {
      const response = await axios.get(ajaxUrl);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async deleteOrder(
    params: IDeleteOrderRequestParams,
  ): Promise<IDeleteOrderResponse> {
    const { action, nonce, id } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}&id=${id}`;

    try {
      const response = await axios.get(ajaxUrl);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async createOrderNote(
    params: ICreateOrderNoteRequestParams,
  ): Promise<ICreateOrderNoteResponse> {
    const { action, nonce, order_id, content } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      order_id,
      content,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async updateOrderNote(
    params: IUpdateOrderNoteRequestParams,
  ): Promise<IUpdateOrderNoteResponse> {
    const { action, nonce, note_id, content } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      note_id,
      content,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async deleteOrderNote(
    params: IDeleteOrderNoteRequestParams,
  ): Promise<IDeleteOrderNoteResponse> {
    const { action, nonce, note_id } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}&note_id=${note_id}`;

    try {
      const response = await axios.get(ajaxUrl);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async updateOrdersStatus(
    params: IUpdateOrdersStatusRequestParams,
  ): Promise<IUpdateOrdersStatusResponse> {
    const { action, nonce, order_ids, status_id } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      order_ids,
      status_id,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }

  public static async deleteOrders(
    params: IDeleteOrdersRequestParams,
  ): Promise<IDeleteOrdersResponse> {
    const { action, nonce, order_ids } = params;

    let ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";
    ajaxUrl = `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`;

    const data = {
      order_ids,
    };

    try {
      const response = await axios.post(ajaxUrl, data);
      return response.data;
    } catch (error) {
      if (axios.isAxiosError(error)) {
        console.error("Axios error:", error.message);
      } else {
        console.error("Unexpected error:", error);
      }
      throw error;
    }
  }
}
