export type BtnPositions =
  | "bottom_left"
  | "top_left"
  | "center_left"
  | "top_center"
  | "top_right"
  | "center_right"
  | "bottom_right"
  | "bottom_center";

export type ClickActions =
  | "scroll_to"
  | "open_modal"
  | "pro_features"
  | "pdf"
  | "invoice"
  | "woo_product_as_modal"
  | "woo_product_with_redirect"
  | "woo_checkout";

export type StickyFormulas = {
  idx: number;
  title: string;
  alias: string;
};

export interface IStickySettings {
  enable: boolean;
  displayType: "btn" | "banner";
  btnPosition: BtnPositions;
  bannerPosition: "bottom" | "top";
  hideIcon: boolean;
  title: "calc_title" | "custom";
  description: string;
  btnText: string;
  oneClickAction: ClickActions;
  showCalculator: boolean;
  showTotal: boolean;
  customText: string;
  customDesc: string;
  icon: string;
  classes: string;
  formulas: StickyFormulas[];
}

export interface IStickyWooCheckout {
  formulas: StickyFormulas[];
}
