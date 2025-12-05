import { Period } from "@/admin/store/analytics/useAnalyticsStore";

export type AllowedGroupingsType = {
  value: string;
  label: string;
};
export type AllowedGroupingsKeys = "week" | "month" | "year" | "hour";

const getAllowedOptions: Record<AllowedGroupingsKeys, AllowedGroupingsType> = {
  hour: {
    value: "hour",
    label: "Hourly",
  },
  week: {
    value: "week",
    label: "Weekly",
  },
  month: {
    value: "month",
    label: "Monthly",
  },
  year: {
    value: "year",
    label: "Yearly",
  },
};

const allowedGroupings: Record<Period, AllowedGroupingsType[]> = {
  all: [
    getAllowedOptions.hour,
    getAllowedOptions.week,
    getAllowedOptions.month,
    getAllowedOptions.year,
  ],
  today: [getAllowedOptions.hour],
  yesterday: [getAllowedOptions.hour],
  last_7_days: [getAllowedOptions.hour, getAllowedOptions.week],
  last_30_days: [
    getAllowedOptions.hour,
    getAllowedOptions.week,
    getAllowedOptions.month,
  ],
  last_90_days: [
    getAllowedOptions.hour,
    getAllowedOptions.week,
    getAllowedOptions.month,
  ],
  last_year: [
    getAllowedOptions.hour,
    getAllowedOptions.week,
    getAllowedOptions.month,
    getAllowedOptions.year,
  ],
};

export const getPeriods = (period: Period): AllowedGroupingsType[] => {
  return allowedGroupings[period] || [];
};

export const getAllowedOption = (
  options: AllowedGroupingsType[],
): AllowedGroupingsKeys => {
  return options.length > 0
    ? (options[0]?.value as AllowedGroupingsKeys)
    : "hour";
};
