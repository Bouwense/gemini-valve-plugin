import { IBaseField } from "@/widget/shared/types/fields/base.type";

interface IBaseInputField extends IBaseField {
  value: number;
  round: boolean;
  sign: string;
  extraDisplayView?: string;
  step: number;

  multiply: boolean;
  unit: number;
  unitSymbol: string;
  unitPosition: "left" | "right";
}

export interface IQuantityField extends IBaseInputField {
  min: number;
  max: number;
  placeholder: string;
  hideMinMax: boolean;
  enabledCurrencySettings: boolean;
  styles: {
    style: string;
  };
  buttonsPosition: "right" | "both";
  separation: boolean;
  originalValue: number;
}

export interface IRangeField extends IBaseInputField {
  min: number;
  max: number;
  multipliedTotal: boolean;
  styles: {
    style: string;
  };
  scalePoints: string;
  jump: boolean;
  originalValue: number;
}

export interface IMultiRangeField extends IRangeField {
  defaultLeft: number;
  defaultRight: number;
  values: Array<number>;
}

export interface ITextField extends IBaseField {
  numberOfCharacters: number;
  placeholder: string;
}

export interface IValidatedFormField extends IBaseField {
  fieldType: "email" | "phone" | "name" | "website_url";
  placeholder: string;
}
