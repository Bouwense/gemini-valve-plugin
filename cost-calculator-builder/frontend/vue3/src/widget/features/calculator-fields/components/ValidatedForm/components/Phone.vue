<template>
  <div class="ccb-field__input-wrapper">
    <VueTelInput
      @input="onInput"
      mode="international"
      :value="field.displayValue"
      :inputOptions="options"
    />
  </div>
</template>

<script setup lang="ts">
import { IValidatedFormField } from "@/widget/shared/types/fields/input.type";
import { useValidatedFormField } from "@/widget/actions/fields/composable/useValidatedFormField.ts";

import { VueTelInput } from "vue-tel-input";
import "vue-tel-input/vue-tel-input.css";

const emit = defineEmits<{
  (event: "update", value: string): void;
}>();

type Props = {
  field: IValidatedFormField;
};

const props = defineProps<Props>();
const { onInput } = useValidatedFormField(props, emit);

const options = {
  placeholder: props.field.placeholder,
};
</script>

<style lang="scss">
.ccb-order-phone {
  input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
  }

  input[type="number"]::-webkit-outer-spin-button,
  input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
}

.ccb-field-form-fields {
  .vti__dropdown {
    outline: none;
  }

  .vue-tel-input:focus-within {
    border-color: var(--ccb-accent-color);
  }

  .vue-tel-input {
    border: none;
    box-shadow: none;
    outline: none;

    input {
      border-radius: 0 var(--ccb-fields-border-radius)
        var(--ccb-fields-border-radius) 0;
    }
  }
  .vti__dropdown {
    position: unset;
    border: var(--ccb-fields-border) var(--ccb-fields-border-style)
      var(--ccb-fields-border-color);
    background-color: var(--ccb-fields-bg-color);
    border-right: none;
    border-radius: var(--ccb-fields-border-radius) 0 0
      var(--ccb-fields-border-radius);
  }

  .vti__dropdown-arrow {
    font-size: 10px;
  }

  .vti__phone {
    outline: none;
    &:focus,
    &:active {
      outline: none;
      border-color: var(--ccb-fields-border-color);
    }
  }

  .vti__dropdown-list {
    z-index: 1000;
    top: 42px;
    box-sizing: border-box;
    left: 0;
    right: 0;
    width: 100%;

    .vti__dropdown-item {
      padding: 10px 15px;
      border-bottom: 1px solid #ddd;
      font-size: 14px;

      &:last-child {
        border-bottom: none;
      }
    }
  }
}
</style>
