import { AllowedGroupingsKeys } from "@/admin/shared/utils/periods";
import { Period } from "@/admin/store/analytics/useAnalyticsStore";

export interface ICalculatorsRequestParams {
  action: string;
  nonce: string;
}

export interface IAnalyticsRequestParams extends ICalculatorsRequestParams {
  data: {
    [key: string]: AllowedGroupingsKeys | Period | number | string | undefined;
  };
}
