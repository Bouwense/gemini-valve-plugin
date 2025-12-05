export type CurrencyPositions =
  | 'left'
  | 'right'
  | 'left_with_space'
  | 'right_with_space';

export interface CurrencyFormatOptions {
  numAfterInteger: number;
  thousandsSeparator: string;
  decimalSeparator: string;
  currency: string;
  currencyPosition?: CurrencyPositions;
} 