<template>
  <div class="ccb-payment">
    <label>
      <div class="ccb-payment-header">
        <div class="ccb-payment-header__label">
          <input
            type="radio"
            name="paymentMethods"
            value="stripe"
            v-model="paymentType"
          />
          <span class="ccb-radio-label">Stripe</span>
        </div>
        <div class="calc-payment_header__img">
          <img
            :src="stripeImg"
            alt="card-payment-image"
            style="max-width: 112px"
          />
        </div>
      </div>
      <div class="ccb-payment-body" v-show="paymentType === 'stripe'">
        <div ref="cardComponent"></div>
      </div>
    </label>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import stripeImg from "@/images/payments/card_payment.webp";
import { useSinglePayment } from "@/widget/actions/pro-features/payments/composable/useSinglePayment.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { usePaymentStore } from "@/widget/app/providers/stores/paymentsStore.ts";
import type { StripeElements } from "@stripe/stripe-js";

const paymentStore = usePaymentStore();
const settingsStore = useSettingsStore();
const { paymentType } = useSinglePayment();
const stripeLoaded = ref<boolean>(false);

const cardComponent = ref<HTMLElement | null>(null);
const stripeElements = ref<StripeElements | null>(null);

onMounted(async () => {
  const stripeData = settingsStore.getStripeData;
  await paymentStore.initializeStripe(stripeData);

  stripeLoaded.value = true;
  if (paymentStore.stripeInstance) {
    stripeElements.value = paymentStore.stripeInstance?.elements();
    const card = stripeElements.value?.create("card");
    card?.mount(cardComponent.value!);

    paymentStore.setCardComponent(card);
  }
});
</script>

<style lang="scss" scoped>
.ccb-payment {
  padding: 0 17px;
  background-color: var(--ccb-fields-bg-color);
  border-radius: 4px;
  cursor: pointer;

  label {
    .ccb-payment-body {
      padding: 15px 0;
    }
    .ccb-payment-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 13px 0;

      .ccb-payment-header__label {
        display: flex;
        align-items: center;

        .ccb-radio-label {
          margin-left: 10px;
        }
      }

      .calc-payment_header__img {
        display: flex;
        img {
          max-width: 112px;
        }
      }
    }
  }
}
</style>
