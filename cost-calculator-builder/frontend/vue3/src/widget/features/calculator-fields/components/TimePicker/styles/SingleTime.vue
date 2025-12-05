<template>
  <div class="ccb-single-timePicker">
    <VueDatePicker
      v-model="time"
      time-picker
      :is-24="format"
      minutes-grid-increment="5"
      placeholder="hh:mm"
    >
      <template #input-icon>
        <i class="ccb-icon-timepicker-light-clock"></i>
      </template>
    </VueDatePicker>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, defineEmits, defineProps } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { useCallbackStore } from "@/widget/app/providers/stores/callbackStore.ts";

const callbackStore = useCallbackStore();

const props = defineProps<{
  format: boolean;
  modelValue: { hours: number; minutes: number; seconds: number } | null;
}>();

const emit = defineEmits(["update:modelValue"]);

const time = ref<Date | { hours: number; minutes: number; seconds: number }>(
  props.modelValue || new Date(),
);

watch(time, (newTime) => {
  if (newTime) {
    emit("update:modelValue", newTime);
  }
});

callbackStore.add("updateSingleTimePicker", (val: string) => {
  if (!val) return;

  const [timePart, period] = val.toLowerCase().split(" ");
  const [hours, minutes] = timePart.split(":").map(Number);

  let convertedHours = hours;
  if (period === "pm" && hours !== 12) {
    convertedHours = hours + 12;
  } else if (period === "am" && hours === 12) {
    convertedHours = 0;
  }

  time.value = {
    hours: convertedHours,
    minutes: minutes,
    seconds: 0,
  };
});
</script>

<style lang="scss">
.ccb-single-timePicker {
  max-width: 162px;
}
</style>
