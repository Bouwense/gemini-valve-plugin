import { ref } from "vue";
import { defineStore } from "pinia";
import { IPaymentTypes } from "@/widget/actions/pro-features/payments/paymentTypeRegistry.ts";
import type { Stripe } from "@stripe/stripe-js";
import { useFieldsStore } from "./fieldsStore";
import { useAppStore } from "./appStore";
import { StripeData } from "@/widget/shared/types/settings/settings.type.ts";

type PaymentTypes = "" | keyof IPaymentTypes;

export const usePaymentStore = defineStore("payment_store", () => {
  const stripeInstance = ref<Stripe | null>(null);
  const cardComponent = ref<any>(null);
  const paymentType = ref<PaymentTypes>("");
  const showForm = ref<boolean>(false);
  const paymentMethodId = ref<string>("");
  const paymentIntentId = ref<string>("");
  const paymentAfterSubmitStoredPm = ref<string>("");

  const initializeStripe = async (
    stripeData: StripeData | null,
  ): Promise<void> => {
    if (
      !stripeInstance.value &&
      stripeData?.enable &&
      stripeData?.publishKey &&
      stripeData?.secretKey
    ) {
      try {
        const { loadStripe } = await import("@stripe/stripe-js");
        stripeInstance.value = await loadStripe(stripeData.publishKey);
      } catch (error) {
        console.error("Failed to load Stripe:", error);
        stripeInstance.value = null;
      }
    }
  };

  const setCardComponent = (component: any): void => {
    cardComponent.value = component;
  };

  const updatePaymentType = (value: PaymentTypes): void => {
    if (value !== paymentType.value) {
      paymentType.value = value;
      const fieldsStore = useFieldsStore();

      // IF demo or live site ( demonstration only )
      const appStore = useAppStore();
      if (appStore.getIsLive) {
        showForm.value = true;
      }
      // END| IF demo or live site ( demonstration only )

      if (fieldsStore.checkRequiredFields()) {
        showForm.value = true;
      }
    }
  };

  const setPaymentMethodId = (value: string): void => {
    paymentMethodId.value = value;
  };

  const setPaymentIntentId = (value: string): void => {
    paymentIntentId.value = value;
  };

  const setPaymentAfterSubmitStoredPm = (value: string): void => {
    paymentAfterSubmitStoredPm.value = value;
  };

  const reset = (): void => {
    paymentType.value = "";
    showForm.value = false;
    paymentAfterSubmitStoredPm.value = "";
  };

  return {
    reset,
    updatePaymentType,
    setPaymentMethodId,
    setPaymentIntentId,
    showForm,
    paymentType,
    stripeInstance,
    cardComponent,
    initializeStripe,
    setCardComponent,
    paymentMethodId,
    paymentIntentId,
    paymentAfterSubmitStoredPm,
    setPaymentAfterSubmitStoredPm,
  };
});
