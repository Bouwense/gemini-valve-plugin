import { defineAsyncComponent } from "vue";
export interface IOrderFormFields {
  name: unknown;
  dropdown: unknown;
  email: unknown;
  phone: unknown;
  text: unknown;
  textarea: unknown;
  space: unknown;
  number: unknown;
  radio: unknown;
  checkbox: unknown;
  "date-picker": unknown;
  "time-picker": unknown;
  "input-textbox": unknown;
  "formatted-text": unknown;
}

export const orderFormFieldRegistry: IOrderFormFields = {
  name: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderName.vue"),
  ),
  dropdown: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderDropdown"),
  ),
  email: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderEmail.vue"),
  ),
  phone: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderPhone.vue"),
  ),
  text: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderText.vue"),
  ),
  textarea: defineAsyncComponent(
    () =>
      import(
        "@/widget/features/submission/order-form/fields/OrderTextarea.vue"
      ),
  ),
  "input-textbox": defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderTextBox.vue"),
  ),
  number: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderNumber.vue"),
  ),
  space: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderSpace.vue"),
  ),
  radio: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderRadio.vue"),
  ),
  checkbox: defineAsyncComponent(
    () =>
      import(
        "@/widget/features/submission/order-form/fields/OrderCheckbox.vue"
      ),
  ),
  "formatted-text": defineAsyncComponent(
    () =>
      import(
        "@/widget/features/submission/order-form/fields/OrderFormattedText.vue"
      ),
  ),
  "date-picker": defineAsyncComponent(
    () =>
      import("@/widget/features/submission/order-form/fields/OrderDate.vue"),
  ),
  "time-picker": defineAsyncComponent(
    () => import("@/widget/features/submission/order-form/fields/OrderTime"),
  ),
};
