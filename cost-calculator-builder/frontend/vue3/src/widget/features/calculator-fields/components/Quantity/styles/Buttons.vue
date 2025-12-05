<template>
  <div
    class="ccb-field__input-wrapper ccb-quantity-buttons"
    :class="[
      { 'ccb-quantity-buttons--separation': separation },
      buttonsPosition,
    ]"
  >
    <div class="ccb-quantity-buttons__item down" @click="decrement">
      <i class="ccb-icon-Path-3518"></i>
    </div>
    <input
      type="text"
      v-model="value"
      @focus="emit('focus', $event)"
      @focusout="emit('focusout', $event)"
      @blur="emit('blur', $event)"
      @keypress="emit('keypress', $event)"
    />
    <div class="ccb-quantity-buttons__item up" @click="increment">
      <i class="ccb-icon-Path-3493"></i>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, toRefs } from "vue";
import { IQuantityField } from "@/widget/shared/types/fields";
import { watch } from "vue";

type Props = {
  field: IQuantityField;
  value: string;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const value = ref<string>(props.value);
const emit = defineEmits([
  "input",
  "focus",
  "focusout",
  "blur",
  "keypress",
  "increment",
  "decrement",
]);

const increment = () => emit("increment");
const decrement = () => emit("decrement");

const separation = computed(() => {
  return field.value.separation;
});

const buttonsPosition = computed(() => {
  return field.value.buttonsPosition === "right"
    ? "ccb-quantity-buttons--right"
    : "ccb-quantity-buttons--both";
});

watch(value, (newValue) => {
  emit("input", { target: { value: newValue } });
});

watch(
  () => props.value,
  (newValue) => {
    value.value = newValue;
  },
);
</script>

<style lang="scss">
.ccb-quantity-buttons {
  display: flex;
  align-items: normal;
  justify-content: flex-end;
  width: 100%;

  input {
    border-right: none;
    border-radius: 0;
    border-top-left-radius: var(--ccb-fields-border-radius);
    border-bottom-left-radius: var(--ccb-fields-border-radius);
    transition: none !important;

    &:focus {
      border-color: var(--ccb-accent-color) !important;
    }
  }

  .ccb-quantity-buttons__item {
    border: var(--ccb-fields-border) var(--ccb-fields-border-style)
      var(--ccb-fields-border-color);
    padding: 12px var(--ccb-field-side-indent);
    min-height: var(--ccb-field-button-height);
    background-color: var(--ccb-fields-bg-color);
    font-size: var(--ccb-fields-button-size);
    font-weight: var(--ccb-field-weight);
    box-sizing: border-box;
    color: var(--ccb-text-color);
    outline: none !important;
    line-height: unset !important;
    box-shadow: none !important;
    cursor: pointer;
    min-width: 47px;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      background-color: color-mix(
        in srgb,
        var(--ccb-fields-bg-color-hover) 85%,
        black
      );
    }
  }

  .down {
    order: 3;
    border-left: none;
    border-top-right-radius: var(--ccb-fields-border-radius);
    border-bottom-right-radius: var(--ccb-fields-border-radius);
  }
}

.ccb-quantity-buttons:focus-within .ccb-quantity-buttons__item {
  border-color: var(--ccb-accent-color);
}

.ccb-quantity-buttons.ccb-quantity-buttons--separation.ccb-quantity-buttons--right:focus-within
  .ccb-quantity-buttons__item {
  border-color: var(--ccb-fields-border-color) !important;
}

.ccb-field-quantity.required
  .ccb-quantity-buttons.ccb-quantity-buttons--separation {
  .ccb-quantity-buttons__item {
    border-color: var(--ccb-fields-border-color) !important;
  }
}

.ccb-field-quantity.required .ccb-quantity-buttons {
  .ccb-quantity-buttons__item {
    border-color: var(--ccb-error-color) !important;
  }
}

.ccb-field-quantity.required input {
  border-color: var(--ccb-error-color) !important;
}

.ccb-quantity-buttons--separation.ccb-quantity-buttons--right {
  input {
    margin-right: 10px;
    border-right: var(--ccb-fields-border) var(--ccb-fields-border-style)
      var(--ccb-fields-border-color);
    border-top-right-radius: var(--ccb-fields-border-radius);
    border-bottom-right-radius: var(--ccb-fields-border-radius);
  }

  .up {
    border-top-left-radius: var(--ccb-fields-border-radius);
    border-bottom-left-radius: var(--ccb-fields-border-radius);
  }
}
.ccb-quantity-buttons--both {
  input {
    order: 2;
    border-left: none;
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    text-align: center;
  }
  .up {
    order: 1;
    border-right: none;
    border-top-left-radius: var(--ccb-fields-border-radius);
    border-bottom-left-radius: var(--ccb-fields-border-radius);
  }
}

.ccb-quantity-buttons--both.ccb-quantity-buttons--separation {
  gap: 10px;

  input {
    border-radius: var(--ccb-fields-border-radius);
    border: var(--ccb-fields-border) var(--ccb-fields-border-style)
      var(--ccb-fields-border-color);
  }

  .ccb-quantity-buttons__item {
    border-radius: var(--ccb-fields-border-radius);
    border: var(--ccb-fields-border) var(--ccb-fields-border-style)
      var(--ccb-fields-border-color);
  }
}
</style>
