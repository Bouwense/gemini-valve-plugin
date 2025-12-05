import { customBtoa } from "@/widget/shared/utils/custom-btoa.utils.ts";
import { IFileData, ISubmitsRequestParams } from "@/widget/shared/types/orders";
import { IWidgetAnalyticsRequestParams } from "@/widget/shared/types/analytics/analytics.type";

// @ts-ignore
const AJAX_URL = window.ajax_window?.ajax_url || "";

export class WidgetAPI {
  public static async makeRequest(params: ISubmitsRequestParams) {
    const { action, nonce, data } = params;
    const formData = new FormData();
    const filesData: IFileData[] = params.files || [];

    formData.append("action", action);
    formData.append("data", customBtoa(JSON.stringify(data)));
    formData.append("nonce", nonce);

    if (filesData?.length) {
      filesData.forEach((fileItem: IFileData) => {
        if (Array.isArray(fileItem?.files)) {
          for (const file of fileItem.files) {
            formData.append([fileItem.alias, file.name].join("_ccb_"), file);
          }
        }
      });
    }

    try {
      const response = await fetch(AJAX_URL, {
        method: "POST",
        body: formData,
      });

      return await response.json();
    } catch (error) {
      console.error(`API Request Failed (${action}):`, error);
      const message =
        typeof error === "object" && error !== null && "message" in error
          ? error?.message
          : "Request failed";
      return { success: false, message };
    }
  }

  public static async calcAnalytics(params: IWidgetAnalyticsRequestParams) {
    const { action, nonce, data } = params;
    const formData = new FormData();

    formData.append("action", action);
    formData.append("data", customBtoa(JSON.stringify(data)));
    formData.append("nonce", nonce);

    try {
      await fetch(AJAX_URL, {
        method: "POST",
        body: formData,
      });
    } catch (error) {
      console.error(`API Request Failed (${action}):`, error);
    }
  }
}
