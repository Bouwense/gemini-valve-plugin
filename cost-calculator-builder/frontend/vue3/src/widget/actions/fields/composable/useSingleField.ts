import {
  Field,
  FieldWithMultipleDisplayView,
} from "@/widget/shared/types/fields";
import { useCurrency } from "./useCurrency.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

interface IUseSingleFieldResult {
  getMultipleOptionsFieldDisplayView: (
    field: FieldWithMultipleDisplayView,
  ) => string | string[];

  parseCommonFieldSign: (
    value: string,
    sign: string,
    position: "left" | "right",
  ) => string;
  getCommonFieldDisplayView: (field: Field, originalValue?: string) => string;
}

export function parseCommonFieldSign(
  value: string,
  sign: string,
  position: "left" | "right" = "right",
) {
  if (position === "left") {
    return `${sign} ${value}`;
  }

  return `${value} ${sign}`;
}

function getMultipleOptionsFieldDisplayView(
  field: FieldWithMultipleDisplayView,
): string | string[] {
  const currencyInstance = useCurrency();
  let displayValue: string | string[] = "";

  if (field) {
    switch (field.summaryView) {
      case "show_value":
        displayValue = field.value?.toString() || "0";
        if (field.useCurrency || field.fieldCurrency) {
          displayValue = currencyInstance.formatCurrency(
            +displayValue,
            currencyInstance.getCurrencyOptions(field),
          );
        }
        break;
      case "show_label_calculable":
      case "show_label_not_calculable":
        if (!Array.isArray(field?.selectedOption)) {
          displayValue = "";
          if (field?.selectedOption?.optionText) {
            displayValue = field.selectedOption.optionText;
          }
        } else if (Array.isArray(field.selectedOption)) {
          displayValue = [];
          for (const option of field.selectedOption) {
            displayValue.push(option.optionText);
          }
        }
        break;
    }
  }

  return displayValue || "";
}

function getCommonFieldDisplayView(
  field: Field,
  originalValue?: string,
): string {
  const currencyInstance = useCurrency();

  if ("originalValue" in field) {
    originalValue = field.originalValue?.toString() || "";
  }

  const settingsStore = useSettingsStore();
  let displayValue = field.value?.toString();

  if (
    (field.useCurrency || field.fieldCurrency) &&
    settingsStore.getCurrencySettings?.useInAll
  ) {
    displayValue = currencyInstance.formatCurrency(
      field.value || 0,
      currencyInstance.getCurrencyOptions(field),
    );
  } else {
    displayValue = displayValue?.replace(
      ".",
      currencyInstance.getCurrencyOptions(field).decimalSeparator,
    );
  }

  if ("sign" in field) {
    if (field.multiply) {
      const leftValue = parseCommonFieldSign(
        originalValue || "",
        field.sign,
        field.unitPosition,
      );

      const rightValue =
        field.useCurrency || field.fieldCurrency
          ? currencyInstance.formatCurrency(
              field.unit,
              currencyInstance.getCurrencyOptions(field),
            )
          : parseCommonFieldSign(
              field.unit.toString(),
              field.unitSymbol,
              field.unitPosition,
            );

      field.extraDisplayView = `${leftValue} x ${rightValue}`;
    } else {
      displayValue =
        field.useCurrency || field.fieldCurrency
          ? displayValue
          : parseCommonFieldSign(
              displayValue?.toString() || "",
              field.sign,
              field.unitPosition,
            );
    }
  }

  return displayValue || "";
}

export function useSingleField(): IUseSingleFieldResult {
  return {
    getMultipleOptionsFieldDisplayView,
    getCommonFieldDisplayView,
    parseCommonFieldSign,
  };
}
