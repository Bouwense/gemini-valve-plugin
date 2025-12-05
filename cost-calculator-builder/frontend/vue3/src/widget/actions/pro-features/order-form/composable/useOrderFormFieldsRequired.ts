import { IFormField } from "@/widget/shared/types/fields";
import { validateEmail } from "@/widget/shared/utils/validate-email.utils";
import { ref } from "vue";

const orderFormRequiredFields = ref<IFormField[]>([]);

interface IOrderFormFieldsRequiredResult {
  checkOrderFormRequiredFields: (fieldsData: IFormField[]) => boolean;
  maybeStartOrderFormValidation: (fieldsData: IFormField[]) => void;
  clearOrderFormRequiredFields: () => void;
  checkFieldRequired: (field: IFormField) => boolean;
}

const nameFieldValidation = (field: IFormField): boolean => {
  if (field.required) return field.type === "name" && !field.value?.trim();
  return false;
};

const emailFieldValidation = (field: IFormField): boolean => {
  if (field.required) {
    return (
      field.type === "email" &&
      (!field.value?.trim() || !validateEmail(field.value))
    );
  }

  return (
    field.type === "email" &&
    (field.value?.trim() === "" || !validateEmail(field.value))
  );
};

const textFieldValidation = (field: IFormField): boolean => {
  let characterLimit: number = 0;

  if ("characterLimit" in field.attributes) {
    characterLimit = field.attributes.characterLimit || 0;
  }

  if (["textarea", "input-textbox"].includes(field.type)) {
    if (field.required) {
      return (
        !field.value?.trim() || (field.value?.length || 0) > characterLimit
      );
    }

    return (
      field.value?.trim() === "" || (field.value?.length || 0) > characterLimit
    );
  }

  return false;
};

const checkCheckboxFieldValidation = (field: IFormField): boolean => {
  if (field.required) {
    return field.type === "checkbox" && field.value.length === 0;
  }

  return false;
};

const commonFieldValidation = (field: IFormField): boolean => {
  if (field.required && ["phone", "number"].includes(field.type)) {
    return field.value?.trim() === "";
  }

  return (
    field.required &&
    ["dropdown", "radio", "time-picker", "date-picker"].includes(field.type) &&
    !field.value
  );
};

const filterOrderFormRequiredFields = (fieldsData: IFormField[]): void => {
  clearOrderFormRequiredFields();
  const fields = fieldsData.filter((field) => {
    return (
      nameFieldValidation(field) ||
      emailFieldValidation(field) ||
      commonFieldValidation(field) ||
      textFieldValidation(field) ||
      checkCheckboxFieldValidation(field)
    );
  });

  orderFormRequiredFields.value = fields;
};

const checkOrderFormRequiredFields = (fieldsData: IFormField[]): boolean => {
  filterOrderFormRequiredFields(fieldsData);
  return orderFormRequiredFields.value.length > 0;
};

const checkFieldRequired = (field: IFormField): boolean => {
  return (
    orderFormRequiredFields.value.filter((f) => f.id === field.id).length > 0
  );
};

const maybeStartOrderFormValidation = (fieldsData: IFormField[]): void => {
  if (orderFormRequiredFields.value.length > 0) {
    filterOrderFormRequiredFields(fieldsData);
  }
};

const clearOrderFormRequiredFields = (): void => {
  orderFormRequiredFields.value = [];
};

export function useOrderFormFieldsRequired(): IOrderFormFieldsRequiredResult {
  return {
    checkOrderFormRequiredFields,
    clearOrderFormRequiredFields,
    maybeStartOrderFormValidation,
    checkFieldRequired,
  };
}
