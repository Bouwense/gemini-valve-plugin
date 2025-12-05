import { computed, ref } from "vue";
import { defineStore } from "pinia";

export const usePaymentAfterSubmitStore = defineStore(
  "payment_after_submit_store",
  () => {
    const submit = ref<boolean>(false);
    const lastOrderId = ref<undefined | number>();
    const isPaymentAfterSubmit = ref<boolean>(false);

    const setIsPaymentAfterSubmit = (value: boolean): void => {
      isPaymentAfterSubmit.value = value;
    };

    const setLastOrderId = (orderId?: number): void => {
      lastOrderId.value = orderId;
    };

    const setSubmit = (value: boolean): void => {
      if (!value && !isPaymentAfterSubmit.value) {
        lastOrderId.value = undefined;
      }

      submit.value = value;
    };

    const getSubmit = computed((): boolean => {
      return submit.value;
    });

    const getLastOrderId = computed((): number | undefined => {
      return lastOrderId.value;
    });

    return {
      getSubmit,
      getLastOrderId,
      isPaymentAfterSubmit,
      setSubmit,
      setIsPaymentAfterSubmit,
      setLastOrderId,
    };
  },
);
