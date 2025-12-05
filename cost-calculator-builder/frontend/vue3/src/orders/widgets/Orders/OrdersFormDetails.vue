<template>
  <div class="order-details-form-details" v-if="getOrderDetails.length > 0">
    <div
      class="order-details-form-details__item"
      v-for="item in getOrderDetails"
    >
      <i :class="getDetailsIcon(item?.type || '')"></i>
      <span>{{ parseValue(item?.value || "") }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IOrderFormDetails } from "@/orders/shared/types/orders.type";

type Props = {
  orderDetails: IOrderFormDetails[];
};

const props = defineProps<Props>();
const { orderDetails } = toRefs(props);

const getOrderDetails = computed(() => {
  return orderDetails.value.filter((item) => item.value);
});

const icons = {
  textarea: "ccb-icon-Chat-Square",
  number: "ccb-icon-Subtraction-6",
  "your-number": "ccb-icon-Subtraction-6",
  "your-email": "ccb-icon-Envelope",
  email: "ccb-icon-Envelope",
  "your-phone": "ccb-icon-Phone",
  phone: "ccb-icon-Color",
  "date-picker": "ccb-icon-date-picker",
  time: "ccb-icon-time",
  "input-textbox": "ccb-icon-Chat-Square",
  "formatted-text": "ccb-icon-Chat-Square",
  radio: "ccb-icon-order-list",
  checkbox: "ccb-icon-Check-Circle-new",
  dropdown: "ccb-icon-Check-Circle-new",
  "time-picker": "ccb-icon-time-picker",
  name: "ccb-icon-Content-Writing-Agency",
  "your-name": "ccb-icon-Content-Writing-Agency",
};

const getDetailsIcon = computed(() => {
  return (type: string) => {
    if (icons[type as keyof typeof icons]) {
      return icons[type as keyof typeof icons];
    }

    return "ccb-icon-order-detail";
  };
});

const parseValue = computed(() => {
  return (value: string) => {
    try {
      const parsedItem = JSON.parse(value);
      if (Array.isArray(parsedItem)) {
        return parsedItem.join(", ");
      }
      return parsedItem;
    } catch {
      return value;
    }
  };
});
</script>

<style scoped lang="scss">
.order-details-form-details {
  display: flex;
  flex-direction: column;
  row-gap: 16px;

  &__item {
    display: flex;
    align-items: center;
    column-gap: 16px;

    i {
      color: rgba(22, 36, 50, 0.4);
    }

    span {
      color: rgba(22, 36, 50, 0.8);
      font-size: 13px;
      font-weight: 500;
      line-height: 1;
    }
  }
}
</style>
