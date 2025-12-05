<template>
  <div class="ccb-payment">
    <label>
      <div class="ccb-payment-header">
        <div class="ccb-payment-header__label">
          <input
            type="radio"
            name="paymentMethods"
            value="cash"
            v-model="paymentType"
          />
          <span class="ccb-radio-label">{{ cashPaymentLabel }}</span>
        </div>
        <div class="calc-payment_header__img">
          <img
            :src="cashImg"
            alt="card-payment-image"
            style="max-width: 32px"
          />
        </div>
      </div>
      <div
        class="ccb-payment-body"
        v-if="paymentType === 'cash' && cashPaymentType"
      >
        <span>{{ cashPaymentType }}</span>
      </div>
    </label>
  </div>
</template>

<script setup lang="ts">
import cashImg from "@/images/payments/cash_payment.webp";
import { useSinglePayment } from "@/widget/actions/pro-features/payments/composable/useSinglePayment.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { computed } from "vue";

const { paymentType } = useSinglePayment();
const translationsStore = useTranslationsStore();
const settingsStore = useSettingsStore();

const cashPaymentType = computed(() => {
  return settingsStore.getPaymentGateway?.cashPayment?.type;
});

const cashPaymentLabel = computed(() => {
  return (
    settingsStore.getPaymentGateway?.cashPayment?.label ||
    translationsStore.getTranslations.cash
  );
});
</script>

<style lang="scss"></style>
