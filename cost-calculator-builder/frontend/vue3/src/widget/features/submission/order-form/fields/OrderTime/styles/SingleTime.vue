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
import { ref, watch, defineEmits, toRefs, computed, onMounted } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { IFormField } from "@/widget/shared/types/fields";
const props = defineProps<{
  field: IFormField;
}>();

const { field } = toRefs(props);
const emit = defineEmits(["update:modelValue"]);

const formatTime = (time: { hours: number; minutes: number }) => {
  const { hours, minutes } = time;
  const formattedMinutes = minutes.toString().padStart(2, "0");

  if (!format.value) {
    const ampm = hours >= 12 ? "PM" : "AM";
    const formattedHours = hours % 12 || 12;
    return `${formattedHours}:${formattedMinutes} ${ampm}`;
  } else {
    const formattedHours = hours.toString().padStart(2, "0");
    return `${formattedHours}:${formattedMinutes}`;
  }
};

const format = computed(() => {
  return field.value?.attributes?.format || false;
});

const time = ref<Date | { hours: number; minutes: number; seconds: number }>(
  new Date(),
);

onMounted(() => {
  if (time.value instanceof Date) {
    const formattedTime = {
      hours: time.value.getHours(),
      minutes: time.value.getMinutes(),
      seconds: time.value.getSeconds(),
    };

    emit("update:modelValue", formatTime(formattedTime));
  }
});

watch(time, (newTime) => {
  if (newTime) {
    let formattedTime;
    if (newTime instanceof Date) {
      formattedTime = {
        hours: newTime.getHours(),
        minutes: newTime.getMinutes(),
      };
    } else {
      formattedTime = newTime;
    }
    emit("update:modelValue", formatTime(formattedTime));
  }
});
</script>

<style lang="scss"></style>
