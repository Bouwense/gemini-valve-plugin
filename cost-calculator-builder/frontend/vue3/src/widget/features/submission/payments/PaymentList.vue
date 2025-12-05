<template>
  <div class="ccb-payments" :class="{ 'is-live': appStore.getIsLive }">
    <div class="ccb-payments__title">
      {{ translationsStore.getTranslations.paymentMethods }}
      <ProBadge />
    </div>
    <div class="ccb-payments__list">
      <PaymentMethod
        v-for="payment in getPayments"
        :type="payment"
        :key="payment"
      />
    </div>
  </div>

  <OrderForm style="padding-top: 20px" v-if="!payment" :payment="true" />
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { IPaymentTypes } from "@/widget/actions/pro-features/payments/paymentTypeRegistry.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";
import PaymentMethod from "@/widget/features/submission/payments/PaymentMethod.vue";
import OrderForm from "@/widget/features/submission/order-form";
import { useAppStore } from "@/widget/app/providers/stores/appStore";

type Props = {
  payment?: boolean;
};

const props = defineProps<Props>();
const { payment } = toRefs(props);

const translationsStore = useTranslationsStore();
const appStore = useAppStore();

const getPayments = computed(() => {
  const settingsStore = useSettingsStore();

  const payments: Array<keyof IPaymentTypes> = [];
  const paymentGateway = settingsStore.getPaymentGateway;
  const formSettings = settingsStore.getFormSettings;
  const paymentMethods: string[] = formSettings?.paymentMethods || [];
  const keys: Record<string, string> = {
    cash: "cash_payment",
    woocommerce: "woo_checkout",
  };

  if (paymentGateway) {
    if (
      paymentGateway.cards?.cardPayments?.stripe?.enable &&
      paymentGateway.cards?.cardPayments?.stripe?.publishKey?.length &&
      paymentGateway.cards?.cardPayments?.stripe?.secretKey?.length
    ) {
      payments.push("stripe");
    }

    if (
      paymentGateway.cards?.cardPayments?.razorpay?.enable &&
      paymentGateway.cards?.cardPayments?.razorpay?.keyId?.length &&
      paymentGateway.cards?.cardPayments?.razorpay?.secretKey?.length
    ) {
      payments.push("razorpay");
    }

    if (paymentGateway.paypal?.enable && paymentGateway.paypal.paypalEmail) {
      payments.push("paypal");
    }

    if (paymentGateway.cashPayment?.enable) {
      payments.push("cash");
    }

    if (settingsStore.getWooCheckoutSettings?.enable) {
      payments.push("woocommerce");
    }
  }

  if (
    settingsStore.getFormSettings?.accessEmail &&
    settingsStore.getFormSettings?.payment
  ) {
    return payments.filter((p: string) =>
      paymentMethods.includes(keys[p] ? keys[p] : p),
    );
  }

  return payments;
});
</script>

<style lang="scss">
.ccb-payments {
  &.is-live {
    padding: 15px 15px;
    background: #f7f8fb;
    border: 1px solid #eaeaea;
    border-radius: 6px;
    .ccb-payments__title {
      font-size: 12px;
      font-weight: 700;
      color: #001931;
      margin-bottom: 21px;
      .is-pro {
        top: 0;
      }
    }
    .ccb-payments__list {
      .ccb-payment {
        background-color: #0019310d;
        .ccb-payment-body {
          background-color: transparent;
        }
        label {
          margin-bottom: 0;
        }
        .ccb-payment-header {
          .ccb-payment-header__label {
            font-size: 14px;
            font-weight: 700;
            color: #001931;
          }
        }
      }
    }
  }
  .ccb-payments__title {
    font-size: var(--ccb-summary-text-size);
    font-weight: var(--ccb-field-weight);
    color: var(--ccb-text-color);
    margin-bottom: 6px;

    @media only screen and (max-width: 480px) {
      font-weight: var(--ccb-mobile-field-weight);
      font-size: var(--ccb-mobile-summary-text-size);
    }
  }

  .ccb-payments__list {
    display: flex;
    flex-direction: column;
    row-gap: 10px;
  }

  .ccb-payment-body {
    border-top: 1px solid #dddddd;
    background: var(--ccb-fields-bg-color);
    padding: 7px 0 10px;

    span {
      font-size: var(--ccb-summary-text-size);
      font-weight: var(--ccb-summary-text-weight);
      color: #00193180 !important;

      @media only screen and (max-width: 480px) {
        font-size: var(--ccb-mobile-summary-text-size);
        font-weight: var(--ccb-mobile-summary-text-weight);
      }
    }
  }
}
</style>
