export interface ICalculatorFields {
  id: number;
  label: string;
  value: string;
  alias: string;
  sort_id: number;
  add_to_summary: boolean;
  converted: string;
  is_basic: boolean;
  option_unit: string;
  options: Array<any>;
  summary_view: string;
}

export interface IOrderFormDetails {
  id: number;
  label: string;
  value: string;
  type?: string;
  sort_id: number;
}

export interface ITotals {
  alias: string;
  id: number;
  is_primary: boolean;
  label: string;
  original_value: number;
  sort_id: number;
  value: string;
  converted: string;
}

export interface ICurrency {
  number_of_decimals: number;
  thousand_separator: string;
  decimal_separator: string;
  currency_position: string;
  currency_sign: string;
}

export interface IDiscounts {}

export interface IPromocodes {}

export interface IOrders {
  order_id: number;
  calc_id: number;
  calc_title: string;
  total_amount: number;
  old_order_id: number;

  calculator_fields: ICalculatorFields[];
  order_form_details: IOrderFormDetails[];
  totals: ITotals[];
  other_totals: ITotals[];
  promocodes: IPromocodes[];
  currency: ICurrency;

  payment_status: string;
  payment_type: string;
  payment_type_label: string;
  payment_status_slug: string;
  client_email: string;
  client_name: string;
  created_at: string;
  formatted_date: string;
  notes: IOrderNote[];
}

export interface IStatuses {
  id: number;
  sort_id: number;
  status_name: string;
  slug: string;
  color: string;
  is_default: boolean;
  is_completed: boolean;
  count: number;
}

export interface IError {
  key: string;
  message: string;
}

export interface IOrdersParams {
  filter: {
    status?: number;
  };
  search: {
    id: string;
    name: string;
    email: string;
    calculator: string;
    payment: string;
    status?: number[];
  };
  sort: {
    id: "asc" | "desc" | "";
    amount: "asc" | "desc" | "";
  };
}

export interface IActions {
  label: string;
  action: string;
  is_danger: boolean;
}

export interface IAttachments {
  file: string;
  url: string;
  type: string;
}

export interface IOrderNote {
  id: number;
  content: string;
  created_at: string;
}
