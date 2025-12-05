import { WooMetaAction } from "@/widget/shared/types/fields";

interface IFormulas {
  idx: number;
  title: string;
  alias: string;
}

export interface IGeneral {
  headerTitle: string;
  descriptions: boolean;
  hideEmpty: boolean;
  hideEmptyForOrdersPdfEmails: boolean;
  sticky: boolean;
  showOptionUnit: boolean;
  showDetailsAccordion: boolean;
}

export interface IGeolocation {
  maps: object;
  markerIconPath: string;
  measure: string;
  measures: object;
  pickUpIconPath: string;
  publicKey: string;
  secretKey: string;
  type: string;
}

interface IFormulas {
  idx: number;
  title: string;
  alias: string;
}

export interface ICurrency {
  useInAll: boolean;
  currency: string;
  numAfterInteger: number;
  decimalSeparator: string;
  thousandsSeparator: string;
  currencyPosition: string;
}

export interface IThankYouPage {
  enable: boolean;
  type: string;
  isOn: number;
  pageId: number;
  pageUrl: string;
  customPageLink: string;
  title: string;
  description: string;
  orderTitle: string;
  backButtonText: string;
  downloadButton: boolean;
  downloadButtonText: string;
  shareButton: boolean;
  shareButtonText: string;
  customButton: boolean;
  customButtonText: string;
  customButtonLink: string;
  showOrderId: boolean;
}

export interface ISticky {
  enable: boolean;
  displayType: string;
  btnPosition: string;
  bannerPosition: string;
  hideIcon: string;
  title: string;
  description: string;
  formulas: IFormulas[];
  pages: any[];
  btnText: string;
  oneClickAction: string;
  showCalculator: boolean;
  showTotal: boolean;
  customText: string;
  customDesc: string;
  icon: string;
  classes: string;
  isOn: boolean;
}

export interface IFormFields {
  formulas: IFormulas[];
  emailSubject: string;
  contactFormId: string;
  formType: string;
  applyFormId: number;
  accessEmail: boolean;
  adminEmailAddress: string;
  customEmailAddresses: string[];
  submitBtnText: string;
  openModalBtnText: string;
  allowContactForm: boolean;
  orderIdInSubject: boolean;
  body: string;
  payment: boolean;
  paymentMethod: boolean;
  paymentMethods: string[];
  termsAndConditions: {
    checkbox: boolean;
    text: string;
    pageId: number;
    link: string;
    linkText: string;
  };
  summaryDisplay: {
    enable: boolean;
    formTitle: string;
    submitBtnText: string;
    actionAfterSubmit: string;
  };
  isOn: number;
  accessTermsEmail: boolean;
}

export interface IWarningTexts {
  requiredMsg: string;
  formFields: {
    emailField: string;
    emailFormat: string;
    nameField: string;
    phoneField: string;
    termsAndConditionsField: string;
  };
}

export interface IEmailOptions {
  adminEmailAddress: string;
  emailDescription: string;
}

export interface IWooProducts {
  enable: boolean;
  categoryId: number;
  categoryIds: number[];
  productIds: number[];
  byCategory: boolean;
  byProduct: boolean;
  hookToShow: string;
  hideWooCart: boolean;
  metaLinks: WooMetaAction[];
  isOn: boolean;
  productPrice?: number;
  currentProductId?: number;
  isVariable?: boolean;
  attributeKeys?: string[];
}

export interface IWooCheckout {
  enable: boolean;
  productId: string | number;
  redirectTo: string;
  description: string;
  formulas: IFormulas[];
  replaceProduct: boolean;
  changeProductCount: string;
  productName: string;
  wooCartUrl: string;
  stockLinks: string[];
  isOn: number;
}

export interface IPaymentGateway {
  enable: boolean;
  isOn: boolean;
  formulas: IFormulas[];
  cards: {
    cardPayments: {
      stripe: {
        enable: boolean;
        secretKey: string;
        publishKey: string;
        currency: string;
        mode: string;
        paymentType: string;
        logo: string;
        label: string;
        slug: string;
        paymentLogoWidth: string;
      };
      razorpay: {
        enable: boolean;
        keyId: string;
        secretKey: string;
        currency: string;
        paymentType: string;
        logo: string;
        label: string;
        slug: string;
        paymentLogoWidth: string;
      };
    };
  };
  paypal: {
    enable: boolean;
    integrationType: string;
    paypalEmail: string;
    currencyCode: string;
    paypalMode: string;
    clientId: string;
    clientSecret: string;
    description: string;
  };
  cashPayment: {
    enable: boolean;
    label: string;
    type: string;
  };
}

export interface IPageBreak {
  totalInPage: string;
  summaryAfterLastPage: string;
  paginationType: string;
  hidePaginationTitle: string;
  formulas: IFormulas[];
  hasPage: string;
}

export interface INotice {
  requiredField: string;
}

export interface IRecaptcha {
  enable: boolean;
  type: "v2" | "v3";
  siteKey: string;
  secretKey: string;
}

export interface IInvoice {
  useInAll: boolean;
  showAfterPayment: number;
  emailButton: string;
  pdfButtonText: string;
}

export interface IPdfSettings {
  document: {
    tabs?: {
      sidebar?: {
        data?: {
          layout?: {
            value?: string;
          };
          textColor?: {
            data: {
              status?: boolean;
            };
            value?: string;
          };
          backgroundColor?: {
            data: {
              status?: boolean;
            };
            value?: string;
          };
        };
      };
      border?: {
        key?: string;
        title?: string;
        data?: {
          borderColor?: {
            value?: string;
          };
          borderSize?: {
            value?: number;
          };
          borderStyle?: {
            value?: string;
          };
          showBorder?: {
            value?: boolean;
            key?: string;
            label?: string;
            type?: string;
          };
          borderType?: {
            value?: string;
          };
        };
      };
      body?: {
        data?: {
          sidePaddings?: {
            value?: number;
          };
          textColor?: {
            value?: string;
          };
          backgroundColor?: {
            value?: string;
          };
          backgroundImage?: {
            value?: string;
          };
          titleColor?: {
            value?: string;
          };
        };
      };
    };
  };
  image: string;
  key: string;
  data: {
    name: string;
    image: string;
    document: {
      font: string;
      dateFormat: string;
    };
  };
  options: {
    useInAll: boolean;
    showAfterPayment: boolean;
    buttonText: string;
  };
  orderBlock: {
    enable: boolean;
    sortId: number;
    isBody: boolean;
    content: {
      showPaymentMethod: boolean;
      showGrandTotal: boolean;
      showHeading: boolean;
      headingName: string;
      headingUnit: string;
      headingValue: string;
      showQrCode: boolean;
      qrCodeLink: string;
    };
    images: {
      stampImage: string;
      stampSize: number;
      signatureImage: string;
      signatureSize: number;
    };
    lines: {
      showLines: boolean;
      lineBorderColor: string;
      lineBorderSize: number;
      lineBorderStyle: string;
      showBorder: boolean;
      borderColor: string;
      borderStyle: string;
      borderBorderSize: number;
      angleBorderSize: number;
    };
    design: {
      titleFontSize: number;
      fontSize: number;
      headingText: string;
      headingTextStatus: boolean;
      headingBackgroundStatus: boolean;
      headingBackground: string;
      tableText: string;
      tableTextStatus: boolean;
      tableBackgroundStatus: boolean;
      tableBackground: string;
    };
  };
  sections: Object;
}

export interface IQuoteSettings {
  useInAll: boolean;
  companyName: string;
  companyInfo: string;
  companyLogo: string;
  showAfterPayment: boolean;
  emailButton: boolean;
  submitBtnText: string;
  btnText: string;
  closeBtn: string;
  successText: string;
  errorText: string;
  fromEmail: string;
  fromName: string;
  buttonText: string;
  dateFormat: string;
}

export interface IEditCalcButton {
  editCalcUrl: string;
}

export interface StripeData {
  enable: boolean;
  secretKey: string;
  publishKey: string;
}
