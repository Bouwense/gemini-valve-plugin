import { computed } from "vue";
import { usePaymentStore } from "@/widget/app/providers/stores/paymentsStore.ts";
import { IPaymentTypes } from "@/widget/actions/pro-features/payments/paymentTypeRegistry.ts";

const paymentStore = usePaymentStore();

export function useSinglePayment() {
  const paymentType = computed({
    get: () => paymentStore.paymentType,
    set: (newValue: keyof IPaymentTypes) => {
      paymentStore.updatePaymentType(newValue);
    },
  });

  return {
    paymentType,
  };
}
