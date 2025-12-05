import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import {
  CurrencyFormatOptions,
  CurrencyPositions,
} from "@/widget/shared/types/common";
import { Field } from "@/widget/shared/types/fields";

interface IUseCurrencyResult {
  getCurrencyOptions: (field?: Field) => CurrencyFormatOptions;
  formatCurrency: (
    value: number,
    options: CurrencyFormatOptions | undefined,
  ) => string;
  parseCurrency: (
    value: string,
    options: CurrencyFormatOptions | undefined,
  ) => number;
  parseCurrencyPosition: (
    value: string,
    currency: string,
    position: CurrencyPositions,
  ) => string;
}

export function useCurrency(): IUseCurrencyResult {
  function getCurrencyOptions(field?: Field): CurrencyFormatOptions {
    const settingsStore = useSettingsStore();

    if (
      typeof field !== "undefined" &&
      "fieldCurrencySettings" in field &&
      field?.fieldCurrency &&
      field?.fieldCurrencySettings
    ) {
      return field?.fieldCurrencySettings;
    }

    return settingsStore.getCurrencySettings as CurrencyFormatOptions;
  }

  function formatCurrency(
    value: number,
    options: CurrencyFormatOptions | undefined,
  ): string {
    if (typeof options === "undefined") {
      return value.toString();
    }

    const numAfterInteger = options?.numAfterInteger || 2;
    const thousandsSeparator = options?.thousandsSeparator || ",";
    const decimalSeparator = options?.decimalSeparator || ".";
    const currency = options?.currency || "";
    const currencyPosition = options?.currencyPosition || "left";

    // Ensure correct decimal places
    let innerValue = Number(value).toFixed(numAfterInteger);

    // Split integer and decimal parts
    let [integerPart, decimalPart] = innerValue.split(".");

    // Add thousands separator
    integerPart = integerPart.replace(
      /\B(?=(\d{3})+(?!\d))/g,
      thousandsSeparator,
    );

    const formattedValue =
      integerPart + (decimalPart ? decimalSeparator + decimalPart : "");

    return parseCurrencyPosition(formattedValue, currency, currencyPosition);
  }

  /**
   * Parses a formatted currency string into a number.
   */
  function parseCurrency(
    inputValue: string,
    options: CurrencyFormatOptions | undefined,
  ): number {
    if (typeof options === "undefined") {
      return +inputValue;
    }

    const numAfterInteger = options?.numAfterInteger || 2;
    const thousandsSeparator = options?.thousandsSeparator || ",";
    const decimalSeparator = options?.decimalSeparator || ".";
    const currency = options?.currency || "";

    let value = inputValue.replace(currency, "").trim();

    let parts;
    if (value.includes(".") && value.includes(",")) {
      parts = value.split(decimalSeparator);
    } else if (value.includes(",")) {
      parts = value.split(",");
    } else if (value.includes(".")) {
      parts = value.split(".");
    } else {
      parts = [value];
    }

    parts[0] = parts[0].split(thousandsSeparator).join("");

    let sanitizedValue = parts.join(".");
    let result = parseFloat(sanitizedValue) || 0;

    if (numAfterInteger !== undefined) {
      let factor = Math.pow(10, numAfterInteger);
      result = Math.round(result * factor) / factor;
    }

    return result;
  }

  function parseCurrencyPosition(
    value: string,
    currency: string,
    position: CurrencyPositions = "left",
  ): string {
    switch (position) {
      case "left":
        return `${currency}${value}`;
      case "right":
        return `${value}${currency}`;
      case "left_with_space":
        return `${currency} ${value}`;
      case "right_with_space":
        return `${value} ${currency}`;
      default:
        return value;
    }
  }

  return {
    getCurrencyOptions,
    formatCurrency,
    parseCurrencyPosition,
    parseCurrency,
  };
}
