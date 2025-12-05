import { AppearanceStore } from "@/widget/app/providers/stores/appearanceStore";
import { IConditionList } from "@/widget/shared/types/app/conditions.type";
import { ISourceField } from "@/widget/shared/types/fields";
import { IDiscount } from "@/widget/shared/types/discounts";
import { ITranslations } from "./translations.type";

export interface IncomingData {
  settings: {
    calc_id: number | null;
    title: string;
  };
  pro_active: boolean;
  appearance: AppearanceStore;
  conditions: IConditionList[];
  fields: ISourceField[];
  dateFormat: string;
  discounts: IDiscount[];
  form_fields: any[];
  geolocation: any;
  pdf_settings: any;
  quote_settings: any;
  translations: ITranslations;
  recaptcha: any;
  is_custom_thank_you_page: boolean;
  order_id: number;
  order_data: any;
  language: string;
}
