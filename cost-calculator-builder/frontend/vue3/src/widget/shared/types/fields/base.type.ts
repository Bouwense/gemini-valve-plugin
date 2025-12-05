import { CurrencyFormatOptions } from "@/widget/shared/types/common/currency.type";
import { IPeriodData } from "@/widget/shared/types/fields/date.type.ts";
export interface IStyles {
  boxStyle: string;
  style: string;
}

export interface IOptions {
  optionText: string;
  optionValue: string;
  optionConverted: string;
  optionHint?: string;
  src?: string;
  icon?: string;
}

export type SummaryView =
  | "show_value"
  | "show_label_not_calculable"
  | "show_label_calculable";

export interface IBaseField {
  id: number;
  label: string;
  fieldName: string;
  alias: string;
  hidden: boolean;
  hideAndLeaveTotal: boolean;
  required: boolean;
  isCalculable: boolean;
  description: string | undefined;
  disabled: boolean;
  useCurrency: boolean;
  additionalStyles?: string;

  defaultValue: string | number;
  displayValue: string;

  calculateHidden: boolean;
  addToSummary: boolean;

  repeaterIdx: number | undefined;
  repeaterAlias: string | undefined;

  value?: number;

  fieldCurrency: boolean;
  fieldCurrencySettings?: CurrencyFormatOptions;
}

export interface ISourceField {
  // common properties
  Id: number;
  label: string;
  value: string | number;
  alias: string;
  isCalculable: boolean;
  hidden: boolean;
  disabled: boolean;
  required: boolean;
  placeholder?: string;
  description?: string;
  allowRound?: boolean;
  allowCurrency?: boolean;
  additionalStyles?: string;

  defaultValue?: string | number;
  default?: string | number;

  addToSummary: boolean;
  calculateHidden: boolean;
  extraLabel?: string;

  min?: number;
  minValue?: number;
  max?: number;
  maxValue?: number;
  step?: number;
  fieldCurrency: boolean;
  fieldCurrencySettings?: CurrencyFormatOptions;

  styles?: IStyles;

  // quantity field properties
  hideMinMax?: boolean;
  enabledCurrencySettings?: boolean;
  buttonsPosition?: "right" | "both";
  separation?: boolean;

  // options field properties
  summaryView?: SummaryView;
  options: IOptions[];
  showValueInOption?: boolean;
  checkedLength?: number;
  minChecked?: number;

  // formula field properties
  costCalcFormula: string;
  legacyFormula?: string;
  formulaView?: boolean;

  // range & multi range properties
  defaultLeft?: number;
  defaultRight?: number;
  sign?: string;
  unitPosition?: "left" | "right";
  unit?: number;
  unitSymbol?: string;
  multiply?: boolean;
  multipliedTotal?: boolean;
  scalePoints?: string;
  jump?: boolean;

  // time & datepicker extra properties
  type?: string;

  // datepicker field properties
  isHaveUnselectable?: boolean;
  notAllowedWeekDays?: number[];
  notAllowedDates?: IPeriodData;
  daysFromCurrent?: number;
  dayPrice?: number;
  dayPriceEnabled?: boolean;
  autocloseEnabled?: boolean;
  calculateUnselectableDays?: boolean;
  range?: boolean;

  // timepicker field properties
  useInterval: boolean;
  format: boolean;
  placeholderHours: string;
  placeholderTime: string;
  minInterval: string;

  // html properties
  htmlContent?: string;
  numberOfCharacters?: number;

  // file upload properties
  fileFormats?: string[];
  maxAttachedFiles?: number;
  maxFileSize?: number;
  calculatePerEach?: boolean;
  uploadFromUrl?: boolean;
  allowPrice?: boolean;
  price?: number;

  // geolocation properties
  costDistance: boolean;
  distanceCostList: Array<number>;
  distanceCostOptions: Array<number>;
  eachCost: number;
  geoType: string;
  lastDistanceCost: object;
  measure: string;
  multiplyLocations: Array<object>;
  pricingType: string;
  selectedPoint?: object;
  userAddress: string;
  userLocation: Array<number>;
  userSelectedOptions: object;

  // repeater
  sumAllAvailable?: boolean;
  groupElements?: ISourceField[];
  repeatCount?: number;
  repeaterIdx?: number;
  enableFormula?: boolean;
  addButtonLabel?: string;
  removeButtonLabel?: string;

  // group
  collapse: boolean;
  collapsible: boolean;
  accordion: boolean;
  icon: string;
  showTitle: boolean;

  // divider
  size?: string;
  len?: string;
  style?: string;

  // validated form field properties
  fieldType?: "email" | "phone" | "name" | "website_url";
}
