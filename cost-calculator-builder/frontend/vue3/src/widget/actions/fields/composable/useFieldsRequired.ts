import { Field, IQuantityField } from "@/widget/shared/types/fields";
import { validateEmail } from "@/widget/shared/utils/validate-email.utils";
import { validateUrl } from "@/widget/shared/utils/validate-url.utils";
import { scrollIntoRequired } from "@/widget/shared/utils/scroll-to-required.utils";
import { useAppStore } from "@/widget/app/providers/stores/appStore";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore";

let timeout: ReturnType<typeof setTimeout> | null = null;

interface IUseFieldsRequiredResult {
  hasRequiredFields: (fields: Field[]) => boolean;
  resetRequiredFields: () => void;
  maybeStartValidation: (fields: Field[]) => void;
  checkFieldRequired: (field: Field) => boolean;
}

const isValueInMinMax = (field: IQuantityField): boolean => {
  let value: number = field.value;

  if (field.unit && field.multiply) {
    value = value / field.unit;
  }

  if (field.min && value < field.min) {
    return false;
  }

  return !(field.max && value > field.max);
};

const fileUploadFieldValidation = (field: Field): boolean => {
  return (
    field.required &&
    field.fieldName === "file_upload" &&
    (!("files" in field) ||
      ("files" in field &&
        field.files?.length === 0 &&
        field.allowPrice &&
        field.price > 0))
  );
};

const quantityFieldValidation = (field: Field): boolean => {
  return (
    field.fieldName === "quantity" &&
    ((field.required && (field.value || 0) <= 0) ||
      !isValueInMinMax(field as IQuantityField))
  );
};

const singleOptionFieldValidation = (field: Field): boolean => {
  return (
    field.required &&
    ["dropDown", "radio", "radio_with_img", "dropDown_with_img"].includes(
      field.fieldName,
    ) &&
    "selectedOption" in field &&
    !field.selectedOption
  );
};

const multiOptionsFieldValidation = (field: Field): boolean => {
  if (field.required) {
    return (
      ["checkbox", "toggle", "checkbox_with_img"].includes(field.fieldName) &&
      "selectedOption" in field &&
      Array.isArray(field.selectedOption) &&
      (field.selectedOption.length <= 0 ||
        ("minAllowedOptions" in field &&
          field.selectedOption.length < +field.minAllowedOptions))
    );
  }

  return (
    ["checkbox", "toggle", "checkbox_with_img"].includes(field.fieldName) &&
    "selectedOption" in field &&
    Array.isArray(field.selectedOption) &&
    "minAllowedOptions" in field &&
    field.selectedOption.length < +field.minAllowedOptions
  );
};

const datePickerFieldValidation = (field: Field): boolean => {
  if (field.required) {
    return (
      field.fieldName === "datePicker" &&
      "selectedDate" in field &&
      ((!field.range && field.selectedDate === null) ||
        (field.range && !Array.isArray(field.selectedDate)) ||
        (field.range &&
          Array.isArray(field.selectedDate) &&
          (field.selectedDate[0] === null || field.selectedDate[1] === null)))
    );
  }

  return (
    field.fieldName === "datePicker" &&
    "selectedDate" in field &&
    field.range &&
    Array.isArray(field.selectedDate) &&
    (field.selectedDate[0] === null || field.selectedDate[1] === null)
  );
};

const textFieldValidation = (field: Field): boolean => {
  if (field.required) {
    return (
      field.fieldName === "text" &&
      ((field.displayValue as string).trim() === "" ||
        ("numberOfCharacters" in field &&
          field.numberOfCharacters < field.displayValue.length))
    );
  }

  return (
    field.fieldName === "text" &&
    "numberOfCharacters" in field &&
    field.displayValue !== "" &&
    (field.numberOfCharacters || 0) !== 0 &&
    field.numberOfCharacters < field.displayValue.length
  );
};

const rangeFieldValidation = (field: Field): boolean => {
  return (
    field.required &&
    ["multi_range", "range"].includes(field.fieldName) &&
    !field.value
  );
};

const validatedFormFieldValidation = (field: Field): boolean => {
  if (field.required) {
    return (
      field.fieldName === "validated_form" &&
      "fieldType" in field &&
      ((field.displayValue as string).trim() === "" ||
        (field.fieldType === "email" && !validateEmail(field.displayValue)) ||
        (field.fieldType === "website_url" && !validateUrl(field.displayValue)))
    );
  }

  if (
    "fieldType" in field &&
    ["email", "website_url"].includes(field.fieldType)
  ) {
    if (field.fieldType === "email" && "" !== field.displayValue) {
      return !validateEmail(field.displayValue);
    }

    if (field.fieldType === "website_url" && "" !== field.displayValue) {
      return !validateUrl(field.displayValue);
    }
    return false;
  }

  return false;
};

const timePickerFieldValidation = (field: Field): boolean => {
  return (
    field.required &&
    field.fieldName === "timePicker" &&
    !Array.isArray(field.displayValue) &&
    field.displayValue.trim() === ""
  );
};

const geolocationFieldValidation = (field: Field): boolean => {
  return (
    field.required &&
    field.fieldName === "geolocation" &&
    "displayValue" in field &&
    field.displayValue?.length === 0
  );
};

const applyRequiredFieldsValidation = (fields: Field[]) => {
  /** Validation rules for required fields
   *  quantity must be higher than 0
   *  checkbox, toggle must have at least one selected element even if its value is 0
   *  dropDown, radio a value must be selected even if its value is 0
   *  datePicker must be chosen
   * **/

  return fields.filter((field: Field) => {
    return (
      quantityFieldValidation(field) ||
      rangeFieldValidation(field) ||
      multiOptionsFieldValidation(field) ||
      singleOptionFieldValidation(field) ||
      textFieldValidation(field) ||
      validatedFormFieldValidation(field) ||
      datePickerFieldValidation(field) ||
      timePickerFieldValidation(field) ||
      fileUploadFieldValidation(field) ||
      geolocationFieldValidation(field)
    );
  });
};

const resetRequiredFields = (): void => {
  const fieldsStore = useFieldsStore();
  fieldsStore.setRequiredFields([]);
};

const filterRequiredFields = (fieldsData: Field[]): Field[] => {
  const fieldsStore = useFieldsStore();
  let fields: Field[] = fieldsData;
  fieldsData.forEach((field: Field) => {
    if (field.fieldName === "repeater" && "groupElements" in field) {
      const groupElements: Field[] = [];
      field.groupElements.forEach((element) => {
        if ("entries" in element && typeof element.entries === "function") {
          for (const [_, existingField] of element.entries()) {
            groupElements.push(existingField as Field);
          }
        }
      });

      fields = [...fields, ...groupElements];
    }
  });

  fields = fields.filter(
    (field: Field) =>
      !field.hidden &&
      field.fieldName !== "total" &&
      field.fieldName !== "repeater",
  );

  fields = applyRequiredFieldsValidation(fields);

  if (fields.length) {
    const appStore = useAppStore();
    if (fieldsStore.getPageBreakEnabled) {
      const pageBreakerFieldAliases: string[] =
        fieldsStore.getActivePage?.groupElements?.map((element) => {
          if ("alias" in element) {
            return element.alias as string;
          }
          return "";
        }) || [];

      fields = fields.filter((field) =>
        pageBreakerFieldAliases.includes(field.alias),
      );
    }

    fields = fields.filter((field: Field) => !field.hidden);

    if (fields.length > 0) {
      if (timeout) {
        clearTimeout(timeout);
      }

      timeout = setTimeout(() => {
        scrollIntoRequired(
          fields[0].alias,
          fields[0].repeaterIdx,
          appStore.getCalcId || 0,
        );
      }, 300);
    }
  }

  return fields;
};

const hasRequiredFields = (fieldsData: Field[]): boolean => {
  const fieldsStore = useFieldsStore();
  resetRequiredFields();
  const fields = filterRequiredFields(fieldsData);
  fieldsStore.setRequiredFields(fields);

  return fieldsStore.getRequiredFields.length > 0;
};

const maybeStartValidation = (fieldsData: Field[]): void => {
  const fieldsStore = useFieldsStore();
  if (fieldsStore.getRequiredFields.length > 0) {
    const fields = filterRequiredFields(fieldsData);
    fieldsStore.setRequiredFields(fields);
  }
};

const checkFieldRequired = (field: Field): boolean => {
  const fieldsStore = useFieldsStore();
  if ("repeaterIdx" in field && typeof field.repeaterIdx !== "undefined") {
    return (
      fieldsStore.getRequiredFields.filter(
        (f) => f.alias === field.alias && f.repeaterIdx === field.repeaterIdx,
      ).length > 0
    );
  }

  return (
    fieldsStore.getRequiredFields.filter((f) => f.alias === field.alias)
      .length > 0
  );
};

export function useFieldsRequired(): IUseFieldsRequiredResult {
  return {
    hasRequiredFields,
    resetRequiredFields,
    maybeStartValidation,
    checkFieldRequired,
  };
}
