<template>
  <OrderForm v-show="!paymentAfterSubmitStore.getSubmit" />
  <div v-show="paymentAfterSubmitStore.getSubmit">
    <Payments />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from "vue";
import OrderForm from "@/widget/features/submission/order-form";
import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore.ts";
import { usePaymentAfterSubmitStore } from "@/widget/app/providers/stores/paymentAfterSubmit.ts";
import Payments from "@/widget/features/submission/payments";

const paymentAfterSubmitStore = usePaymentAfterSubmitStore();

onMounted(() => {
  const orderFormStore = useOrderFormStore();
  orderFormStore.updateNextButtonStatus(false);
});
</script>
