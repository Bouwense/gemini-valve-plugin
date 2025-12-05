export interface IDiscountCondition {
  discountConditionId: number;
  discountId: number;
  fieldAlias: string; // Can contain multiple total fields (comma-separated)
  overPrice: number;
  discountAmount: number;
  conditionSymbol: '>' | '<' | '=';
  discountType: 'percent_of_amount' | 'fixed_amount';
  isPromo?: boolean;
  promocode?: string;
}

export interface IDiscount {
  discountId: number;
  title: string;
  calcId: number;
  isPromo: boolean;
  viewType: 'show_with_title' | 'show_without_title';
  period: 'single_day' | 'period' | 'permanently';
  periodStartDate?: string;
  periodEndDate?: string;
  singleDate?: string;
  discountStatus: 'active' | 'inactive';
  promocode?: string;
  promocodeCount?: number;
  promocodeUsed?: number;
  conditions: IDiscountCondition[];
}

export type DiscountsStore = Record<string, IDiscountCondition[]>;
export type OriginalDiscountsStore = Record<number, IDiscount>;

export interface ITotalDiscount {
  discountType: string;
  isPromo?: boolean;
  promocode?: string;
  viewType: string;
  discountName: string;
  extraView: string;
} 