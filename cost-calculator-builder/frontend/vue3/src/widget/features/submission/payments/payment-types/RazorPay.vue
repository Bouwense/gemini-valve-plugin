<template>
  <div class="ccb-payment">
    <label>
      <div class="ccb-payment-header">
        <div class="ccb-payment-header__label">
          <input
            type="radio"
            name="paymentMethods"
            value="razorpay"
            v-model="paymentType"
          />
          <span class="ccb-radio-label">Razorpay</span>
        </div>
        <div class="calc-payment_header__img">
          <img
            :src="razorpayImg"
            alt="card-payment-image"
            style="max-width: 112px"
          />
        </div>
      </div>
      <div class="calc-payment-body">
        <div
          id="ccb_stripe_397"
          class="calc-stripe-wrapper"
          style="background: transparent; display: none"
        ></div>
      </div>
    </label>
  </div>
</template>

<script setup lang="ts">
import razorpayImg from "@/images/payments/card_payment.webp";
import { useSinglePayment } from "@/widget/actions/pro-features/payments/composable/useSinglePayment.ts";
const { paymentType } = useSinglePayment();
</script>

<style lang="scss"></style>
