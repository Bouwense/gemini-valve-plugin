import { IStickySettings, IStickyWooCheckout } from "@/sticky/shared/types/app";
import { CurrencyFormatOptions } from "@/widget/shared/types/common/currency.type";
import { ITranslations } from "@/widget/shared/types/app/translations.type";

export interface IncomingStickyData {
  stickySettings: IStickySettings;
  wooCheckoutSettings: IStickyWooCheckout;
  calcId: number;
  title: string;
  currency: CurrencyFormatOptions;
  translations: ITranslations;
  is_pro_active: boolean;
}
