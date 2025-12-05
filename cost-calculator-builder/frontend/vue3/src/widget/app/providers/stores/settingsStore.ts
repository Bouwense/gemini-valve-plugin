import { defineStore } from "pinia";
import { convertKeysToCamelCase } from "@/widget/shared/utils/convert-case-to-camel-case.utils.ts";
import { StripeData } from "@/widget/shared/types/settings/settings.type.ts";

import {
  ICurrency,
  IEmailOptions,
  IFormFields,
  IGeneral,
  IInvoice,
  INotice,
  IPageBreak,
  IRecaptcha,
  ISticky,
  IThankYouPage,
  IWooProducts,
  IPaymentGateway,
  IWooCheckout,
  IGeolocation,
  IEditCalcButton,
  IPdfSettings,
  IQuoteSettings,
  IWarningTexts,
} from "@/widget/shared/types/settings";

interface SettingsStore {
  general: IGeneral | null;
  currency: ICurrency | null;
  thankYouPage: IThankYouPage | null;
  editCalcButton: IEditCalcButton | null;
  stickyCalc: ISticky | null;
  formFields: IFormFields | null;
  warningTexts: IWarningTexts | null;
  emailOptions: IEmailOptions | [];
  wooProducts: IWooProducts | null;
  wooCheckout: IWooCheckout | null;
  paymentGateway: IPaymentGateway | null;
  pageBreaker: IPageBreak | null;
  notice: INotice | null;
  recaptcha: IRecaptcha | null;
  invoice: IInvoice | null;
  geolocation: IGeolocation | null;
  pdfSettings: IPdfSettings | null;
  quoteSettings: IQuoteSettings | null;
  termsAndConditionsTrigger: boolean;
  summaryDisplayShowSummary: boolean;
  language: string;
}

export const useSettingsStore = defineStore("settings_store", {
  state: (): SettingsStore => ({
    general: null,
    currency: null,
    thankYouPage: null,
    stickyCalc: null,
    formFields: null,
    warningTexts: null,
    emailOptions: [],
    wooProducts: null,
    wooCheckout: null,
    paymentGateway: null,
    pageBreaker: null,
    notice: null,
    invoice: null,
    recaptcha: null,
    geolocation: null,
    editCalcButton: null,
    pdfSettings: null,
    quoteSettings: null,
    termsAndConditionsTrigger: false,
    summaryDisplayShowSummary: false,
    language: "",
  }),

  getters: {
    getPdfSettings: (state: SettingsStore): IPdfSettings | null => {
      return state.pdfSettings;
    },

    getQuoteSettings: (state: SettingsStore): IQuoteSettings | null =>
      state.quoteSettings,

    getPageBreakerSettings: (state: SettingsStore): IPageBreak | null =>
      state.pageBreaker,

    getGeneralSettings: (state: SettingsStore): IGeneral | null =>
      state.general,

    getCurrencySettings: (state: SettingsStore): ICurrency | null =>
      state.currency,

    getWooCheckoutSettings: (state: SettingsStore): IWooCheckout | null =>
      state.wooCheckout,

    getWooProductsSettings: (state: SettingsStore): IWooProducts | null =>
      state.wooProducts,

    getFormSettings: (state: SettingsStore): IFormFields | null =>
      state.formFields,

    getRecaptchaSettings: (state: SettingsStore): IRecaptcha | null =>
      state.recaptcha,

    getWarningTexts: (state: SettingsStore): IWarningTexts | null =>
      state.warningTexts,

    getPaymentGateway: (state: SettingsStore): IPaymentGateway | null =>
      state.paymentGateway,

    getStripeData: (state: SettingsStore): StripeData | null => {
      return state.paymentGateway?.cards?.cardPayments?.stripe as StripeData;
    },

    getStripePublishKey: (state: SettingsStore): string => {
      const stripeData = state.paymentGateway?.cards?.cardPayments?.stripe;
      return stripeData?.publishKey || "";
    },

    getStripeSecretKey: (state: SettingsStore): string => {
      const stripeData = state.paymentGateway?.cards?.cardPayments?.stripe;
      return stripeData?.secretKey || "";
    },

    getRazorpayKeyId: (state: SettingsStore): string => {
      const razorpayData = state.paymentGateway?.cards?.cardPayments?.razorpay;
      return razorpayData?.keyId || "";
    },

    getRazorpayPaymentCurrency: (state: SettingsStore): string => {
      const razorpayData = state.paymentGateway?.cards?.cardPayments?.razorpay;
      return razorpayData?.currency || "usd";
    },

    getThankYouPage: (state: SettingsStore): IThankYouPage | null => {
      return state.thankYouPage;
    },

    getInvoice: (state: SettingsStore): IInvoice | null => {
      return state.invoice;
    },

    getEditCalcButton: (state: SettingsStore): IEditCalcButton | null => {
      return state.editCalcButton;
    },

    hasThankYouPage(state: SettingsStore) {
      const thankYouPage = state.thankYouPage;
      return (
        thankYouPage?.enable &&
        ["same_page", "modal"].includes(thankYouPage?.type)
      );
    },

    getTermsAndConditionsStatus(state: SettingsStore): boolean {
      if (state.formFields?.accessTermsEmail) {
        return !state.formFields?.termsAndConditions?.checkbox;
      }

      return false;
    },

    checkTermsAndConditionsApproved(state: SettingsStore): boolean {
      return state.termsAndConditionsTrigger
        ? this.getTermsAndConditionsStatus
        : false;
    },

    getSummaryDisplayShowSummary(state: SettingsStore): boolean {
      return state.summaryDisplayShowSummary;
    },

    getLanguage(state: SettingsStore): string {
      return state.language;
    },
  },

  actions: {
    initGeolocationSettingsStore(data: IGeolocation): void {
      const convertedData = convertKeysToCamelCase(data);
      this.geolocation = convertedData as IGeolocation;
    },

    initPdfSettings(data: IPdfSettings): void {
      const convertedData = convertKeysToCamelCase(data);
      this.pdfSettings = convertedData as IPdfSettings;
    },

    initQuoteSettings(data: IQuoteSettings): void {
      const convertedData = convertKeysToCamelCase(data);
      this.quoteSettings = convertedData;
    },

    updateTermsValue(value: boolean): void {
      this.setTermsAndConditionsTrigger(false);
      if (this.formFields?.accessTermsEmail) {
        this.formFields.termsAndConditions.checkbox = value;
      }
    },

    setTermsAndConditionsTrigger(value: boolean): void {
      if (this.formFields?.accessTermsEmail) {
        this.termsAndConditionsTrigger = value;
      }
    },

    initSettingsStore(data: any): void {
      const convertedData = convertKeysToCamelCase(data);

      this.general = convertedData.general as IGeneral;
      this.currency = convertedData.currency as ICurrency;
      this.thankYouPage = convertedData.thankYouPage as IThankYouPage;
      this.stickyCalc = convertedData.stickyCalc as ISticky;
      this.formFields = convertedData.formFields as IFormFields;
      this.warningTexts = convertedData.texts as IWarningTexts;
      this.emailOptions = convertedData.emailOptions as IEmailOptions;
      this.wooProducts = convertedData.wooProducts as IWooProducts;
      this.wooCheckout = convertedData.wooCheckout as IWooCheckout;
      this.paymentGateway = convertedData.paymentGateway as IPaymentGateway;
      this.pageBreaker = convertedData.pageBreak as IPageBreak;
      this.notice = convertedData.notice as INotice;
      this.invoice = convertedData.invoice as IInvoice;
      this.editCalcButton = convertedData.editCalcButton as IEditCalcButton;

      if (convertedData.recaptcha?.enable) {
        let siteKey = convertedData.recaptcha?.v2?.siteKey;
        let secretKey = convertedData.recaptcha?.v2?.secretKey;

        if (convertedData.recaptcha?.type === "v3") {
          siteKey = convertedData.recaptcha?.v3?.siteKey;
          secretKey = convertedData.recaptcha?.v3?.secretKey;
        }

        this.recaptcha = {
          enable: convertedData.recaptcha.enable,
          type: convertedData.recaptcha.type === "v2" ? "v2" : "v3",
          siteKey,
          secretKey,
        } as IRecaptcha;
      }

      if (
        this.formFields?.summaryDisplay?.enable &&
        this.formFields?.summaryDisplay?.actionAfterSubmit ===
          "show_summary_block"
      ) {
        this.thankYouPage.enable = false;
      }
    },

    initLang(language: string) {
      this.language = language;
    },

    setSummaryDisplayShowSummary(value: boolean): void {
      this.summaryDisplayShowSummary = value;
    },
  },
});
