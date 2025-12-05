<template>
  <Slider
    style="margin: 10px 0"
    v-model="rawInput"
    :format="getFormatValue"
    :min="field.min"
    :max="field.max"
    :step="field.step"
    :disabled="field.disabled"
    @change="updateValue"
    show-tooltip="focus"
  ></Slider>
</template>

<script setup lang="ts">
import { toRefs, ref } from "vue";
import { IRangeField } from "@/widget/shared/types/fields";
import Slider from "@vueform/slider";

type Props = {
  field: IRangeField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);
const rawInput = ref<number>(field.value.value);

const emit = defineEmits<{
  (event: "update", value: string): void;
}>();

const updateValue = (value: number) => {
  emit("update", value.toString());
};

const getFormatValue = (num: number): string => {
  const rounded = num.toFixed(2);
  return parseFloat(rounded).toString();
};
</script>

<style lang="scss"></style>
