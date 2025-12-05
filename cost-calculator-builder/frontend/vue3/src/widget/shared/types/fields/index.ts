import {
  IBaseField,
  IOptions,
  IStyles,
  SummaryView,
  ISourceField,
} from "@/widget/shared/types/fields/base.type";
import {
  IQuantityField,
  ITextField,
  IRangeField,
  IMultiRangeField,
  IValidatedFormField,
} from "@/widget/shared/types/fields/input.type";
import {
  ISingleOptionsField,
  IMultiOptionsField,
  FieldWithMultipleDisplayView,
} from "@/widget/shared/types/fields/select.type";
import {
  IDatePickerField,
  ITimePickerField,
  IPeriod,
  IPeriodData,
} from "@/widget/shared/types/fields/date.type";
import { IFormField } from "@/widget/shared/types/fields/form.type";
import {
  IRepeaterField,
  IFormulaField,
  IDividerField,
  IHtmlField,
  IGeolocationField,
  IFileUploadField,
  IPageBreakerField,
  IGroupField,
  IDefaultTotal,
} from "@/widget/shared/types/fields/fields.type";

export type {
  IBaseField,
  IOptions,
  IStyles,
  SummaryView,
  IQuantityField,
  ITextField,
  IRangeField,
  IMultiRangeField,
  ISingleOptionsField,
  IMultiOptionsField,
  IDatePickerField,
  ITimePickerField,
  IPeriod,
  IPeriodData,
  IFileUploadField,
  IFormulaField,
  IRepeaterField,
  ISourceField,
  IFormField,
  IDividerField,
  IHtmlField,
  IGeolocationField,
  FieldWithMultipleDisplayView,
  IGroupField,
  IValidatedFormField,
  IDefaultTotal,
};

export * from "@/widget/shared/types/fields/woo.type";

export type Field =
  | IQuantityField
  | ITextField
  | IRangeField
  | IMultiRangeField
  | ISingleOptionsField
  | IMultiOptionsField
  | IDatePickerField
  | ITimePickerField
  | IFileUploadField
  | IFormulaField
  | IRepeaterField
  | IDividerField
  | IHtmlField
  | IGeolocationField
  | IPageBreakerField
  | IGroupField
  | IValidatedFormField;
