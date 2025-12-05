export interface IFormFieldOptions {
  optionText: string;
}

export interface IFormFieldsAttributes {
  confirmation?: boolean;
  confirmationLabel?: string;
  confirmationPlaceholder?: string;
  position?: "horizontal" | "vertical";
  display?: "horizontal" | "vertical";
  primary?: boolean;
  characterLimit?: number;
  multiselect?: boolean;
  options?: IFormFieldOptions[];
  range?: boolean;
  format?: boolean;
  separator?: string;
  defaultValues?: [];
}

export interface IFormField {
  id: number;
  type: string;
  label: string;
  placeholder: string;
  required: boolean;
  value: any;
  sortId: number;
  fieldWidth: number;
  attributes: IFormFieldsAttributes;
  selectedIdx?: number[];
}
