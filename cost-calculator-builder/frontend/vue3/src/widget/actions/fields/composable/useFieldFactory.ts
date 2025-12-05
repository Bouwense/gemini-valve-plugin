import { reactive } from "vue";
import {
  Field,
  IBaseField,
  IDatePickerField,
  IDividerField,
  IFileUploadField,
  IFormulaField,
  IGeolocationField,
  IHtmlField,
  IMultiOptionsField,
  IMultiRangeField,
  IOptions,
  IQuantityField,
  IRangeField,
  IRepeaterField,
  ISingleOptionsField,
  ISourceField,
  ITextField,
  ITimePickerField,
  IGroupField,
  IValidatedFormField,
} from "@/widget/shared/types/fields";

import { convertKeysToCamelCase } from "@/widget/shared/utils/convert-case-to-camel-case.utils.ts";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency.ts";
import { useFields } from "@/widget/actions/fields/composable/useFields.ts";

export function createField(
  data: ISourceField,
  repIdx?: number,
  repeaterAlias?: string,
): IBaseField {
  const fieldName = data.alias.replace(/\_field_id.*/, "");
  return reactive<IBaseField>({
    id: +data.Id,
    label: data.label,
    fieldName: fieldName,
    alias: data.alias,
    hidden: data.hidden,
    hideAndLeaveTotal: false,
    required: data.required || false,
    isCalculable: data.isCalculable,
    description: data.description,
    disabled: false,
    additionalStyles: data.additionalStyles,
    defaultValue: data.defaultValue || data.default || 0,
    displayValue:
      data.defaultValue?.toString() || data.default?.toString() || "0",

    calculateHidden: data.calculateHidden || false,
    addToSummary: data.addToSummary,

    repeaterIdx: repIdx,
    repeaterAlias: repeaterAlias,

    fieldCurrencySettings: convertKeysToCamelCase(data.fieldCurrencySettings),
    fieldCurrency: data.fieldCurrency,
    useCurrency: data.allowCurrency || false,
  });
}

export function createQuantityField(data: ISourceField): IQuantityField {
  const singleFieldMixins = useSingleField();
  const extra = data.extraLabel ? `(${data.extraLabel})` : undefined;

  let value: number = 0;
  if (data.default) {
    value = +data.default;
  }

  if (Number(data.min) > value) {
    value = data.min || 0;
  }

  const round = data.allowRound || false;
  const field = reactive<IQuantityField>({
    ...createField(data),
    value: round ? Math.round(value) : value,
    step: Number(data.step) || 1,
    round,
    min: Number(data.min) || 0,
    max: Number(data.max) || 100,
    extraDisplayView: extra,
    placeholder: data.placeholder || "",
    hideMinMax: !!data.hideMinMax || false,
    sign: data.sign || "",

    multiply: data.multiply || false,
    unit: data.unit || 1,
    unitSymbol: data.unitSymbol || "",
    unitPosition: data.unitPosition || "right",
    styles: data.styles || { style: "default" },
    buttonsPosition: data.buttonsPosition || "right",
    separation: data.separation || false,
    enabledCurrencySettings: data.enabledCurrencySettings || false,
    originalValue: value,
  });

  field.displayValue = singleFieldMixins.getCommonFieldDisplayView(
    field,
    field.value?.toString(),
  );
  return field;
}

export function createRangeField(data: ISourceField): IRangeField {
  const singleFieldMixins = useSingleField();
  const round = data.allowRound || false;

  let value: number = 0;
  if (data.default) {
    value = +data.default;
  }

  const field = reactive<IRangeField>({
    ...createField(data),
    value: round ? Math.round(value) : value,
    step: Number(data.step) || 1,
    round,
    min: Number(data.minValue) || 0,
    max: Number(data.maxValue) || 100,
    extraDisplayView: "",
    sign: data.sign || "",
    multiply: data.multiply || false,
    unit: data.unit || 1,
    unitSymbol: data.unitSymbol || "",
    unitPosition: data.unitPosition || "right",
    multipliedTotal: data.multipliedTotal || false,
    styles: data.styles || { style: "default" },
    scalePoints: data.scalePoints || "",
    jump: data.jump || false,
    originalValue: value,
  });

  field.displayValue = singleFieldMixins.getCommonFieldDisplayView(
    field,
    field.value?.toString(),
  );

  return field;
}

export function createTextField(data: ISourceField): ITextField {
  return reactive<ITextField>({
    ...createField(data),
    numberOfCharacters: data.numberOfCharacters || 0,
    placeholder: data.placeholder || "",
    displayValue: "",
    addToSummary: true,
  });
}

export function createMultiRangeField(data: ISourceField): IMultiRangeField {
  const singleFieldMixins = useSingleField();

  const defaultLeft: number = data.defaultLeft || 0;
  const defaultRight: number = data.defaultRight || 0;

  let value: number = 0;
  if (defaultRight - defaultLeft > 0) {
    value = defaultRight - defaultLeft;
  }

  if (data.multiply) {
    value = value * (data.unit || 1);
  }

  const round = data.allowRound || false;

  const field = reactive<IMultiRangeField>({
    ...createField(data),
    value: round ? Math.round(value) : value,
    step: Number(data.step) || 1,
    round,
    min: Number(data.minValue || 0),
    max: Number(data.maxValue || 100),
    extraDisplayView: "",
    defaultLeft,
    defaultRight,
    sign: data.sign || "",
    values: [defaultLeft, defaultRight],
    multiply: data.multiply || false,
    unit: data.unit || 1,
    unitSymbol: data.unitSymbol || "",
    unitPosition: data.unitPosition || "right",
    multipliedTotal: data.multipliedTotal || false,
    styles: data.styles || { style: "default" },
    scalePoints: data.scalePoints || "",
    jump: data.jump || false,
    originalValue: value,
  });

  field.displayValue = singleFieldMixins.getCommonFieldDisplayView(
    field,
    field.value?.toString(),
  );

  return field;
}

export function createMultiOptionsField(
  data: ISourceField,
): IMultiOptionsField {
  const currencyInstance = useCurrency();
  const singleFieldMixins = useSingleField();

  const field = reactive<IMultiOptionsField>({
    ...createField(data),
    options: data.options || [],
    defaultValue: data.default?.toString() || "",
    summaryView: data.summaryView || "show_value",
    styles: data.styles,
    round: data.allowRound || false,
    minAllowedOptions: data?.minChecked || 0,
    maxAllowedOptions: data?.checkedLength || data.options?.length || 0,
    disableOptions: [],
    showValueInOption: data.showValueInOption || false,
  });

  const options: IOptions[] = data.options.map((f: IOptions, idx: number) => {
    let splitValues: string[] = f.optionValue?.toString()?.split("_");
    const tempValue: string = splitValues[0] || "0";
    const originalValue: string = field.round
      ? Math.round(Number(tempValue)).toString()
      : tempValue;

    const optionConverted =
      field.useCurrency || field.fieldCurrency
        ? currencyInstance.formatCurrency(
            +originalValue,
            currencyInstance.getCurrencyOptions(field),
          )
        : originalValue;

    return {
      ...f,
      optionValue: originalValue + "_" + idx,
      optionConverted,
    };
  });

  field.options = options;

  let defaultSelectedOptions: IOptions[] = [];
  const defaultValues = data?.default?.toString()?.split(",") || [];
  if (defaultValues?.length) {
    defaultSelectedOptions = options.filter((option: IOptions) =>
      defaultValues.includes(option.optionValue),
    );
  }

  field.selectedOption = defaultSelectedOptions;

  let value = 0;
  if (
    Array.isArray(options) &&
    field.summaryView !== "show_label_not_calculable"
  ) {
    for (const option of defaultSelectedOptions) {
      let splitValues: string[] = option.optionValue?.toString()?.split("_");
      const originalValue: number = +splitValues[0];

      if (!isNaN(originalValue)) {
        value += originalValue;
      }
    }
  }

  field.value = field.round ? Math.round(value) : value;
  field.displayValue =
    singleFieldMixins.getMultipleOptionsFieldDisplayView(field);

  return field;
}

export function createSingleOptionField(
  data: ISourceField,
): ISingleOptionsField {
  const currencyInstance = useCurrency();
  const singleFieldMixins = useSingleField();

  const field = reactive<ISingleOptionsField>({
    ...createField(data),
    options: data.options || [],
    summaryView: data.summaryView || "show_value",
    styles: data.styles,
    round: data.allowRound || false,
    showValueInOption: data.showValueInOption || false,
    disableOptions: [],
  });

  const options: IOptions[] = data.options.map((f: IOptions, idx: number) => {
    let splitValues: string[] = f.optionValue?.toString()?.split("_");
    const tempValue: string = splitValues[0] || "0";
    const originalValue: string = field.round
      ? Math.round(Number(tempValue)).toString()
      : tempValue;

    const optionValue = originalValue + "_" + idx;
    const optionConverted = field.useCurrency
      ? currencyInstance.formatCurrency(
          +originalValue,
          currencyInstance.getCurrencyOptions(field),
        )
      : originalValue;

    return {
      ...f,
      optionValue,
      optionConverted,
    };
  });

  field.options = options;
  field.selectedOption = options.find(
    (f: IOptions) => f.optionValue === data.default,
  );

  let value: number = 0;

  if (
    field.selectedOption?.optionValue &&
    field.summaryView !== "show_label_not_calculable"
  ) {
    const optionValue: string = field.selectedOption?.optionValue;
    value = +optionValue.split("_")[0] || 0;
  }

  field.value = field.round ? Math.round(value) : value;
  field.displayValue = singleFieldMixins.getMultipleOptionsFieldDisplayView(
    field,
  ) as string;

  return field;
}

export function createDividerField(data: ISourceField): IDividerField {
  return reactive<IDividerField>({
    ...createField(data),
    size: data.size || "",
    len: data.len || "",
    style: data.style || "",
  });
}

export function createHtmlField(data: ISourceField): IHtmlField {
  return reactive<IHtmlField>({
    ...createField(data),
    htmlContent: data.htmlContent || "",
  });
}

export function createFileUploadField(data: ISourceField): IFileUploadField {
  const singleFieldMixins = useSingleField();
  const field = reactive<IFileUploadField>({
    ...createField(data),
    fileFormats: data.fileFormats || [],
    maxFileSize: Number(data.maxFileSize || 0),
    maxAttachedFiles: Number(data.maxAttachedFiles || 0),
    calculatePerEach: data.calculatePerEach || false,
    allowPrice: data.allowPrice || false,
    price: Number(data.price || 0),
    uploadFromUrl: data.uploadFromUrl || false,
  });

  field.displayValue = singleFieldMixins.getCommonFieldDisplayView(field);

  return field;
}

export function createGeolocationField(data: ISourceField): IGeolocationField {
  const singleFieldMixins = useSingleField();
  const field = reactive<IGeolocationField>({
    ...createField(data),
    type: data.type || "",
    costDistance: data.costDistance || false,
    distanceCostList: data.distanceCostList || [],
    distanceCostOptions: data.distanceCostOptions || [],
    eachCost: data.eachCost || 0,
    geoType: data.geoType || "userLocation",
    lastDistanceCost: data.lastDistanceCost || {},
    measure: data.measure || "",
    multiplyLocations: data.multiplyLocations || [],
    pricingType: data.pricingType || "each",
    selectedPoint: data.selectedPoint || {},
    userAddress: data.userAddress || "",
    userLocation: data.userLocation || [],
    extraDisplayView: [],
    userSelectedOptions: data.userSelectedOptions || [],
  });

  field.displayValue = singleFieldMixins.getCommonFieldDisplayView(field);

  return field;
}

export function createDatePickerField(data: ISourceField): IDatePickerField {
  return reactive<IDatePickerField>({
    ...createField(data),
    styles: data.styles,
    placeholder: data.placeholder || "",
    isHaveUnselectable: data.isHaveUnselectable || false,
    notAllowedWeekDays: data.notAllowedWeekDays || [],
    notAllowedDates: data.notAllowedDates,
    daysFromCurrent: Number(data.daysFromCurrent || 0),
    dayPrice: Number(data.dayPrice || 0),
    dayPriceEnabled: data.dayPriceEnabled || false,
    autoCloseEnabled: data.autocloseEnabled || false,
    calculateUnselectableDays: data.calculateUnselectableDays || false,
    range: data.range?.toString() === "0" ? false : data.range || false,
    selectedDate: null as unknown as Date,
    displayValue: "",
  });
}

export function createTimePickerField(data: ISourceField): ITimePickerField {
  return reactive<ITimePickerField>({
    ...createField(data),
    styles: data.styles,
    useInterval: data.useInterval || false,
    format: data.format || false,
    placeholderHours: data.placeholderHours || "",
    placeholderTime: data.placeholderTime || "",
    range: data.range || false,
    displayValue: "",
    minInterval: data.minInterval || "",
  });
}

export function createFormulaField(data: ISourceField): IFormulaField {
  let formula = data.costCalcFormula || "0";
  if (data.formulaView && data.legacyFormula) {
    formula = data.legacyFormula || "0";
  }

  return reactive<IFormulaField>({
    ...createField(data),
    formula,
    originalFormula: "",
  });
}

export function createRepeaterField(data: ISourceField): IRepeaterField {
  const fieldsInstance = useFields();
  const groupElements: Map<string, Field> = new Map();

  const fields: ISourceField[] = data.groupElements || [];
  for (const field of fields) {
    const createdField: Field = fieldsInstance.addField(field, 0, data.alias);

    if (createdField) {
      groupElements.set(field.alias, createdField);
    }
  }

  const enableFormula: boolean = data.enableFormula || false;
  const sumAllAvailable: boolean =
    (!data.enableFormula && data?.sumAllAvailable) || false;

  return reactive<IRepeaterField>({
    ...createField(data),
    originalElements: data.groupElements || [],
    groupElements: [groupElements],
    sumAllAvailable,
    enableFormula,
    repeatCount: data.repeatCount || 1,
    formula: data.costCalcFormula || "0",
    originalFormula: "",
    addButtonLabel: data.addButtonLabel || "",
    removeButtonLabel: data.removeButtonLabel || "",
  });
}

export function createGroupField(data: ISourceField): IGroupField {
  const fieldsInstance = useFields();
  const groupElements: Map<string, Field> = new Map();

  const fields: ISourceField[] = data.groupElements || [];
  for (const field of fields) {
    const createdField: Field = fieldsInstance.addField(field, 0, data.alias);
    if (createdField) {
      groupElements.set(field.alias, createdField);
    }
  }

  return reactive<IGroupField>({
    ...createField(data),
    groupElements: [groupElements],
    collapse: data.collapse || false,
    accordion: data.accordion || false,
    collapsible: data.collapsible || false,
    icon: data.icon || "",
    showTitle: data.showTitle || false,
  });
}

export function createValidatedFormField(
  data: ISourceField,
): IValidatedFormField {
  return reactive<IValidatedFormField>({
    ...createField(data),
    fieldType: data.fieldType || "email",
    placeholder: data.placeholder || "",
    displayValue: data.default?.toString() || "",
  });
}
