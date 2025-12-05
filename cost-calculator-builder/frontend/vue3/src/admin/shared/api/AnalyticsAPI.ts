import axios from "axios";
import {
  IAnalyticsRequestParams,
  ICalculatorsRequestParams,
} from "@/admin/shared/types/analytics/api/request.type";

// @ts-ignore
const AJAX_URL = window.ajax_window?.ajax_url || "";

export class AnalyticsAPI {
  public static async fetchCalculators(params: ICalculatorsRequestParams) {
    const { action, nonce } = params;

    const ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";

    const urlParams = new URLSearchParams({ action, nonce });

    try {
      const response = await axios.get(`${ajaxUrl}?${urlParams.toString()}`);
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

  public static async fetchAnalyticsData(
    params: IAnalyticsRequestParams,
    status: "get" | "post",
  ) {
    const { action, nonce, data } = params;

    const ajaxUrl = (window as any).ajaxurl || "/wp-admin/admin-ajax.php";

    const urlParams = new URLSearchParams({ action, nonce });

    for (const key in data) {
      if (data.hasOwnProperty(key)) {
        // @ts-ignore
        urlParams.append(key, data[key] as string);
      }
    }

    try {
      if (status === "get") {
        const response = await axios.get(`${ajaxUrl}?${urlParams.toString()}`);
        return response.data;
      } else {
        const response = await axios.post(
          `${ajaxUrl}?action=${action}&_ajax_nonce=${nonce}`,
          { data },
          {
            headers: {
              "Content-Type": "application/json",
            },
          },
        );
        return response.data;
      }
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
