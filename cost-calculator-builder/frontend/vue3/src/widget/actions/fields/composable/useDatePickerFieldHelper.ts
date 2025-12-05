import { IDatePickerField } from "@/widget/shared/types/fields";
import moment from "moment/min/moment-with-locales";
import DateFormatter from "php-date-formatter";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";

interface IUseDatePickerFieldHelper {
  parseDate: (str: string) => Date;
  displayValueHelper: (
    innerDate: Date | Date[] | undefined,
    locale?: string,
  ) => string;
  getDaysDifference: (
    start: Date,
    end: Date,
    field: IDatePickerField,
    getBlockPeriodDates: Date[],
    getDisabledWeekDays: number[],
  ) => number;
}

const parseDate = (str: string): Date => {
  const [day, month, year] = str.split("/").map(Number);
  return new Date(year, month - 1, day); // Month is 0-based in JS
};

const getDaysDifference = (
  start: Date,
  end: Date,
  field: IDatePickerField,
  getBlockPeriodDates: Date[],
  getDisabledWeekDays: number[],
): number => {
  if (!start || !end) return 0;
  const startDate = new Date(start);
  const endDate = new Date(end);

  if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) return 0;
  if (startDate.getTime() === endDate.getTime()) return 1;

  const timeDiff = Math.abs(endDate.getTime() - startDate.getTime());
  const dayDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24));

  let disabledDays: number = 0;
  if (!field.calculateUnselectableDays && field.range) {
    const periodsCount = getBlockPeriodDates.filter((date) => {
      const time = new Date(date).getTime();
      return time >= startDate.getTime() && time <= endDate.getTime(); // Check if within range
    }).length;

    let weeksDaysCount: number = 0;
    for (let i = 1; i <= dayDiff; i++) {
      const nDay = new Date(start);
      nDay.setDate(nDay.getDate() + i);
      const weekdayNumber = nDay.getDay();
      if (getDisabledWeekDays?.includes(weekdayNumber)) {
        weeksDaysCount++;
      }
    }

    disabledDays = periodsCount + weeksDaysCount;
  }

  const result = dayDiff > 0 ? dayDiff + 1 : 0;
  return Math.max(0, result - disabledDays);
};

const displayValueHelper = (
  innerDate: Date | Date[] | undefined,
  locale: string = "en",
): string => {
  if (!innerDate) return "";
  moment.locale(locale);

  const appStore = useAppStore();
  const dateFormat = appStore.getDateFormat ? appStore.getDateFormat : "M j, Y";
  const localeData = moment.localeData();
  const fmt = new DateFormatter({
    dateSettings: {
      months: localeData.months(),
      monthsShort: localeData.monthsShort(),
    },
  });

  let value: string = "";
  if (Array.isArray(innerDate)) {
    const values: string[] = [];
    for (const d of innerDate) {
      values.push(fmt.formatDate(d, dateFormat));
    }

    value = values.join(" - ");
  } else if (innerDate) {
    value = fmt.formatDate(innerDate, dateFormat) as string;
  }

  return value;
};

export function useDatePickerFieldHelper(): IUseDatePickerFieldHelper {
  return {
    parseDate,
    getDaysDifference,
    displayValueHelper,
  };
}
