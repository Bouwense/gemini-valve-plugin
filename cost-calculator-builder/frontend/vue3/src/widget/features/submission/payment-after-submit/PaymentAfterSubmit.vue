<template>
  <OrderForm v-show="!paymentAfterSubmitStore.getSubmit" />
  <div v-show="paymentAfterSubmitStore.getSubmit">
    <Payments />
  </div>
</template>

<script setup lang="ts">
import OrderForm from "@/widget/features/submission/order-form";
import Payments from "@/widget/features/submission/payments";
import { usePaymentAfterSubmitStore } from "@/widget/app/providers/stores/paymentAfterSubmit.ts";

const paymentAfterSubmitStore = usePaymentAfterSubmitStore();
</script>

<style scoped lang="scss"></style>
