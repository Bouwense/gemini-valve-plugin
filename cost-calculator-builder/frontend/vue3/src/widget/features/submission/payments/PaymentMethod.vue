<template>
  <component :is="FieldComponent"></component>
</template>
<script setup lang="ts">
import { computed, toRefs } from "vue";
import {
  IPaymentTypes,
  paymentTypeRegistry,
} from "@/widget/actions/pro-features/payments/paymentTypeRegistry.ts";

type Props = {
  type: keyof IPaymentTypes;
};

const props = defineProps<Props>();
const { type } = toRefs(props);

const FieldComponent = computed(() => {
  const key: keyof IPaymentTypes = type.value as keyof IPaymentTypes;
  return paymentTypeRegistry[key] || "";
});
</script>

<style lang="scss"></style>
