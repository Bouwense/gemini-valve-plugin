export interface WooMetaAction {
  wooMeta: "price" | "quantity";
  action: "set_value" | "set_value_disable";
  calcField: string;
} 