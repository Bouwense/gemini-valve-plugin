export interface CurrencySettings {
  currency: string;
  currencyPosition: string;
  numAfterInteger: number;
  thousandsSeparator: string;
  decimalSeparator: string;
}

export function currencyConvertor(
  amount: number,
  settings: CurrencySettings,
): string {
  const {
    currency = "",
    currencyPosition = "left_with_space",
    numAfterInteger = 2,
    thousandsSeparator = ",",
    decimalSeparator = ".",
  } = settings;

  const fixed = amount.toFixed(numAfterInteger);

  const [integerPart, fractionalPart] = fixed.split(".");

  const formattedInt = integerPart.replace(
    /\B(?=(\d{3})+(?!\d))/g,
    thousandsSeparator,
  );

  const formattedNumber =
    numAfterInteger > 0
      ? `${formattedInt}${decimalSeparator}${fractionalPart}`
      : formattedInt;

  switch (currencyPosition) {
    case "left_with_space":
      return `${currency || ""} ${formattedNumber}`;
    case "right_with_space":
      return `${formattedNumber} ${currency || ""}`;
    case "left":
      return `${currency || ""}${formattedNumber}`;
    case "right":
      return `${formattedNumber}${currency || ""}`;
  }

  return formattedNumber;
}
