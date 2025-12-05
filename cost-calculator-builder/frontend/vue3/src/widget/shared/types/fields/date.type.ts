import { IBaseField, IStyles } from "@/widget/shared/types/fields/base.type";

export interface IPeriod {
  start: string;
  end: string;
}

export interface IPeriodData {
  period: IPeriod[];
  allPast: boolean;
  current: boolean;
  daysFromCurrent: boolean;
}

export interface IDatePickerField extends IBaseField {
  styles?: IStyles;
  placeholder: string;
  isHaveUnselectable: boolean;
  notAllowedWeekDays: number[];
  notAllowedDates: IPeriodData | undefined;
  daysFromCurrent: number;
  dayPrice: number;
  dayPriceEnabled: boolean;
  autoCloseEnabled: boolean;
  calculateUnselectableDays?: boolean;
  range: boolean;
  selectedDate?: Date | Date[];
  extraDisplayView?: string[];
}

export interface ITimePickerField extends IBaseField {
  styles?: IStyles;
  useInterval: boolean;
  format: boolean;
  placeholderHours: string;
  placeholderTime: string;
  minInterval: string;
  range: boolean;
}
