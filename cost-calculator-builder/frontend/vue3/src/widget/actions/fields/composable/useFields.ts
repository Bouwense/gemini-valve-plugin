import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore";
import {
  createTextField,
  createHtmlField,
  createRangeField,
  createDividerField,
  createFormulaField,
  createQuantityField,
  createFileUploadField,
  createDatePickerField,
  createMultiRangeField,
  createTimePickerField,
  createGeolocationField,
  createMultiOptionsField,
  createSingleOptionField,
  createRepeaterField,
  createGroupField,
  createValidatedFormField,
} from "./useFieldFactory";

import { Field, IOptions, ISourceField } from "@/widget/shared/types/fields";
import { convertKeysToCamelCase } from "@/widget/shared/utils/convert-case-to-camel-case.utils.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore";

interface IFieldsResult {
  triggerFieldCondition: (alias: string) => void;
  triggerConditions: () => void;
  addField: (
    data: ISourceField,
    repeaterIdx?: number,
    repeaterAlias?: string,
  ) => Field;
  getFields: () => Field[];
}

function addField(
  data: ISourceField,
  repeaterIdx?: number,
  repeaterAlias?: string,
): Field {
  data = convertKeysToCamelCase(data);

  const fieldName: string = data.alias.replace(/\_field_id.*/, "");
  const options: IOptions[] = (data.options || []) as IOptions[];

  if (options.length) {
    for (let i = 0; i < options?.length; i++) {
      const option = options[i];
      if (!option.optionValue.includes("_")) {
        options[i].optionValue = `${option.optionValue}_${i}`;
      }
    }

    data.options = options;
  }

  let field;

  switch (fieldName) {
    case "quantity":
      field = createQuantityField(data);
      break;
    case "radio":
    case "dropDown":
    case "radio_with_img":
    case "dropDown_with_img":
      field = createSingleOptionField(data);
      break;
    case "checkbox":
    case "toggle":
    case "checkbox_with_img":
      field = createMultiOptionsField(data);
      break;
    case "text":
      field = createTextField(data);
      break;
    case "line":
      field = createDividerField(data);
      break;
    case "html":
      field = createHtmlField(data);
      break;
    case "file_upload":
      field = createFileUploadField(data);
      break;
    case "range":
      field = createRangeField(data);
      break;
    case "multi_range":
      field = createMultiRangeField(data);
      break;
    case "geolocation":
      field = createGeolocationField(data);
      break;
    case "datePicker":
      field = createDatePickerField(data);
      break;
    case "timePicker":
      field = createTimePickerField(data);
      break;
    case "total":
      field = createFormulaField(data);
      break;
    case "repeater":
      field = createRepeaterField(data);
      break;
    case "group":
      field = createGroupField(data);
      break;
    case "validated_form":
      field = createValidatedFormField(data);
      break;
  }

  if (field && field.fieldName !== "repeater") {
    field.repeaterIdx = repeaterIdx;
    if ("repeaterAlias" in field) field.repeaterAlias = repeaterAlias;
  }

  return field as Field;
}

function triggerFieldCondition(alias: string): void {
  const conditionsStore = useConditionsStore();
  conditionsStore.applyConditionForField(alias);
}

function triggerConditions(): void {
  const conditionsStore = useConditionsStore();
  conditionsStore.triggerCondition();
}

function getFields(): Field[] {
  const fieldsStore = useFieldsStore();
  return fieldsStore.getFields as Field[];
}

export function useFields(): IFieldsResult {
  return {
    addField,
    triggerFieldCondition,
    triggerConditions,
    getFields,
  };
}
