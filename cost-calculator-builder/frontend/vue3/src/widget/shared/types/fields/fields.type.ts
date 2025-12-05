import {
  Field,
  IBaseField,
  ISourceField,
} from "@/widget/shared/types/fields/index.ts";

import { IConditionRule } from "@/widget/shared/types/app/conditions.type";

export interface ITotalDiscount {
  discountType: string;
  isPromo?: boolean;
  promocode?: string;
  viewType: string;
  discountName: string;
  extraView: string;
}

export interface IDividerField extends IBaseField {
  htmlContent?: string;
  size: string;
  len: string;
  style: string;
}

export interface IHtmlField extends IBaseField {
  htmlContent: string;
}

export interface IGeolocationField extends IBaseField {
  type: string;
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
  extraDisplayView?: string[];
  userSelectedOptions: object;
}

export interface IFileUploadField extends IBaseField {
  fileFormats: string[];
  maxAttachedFiles: number;
  maxFileSize: number;
  calculatePerEach: boolean;
  uploadFromUrl: boolean;
  allowPrice: boolean;
  price: number;
  files?: File[];
  extraDisplayView?: string;
  customAlias?: string;
  inRepeater?: boolean;
  options?: {
    value: File[] | undefined;
  };
}

export interface IFormulaField extends IBaseField {
  formula: string;
  originalFormula: string;
  originalValue?: number;
  originalDisplayView?: string;
  hasDiscount?: boolean;
  discount?: ITotalDiscount;
}

export interface IRepeaterField extends IFormulaField {
  originalElements: ISourceField[];
  groupElements: Map<string, Field>[];
  sumAllAvailable: boolean;
  repeatCount: number;
  enableFormula: boolean;
  addButtonLabel: string;
  removeButtonLabel: string;
}

export interface IGroupField extends IBaseField {
  collapse: boolean;
  accordion: boolean;
  collapsible: boolean;
  hidden: boolean;
  icon: string;
  isCalculable: boolean;
  label: string;
  showTitle: boolean;
  groupElements: Map<string, Field>[];
}

export interface IPageBreakerField extends IBaseField {
  groupElements: Map<string, Field>[];
  conditions: Record<string, IConditionRule[]>;
  nextBtnLabel: string;
  previousBtnLabel: string;
  alias: string;
  condition: string;
  action: string;
  pageTo: string;
  type: string;
}

export type IDefaultTotal = Pick<
  IFormulaField,
  "displayValue" | "label" | "value" | "fieldName"
>;
