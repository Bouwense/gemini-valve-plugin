import { defineAsyncComponent } from "vue";
export interface IPaymentTypes {
  stripe: unknown;
  paypal: unknown;
  cash: unknown;
  razorpay: unknown;
  woocommerce: unknown;
}

export const paymentTypeRegistry: IPaymentTypes = {
  stripe: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/payments/payment-types/Stripe.vue"),
  ),
  paypal: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/payments/payment-types/Paypal.vue"),
  ),
  cash: defineAsyncComponent(
    () =>
      import("@/widget/features/submission/payments/payment-types/Cash.vue"),
  ),
  razorpay: defineAsyncComponent(
    () =>
      import(
        "@/widget/features/submission/payments/payment-types/RazorPay.vue"
      ),
  ),
  woocommerce: defineAsyncComponent(
    () =>
      import(
        "@/widget/features/submission/payments/payment-types/Woocommerce.vue"
      ),
  ),
};
