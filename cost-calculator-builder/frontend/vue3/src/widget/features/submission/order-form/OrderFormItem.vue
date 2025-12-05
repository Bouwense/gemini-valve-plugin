<template>
  <component :is="FieldComponent" :field="field"></component>
</template>

<script setup lang="ts">
import { computed, toRefs } from "vue";
import { IFormField } from "@/widget/shared/types/fields";
import {
  IOrderFormFields,
  orderFormFieldRegistry,
} from "@/widget/actions/pro-features/order-form/orderFormFieldRegistry.ts";

type Props = {
  name: string;
  field: IFormField;
};

const props = defineProps<Props>();
const { field, name } = toRefs(props);

const FieldComponent = computed(() => {
  const key: keyof IOrderFormFields = name.value as keyof IOrderFormFields;
  return orderFormFieldRegistry[key] || "";
});
</script>

<style lang="scss">
.ccb-order-field__counter {
  font-size: 12px;
  opacity: 0.6;
  width: 100%;
  display: flex;
  justify-content: flex-end;
  padding-top: 5px;
}

.ccb-order-required-mark {
  color: var(--ccb-error-color) !important;
}

.ccb-order-field-required {
  position: absolute;
  top: -14px;
  right: 0;

  &__tooltip {
    top: -16px;
    right: 2px;
    background-color: #d94141;
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    position: relative;

    &::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 90%;
      transform: translateX(-50%);
      border-width: 5px;
      border-style: solid;
      border-color: #d94141 transparent transparent transparent;
    }
  }

  &.left {
    right: auto;
    left: 0;
    top: -20px;

    .ccb-order-field-required__tooltip {
      &::after {
        left: 10%;
      }
    }
  }
}

.ccb-datetime {
  .error-tip {
    display: none !important;
  }
}

.ccb-order-field--required {
  .ccb-order-field__label {
    color: var(--ccb-error-color) !important;
  }

  .ccb-order-dropdown__input,
  textarea,
  select,
  input {
    border-color: var(--ccb-error-color) !important;
  }

  .ccb-default-checkbox {
    label {
      &:before {
        border-color: var(--ccb-error-color) !important;
      }
    }
  }
}

.ccb-order-field__label_confirmation {
  color: var(--ccb-error-color) !important;
}

.ccb-order-field__wrapper {
  position: relative;
}
</style>
