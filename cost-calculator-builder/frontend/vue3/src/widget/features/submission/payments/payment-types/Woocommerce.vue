<template>
  <div class="ccb-payment">
    <label>
      <div class="ccb-payment-header">
        <div class="ccb-payment-header__label">
          <input
            type="radio"
            name="paymentMethods"
            value="woocommerce"
            v-model="paymentType"
          />
          <span class="ccb-radio-label">Woocommerce</span>
        </div>
        <div class="calc-payment_header__img">
          <img
            :src="cashImg"
            alt="card-payment-image"
            style="max-width: 32px"
          />
        </div>
      </div>
    </label>
  </div>
</template>

<script setup lang="ts">
import cashImg from "@/images/payments/woocommerce.webp";
import { useSinglePayment } from "@/widget/actions/pro-features/payments/composable/useSinglePayment.ts";
const { paymentType } = useSinglePayment();
</script>

<style lang="scss"></style>
