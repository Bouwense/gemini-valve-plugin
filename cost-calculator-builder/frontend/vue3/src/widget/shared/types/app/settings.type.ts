import { AppearanceStore } from "@/widget/app/providers/stores/appearanceStore";
import { IConditionList } from "@/widget/shared/types/app/conditions.type";
import { ISourceField } from "@/widget/shared/types/fields";
import { IDiscount } from "@/widget/shared/types/discounts";

export interface ISettings {
  calc_id: number | null;
  title: string;
  currency: string;
  currencyPosition: string;
  thousandSeparator: string;
  decimalSeparator: string;
  decimalPlaces: number;
  dateFormat: string;
  timeFormat: string;
  timezone: string;
  language: string;
  theme: string;
  responsive: boolean;
  mobileBreakpoint: number;
  desktopBreakpoint: number;
  tabletBreakpoint: number;
  animation: boolean;
  animationDuration: number;
  animationType: string;
  debug: boolean;
  appearance: AppearanceStore;
  conditions: IConditionList[];
  fields: ISourceField[];
  discounts: IDiscount[];
  form_fields: any[];
  geolocation: any;
  quote_settings: any;
}
