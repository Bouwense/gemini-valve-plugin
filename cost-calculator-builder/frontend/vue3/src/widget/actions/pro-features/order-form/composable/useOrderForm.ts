import { IFormField } from "@/widget/shared/types/fields";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { useNotificationsStore } from "@/widget/app/providers/stores/notificationsStore.ts";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import { splitByLastUnderscore } from "@/widget/shared/utils/split-by-last-underscore.utils.ts";
import { useOrderFormFieldsRequired } from "./useOrderFormFieldsRequired";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore";
import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore";

const { maybeStartOrderFormValidation, checkOrderFormRequiredFields } =
  useOrderFormFieldsRequired();

interface OrderFormResult {
  initializeFields: (rowData: any[]) => void;
  submitForm: () => void;
  validateOrderFormSettings: () => boolean;
  updateValue: (id: number, value: any, idx?: number[]) => void;
}

function initializeFields(rawData: any[]): void {
  const orderFormStore = useOrderFormStore();
  orderFormStore.updateFormFields(
    rawData.map((field) => {
      const attr = field.attributes;

      let defaultValue = attr.default_value;
      let selectedIdx: number[] = [];
      if (field.type === "checkbox" || field.type === "dropdown") {
        defaultValue = defaultValue.map((option: string) => {
          const lastWithSpace: string[] = option.split(" ");
          const last: string = lastWithSpace[lastWithSpace.length - 1];
          const idx = +last.split("_")[1];
          if (!isNaN(idx)) {
            selectedIdx.push(idx);
          }
          return splitByLastUnderscore(option);
        });
      }

      if (field.type === "formatted-text") {
        defaultValue = field.attributes.text;
      }

      return {
        id: +field.id,
        type: field.type,
        label: attr.label || "",
        placeholder: attr.placeholder || "",
        required: attr.required === "1",
        value: defaultValue,
        sortId: Number(field.sort_id || 0),
        fieldWidth: Number(field.field_width || 0),
        ...(selectedIdx.length ? { selectedIdx } : {}),
        attributes: {
          ...(attr.confirmation
            ? { confirmation: attr.confirmation === "1" }
            : {}),
          ...(attr.confirmation_label
            ? { confirmationLabel: attr.confirmation_label }
            : {}),
          ...(attr.confirmation_placeholder
            ? { confirmationPlaceholder: attr.confirmation_placeholder }
            : {}),
          ...(attr.position
            ? {
                position:
                  attr.position.toLowerCase() === "horizontal"
                    ? "horizontal"
                    : "vertical",
              }
            : {}),
          ...(attr.display
            ? {
                display:
                  attr.display.toLowerCase() === "horizontal"
                    ? "horizontal"
                    : "vertical",
              }
            : {}),
          ...(attr.primary
            ? {
                primary: attr.primary === "1",
              }
            : {}),
          ...(attr.character_limit
            ? { characterLimit: Number(attr.character_limit || 0) }
            : {}),
          ...(attr.multiselect
            ? { multiselect: attr.multiselect === "1" }
            : {}),
          ...(attr.range ? { range: attr.range === "1" } : {}),
          ...(attr.format ? { format: attr.format === "1" } : {}),
          ...(attr.options ? { options: attr.options } : {}),
        },
      };
    }),
  );
}

function validateForm(): boolean {
  const orderFormStore = useOrderFormStore();

  orderFormStore.updateErrors({});
  orderFormStore.getFormFields.forEach((field: IFormField) => {
    if (field.required && !field.value) {
      orderFormStore.updateErrors({
        [field.id]: `${field.label} is required`,
      });
    }
  });

  return Object.keys(orderFormStore.getErrors).length === 0;
}

function validateOrderFormSettings(): boolean {
  const orderFormStore = useOrderFormStore();
  const settingsStore = useSettingsStore();
  const notificationsStore = useNotificationsStore();
  const appStore = useAppStore();
  const summaryDisplay = settingsStore.getFormSettings?.summaryDisplay;
  const fieldsStore = useFieldsStore();

  if (summaryDisplay?.enable && !fieldsStore.checkRequiredFields()) {
    return true;
  }

  const hasRequiredFields: boolean = checkOrderFormRequiredFields(
    orderFormStore.getFormFields,
  );

  settingsStore.setTermsAndConditionsTrigger(true);

  if (hasRequiredFields || settingsStore.getTermsAndConditionsStatus) {
    return true;
  }

  settingsStore.setTermsAndConditionsTrigger(false);

  const adminEmailAddress = settingsStore.getFormSettings?.adminEmailAddress;
  const emailSubject = settingsStore.getFormSettings?.emailSubject;

  if (!adminEmailAddress || !emailSubject) {
    const link =
      "https://docs.stylemixthemes.com/cost-calculator-builder/pro-plugin-features/send-form";
    notificationsStore.updateNotifications({
      status: true,
      type: "error",
      message: "Error: Missing Settings!",
      description: `Please fill in all necessary fields in the settings to complete your <span style="font-weight: 700">Order Form</span> <a href="${link}" target="_blank">setup</a> in "${appStore.getCalcTitle}"`,
    });

    return true;
  }
  return false;
}

function submitForm(): void {
  if (!validateForm()) {
    return;
  }
}

function updateValue(id: number, value: any, idx?: number[]): void {
  const orderFormStore = useOrderFormStore();
  const formFields = orderFormStore.getFormFields;

  for (let i = 0; i < formFields.length; i++) {
    const field = formFields[i];
    if (+field.id === id) {
      formFields[i].value = value;

      if (typeof idx !== "undefined") {
        field.selectedIdx = idx;
      }
    }
  }

  orderFormStore.updateFormFields(formFields);

  maybeStartOrderFormValidation(formFields);
}

export function useOrderForm(): OrderFormResult {
  return {
    validateOrderFormSettings,
    initializeFields,
    submitForm,
    updateValue,
  };
}
