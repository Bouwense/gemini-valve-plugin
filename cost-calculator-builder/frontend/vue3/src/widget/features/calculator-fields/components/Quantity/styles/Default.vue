<template>
  <div class="ccb-field__input-wrapper">
    <input
      type="text"
      v-model="value"
      @focus="emit('focus', $event)"
      @focusout="emit('focusout', $event)"
      @blur="emit('blur', $event)"
      @keypress="emit('keypress', $event)"
    />
    <div class="ccb-input-counter up" @click="increment">
      <i class="ccb-icon-Path-3486"></i>
    </div>
    <div class="ccb-input-counter down" @click="decrement">
      <i class="ccb-icon-Path-3485"></i>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { IQuantityField } from "@/widget/shared/types/fields";
import { watch } from "vue";

type Props = {
  field: IQuantityField;
  value: string;
};

const props = defineProps<Props>();

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
