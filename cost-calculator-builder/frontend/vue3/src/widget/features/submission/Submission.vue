<template>
  <div>
    <div v-show="!appStore.getSubmissionLoader" style="width: 100%">
      <template v-if="isWooCheckoutOnlyEnabled">
        <div class="ccb-payment-after-submit-btn-wrapper">
          <Button
            :text="translationsStore.getTranslations.addToCart"
            type="success"
            :on-click="addToCartOnlyAction"
          />
        </div>
      </template>
      <template v-else-if="getSubmissionComponent">
        <component :is="getSubmissionComponent" />
      </template>
      <div class="ccb-payment-after-submit-btn-wrapper">
        <Button
          v-if="showButtons"
          :text="getMakePaymentText"
          type="success"
          :on-click="paymentAfterSubmitClickAction"
          :disabled="makePaymentBtnDisabledStatus"
        />
      </div>
      <Transition name="fade">
        <DemoNotice v-if="showDemoNotice" />
      </Transition>
    </div>
    <div
      v-show="appStore.getSubmissionLoader"
      style="width: 100%; opacity: 0.5"
    >
      <Loader type="submit" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, onMounted, ref } from "vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { usePaymentAfterSubmitStore } from "@/widget/app/providers/stores/paymentAfterSubmit.ts";
import Button from "@/widget/shared/ui/components/Button";
import { usePaymentStore } from "@/widget/app/providers/stores/paymentsStore.ts";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore";
import Loader from "@/widget/shared/ui/components/Loader/Loader.vue";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import DemoNotice from "@/widget/shared/ui/components/Demo-notice/DemoNotice.vue";

import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
const settings = useSettingsStore();
const submissionStore = useSubmissionStore();
const paymentAfterSubmitStore = usePaymentAfterSubmitStore();
const paymentStore = usePaymentStore();
const appStore = useAppStore();
const translationsStore = useTranslationsStore();

const getMakePaymentText = computed((): string => {
  const makePayment =
    translationsStore.getTranslations.makePayment || "Make Payment";
  if (getPaymentType.value === "woocommerce") {
    return "Add to cart";
  } else {
    return makePayment;
  }
});

const getPaymentType = computed((): string => {
  const paymentStore = usePaymentStore();
  return paymentStore.paymentType;
});

const getSubmissionComponent = computed(() => {
  const { setIsPaymentAfterSubmit } = usePaymentAfterSubmitStore();
  const formSettings = settings.getFormSettings;
  let paymentEnable: boolean = paymentsStatus.value;

  if (!paymentEnable) {
    paymentEnable = settings.getWooCheckoutSettings?.enable || false;
  }

  if (!isWooCheckoutOnlyEnabled.value && formSettings?.accessEmail) {
    let payments: string[] = getPayments.value;

    if (formSettings?.summaryDisplay?.enable) {
      if (formSettings?.payment && formSettings?.paymentMethods?.length) {
        setIsPaymentAfterSubmit(true);
      }

      if (
        formSettings?.summaryDisplay?.actionAfterSubmit ===
          "show_summary_block_with_pdf" ||
        formSettings?.summaryDisplay?.actionAfterSubmit === "show_summary_block"
      ) {
        if (
          settings.getSummaryDisplayShowSummary &&
          formSettings?.payment &&
          formSettings?.paymentMethods?.length
        ) {
          return defineAsyncComponent(
            () => import("@/widget/features/submission/payments"),
          );
        }
      }

      return defineAsyncComponent(
        () => import("@/widget/features/submission/summary-block"),
      );
    }

    if (!formSettings?.payment || !payments.length) {
      return defineAsyncComponent(
        () => import("@/widget/features/submission/order-form"),
      );
    }

    setIsPaymentAfterSubmit(true);

    return defineAsyncComponent(
      () => import("@/widget/features/submission/payment-after-submit"),
    );
  } else if (paymentEnable) {
    return defineAsyncComponent(
      () => import("@/widget/features/submission/payments"),
    );
  }

  return "";
});

const paymentAfterSubmitClickAction = () => {
  submissionStore.submissionCreateOrder();
};

const isPaypalEnabled = computed((): boolean => {
  const paymentsSettings = settings.paymentGateway;
  return !!(
    paymentsSettings?.paypal?.enable && paymentsSettings?.paypal?.paypalEmail
  );
});

const isCashPaymentEnabled = computed((): boolean => {
  const paymentsSettings = settings.paymentGateway;
  return !!paymentsSettings?.cashPayment?.enable;
});

const isStripeEnabled = computed((): boolean => {
  const paymentsSettings = settings.paymentGateway;
  const stripe = paymentsSettings?.cards?.cardPayments?.stripe;
  return !!(stripe?.enable && stripe?.secretKey && stripe?.secretKey);
});

const isRazorpayEnabled = computed((): boolean => {
  const paymentsSettings = settings.paymentGateway;
  const razorpay = paymentsSettings?.cards?.cardPayments?.razorpay;
  return !!(razorpay?.enable && razorpay?.keyId && razorpay?.secretKey);
});

const paymentsStatus = computed((): boolean => {
  if (settings.getFormSettings?.accessEmail) return false;

  return (
    isPaypalEnabled.value ||
    isCashPaymentEnabled.value ||
    isStripeEnabled.value ||
    isRazorpayEnabled.value
  );
});

const getPayments = computed(() => {
  const formSettings = settings.getFormSettings;
  let payments: string[] = [];
  if (formSettings?.payment) {
    payments = formSettings.paymentMethods || [];
  }

  return payments;
});

const isWooCheckoutOnlyEnabled = computed((): boolean => {
  const formSettings = settings.getFormSettings;
  let payments: string[] = getPayments.value;

  if (formSettings?.payment && payments.length) return false;

  return (
    (settings.getWooCheckoutSettings?.enable && !paymentsStatus.value) || false
  );
});

const showButtons = computed(() => {
  return (
    paymentAfterSubmitStore.isPaymentAfterSubmit &&
    paymentAfterSubmitStore.getSubmit
  );
});

const makePaymentBtnDisabledStatus = computed(() => {
  const fieldsStore = useFieldsStore();
  return !(paymentStore.paymentType && fieldsStore.checkRequiredFields());
});

const showDemoNotice = ref(false);
const isLive = computed(() => {
  return appStore.getIsLive;
});

const addToCartOnlyAction = () => {
  if (isLive.value) {
    showDemoNotice.value = true;
    setTimeout(() => {
      showDemoNotice.value = false;
    }, 5000);
    return;
  }

  const fieldsStore = useFieldsStore();
  if (fieldsStore.checkRequiredFields()) {
    paymentStore.updatePaymentType("woocommerce");
    submissionStore.submissionCreateOrder();
  }
};

const initListeners = () => {
  window.addEventListener("ccbWooCheckout", () => {
    paymentStore.updatePaymentType("woocommerce");
    submissionStore.submissionCreateOrder();
  });
};

onMounted(() => {
  initListeners();
});
</script>

<style scoped lang="scss">
.ccb-payment-after-submit-btn-wrapper {
  width: 100%;
  display: flex;

  button {
    width: 100%;
  }
  & ~ .ccb-demo-notice {
    margin-top: 20px;
    display: block;
  }
}
</style>
