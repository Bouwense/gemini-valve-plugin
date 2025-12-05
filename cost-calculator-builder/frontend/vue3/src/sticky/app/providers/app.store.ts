import { defineStore } from "pinia";
import { IStickySettings, IStickyWooCheckout } from "@/sticky/shared/types/app";
import { CurrencyFormatOptions } from "@/widget/shared/types/common/currency.type";

interface IAppStore {
  settings: IStickySettings | null;
  calcId: number | null;
  title: string;
  wooCheckoutSettings: IStickyWooCheckout | null;
  currencySettings: CurrencyFormatOptions | null;
  isProActive: boolean;
}

export const useAppStore = defineStore("stickyApp", {
  state: (): IAppStore => ({
    settings: null,
    calcId: null,
    title: "",
    wooCheckoutSettings: null,
    currencySettings: null,
    isProActive: false,
  }),

  getters: {
    getStickySettings: (state: IAppStore): IStickySettings | null =>
      state.settings,

    getCalcId: (state: IAppStore): number | null => state.calcId,

    getCalcTitle: (state: IAppStore): string => state.title,

    getWooCheckoutSettings: (state: IAppStore): IStickyWooCheckout | null =>
      state.wooCheckoutSettings,

    getCurrencySettings: (state: IAppStore): CurrencyFormatOptions | null =>
      state.currencySettings,

    getIsProActive: (state: IAppStore): boolean => state.isProActive,
  },

  actions: {
    initStickySettings(settings: IStickySettings): void {
      this.settings = settings;
    },

    initWooCheckoutSettings(settings: IStickyWooCheckout): void {
      this.wooCheckoutSettings = settings;
    },

    initCurrencySettings(currency: CurrencyFormatOptions): void {
      this.currencySettings = currency;
    },

    initCalcId(calcId: number): void {
      this.calcId = +calcId || null;
    },

    initTitle(title: string): void {
      this.title = title;
    },

    setIsProActive(isProActive: boolean): void {
      this.isProActive = isProActive;
    },
  },
});
