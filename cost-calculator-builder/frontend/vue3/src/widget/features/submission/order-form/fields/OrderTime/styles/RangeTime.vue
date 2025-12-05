<template>
  <div class="ccb-range-timePicker">
    <div class="ccb-range-timePicker__from">
      <VueDatePicker
        v-model="from"
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
    <div class="ccb-range-timePicker__separator">
      <span>to</span>
    </div>
    <div class="ccb-range-timePicker__to">
      <VueDatePicker
        v-model="to"
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
  </div>
</template>

<script setup lang="ts">
import { ref, toRefs, computed, watch, defineEmits, onMounted } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { IFormField } from "@/widget/shared/types/fields";

const props = defineProps<{
  field: IFormField;
}>();

const emit = defineEmits(["update:modelValue"]);

const { field } = toRefs(props);

const from = ref<Date | { hours: number; minutes: number; seconds: number }>(
  new Date(),
);

const to = ref<Date | { hours: number; minutes: number; seconds: number }>(
  new Date(),
);

const format = computed(() => {
  return field.value?.attributes?.format || false;
});

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

onMounted(() => {
  if (from.value instanceof Date && to.value instanceof Date) {
    const fromTime = {
      hours: from.value.getHours(),
      minutes: from.value.getMinutes(),
      seconds: from.value.getSeconds(),
    };
    const toTime = {
      hours: to.value.getHours(),
      minutes: to.value.getMinutes(),
      seconds: to.value.getSeconds(),
    };

    const fromTimeFormatted = formatTime(fromTime);
    const toTimeFormatted = formatTime(toTime);
    emit("update:modelValue", `${fromTimeFormatted} - ${toTimeFormatted}`);
  }
});

watch([from, to], (newTime: unknown) => {
  if (!Array.isArray(newTime)) return;
  const fromTime =
    newTime[0] instanceof Date
      ? {
          hours: newTime[0].getHours(),
          minutes: newTime[0].getMinutes(),
          seconds: newTime[0].getSeconds(),
        }
      : newTime[0];
  const toTime =
    newTime[1] instanceof Date
      ? {
          hours: newTime[1].getHours(),
          minutes: newTime[1].getMinutes(),
          seconds: newTime[1].getSeconds(),
        }
      : newTime[1];

  const fromTimeFormatted = formatTime(fromTime);
  const toTimeFormatted = formatTime(toTime);
  emit("update:modelValue", `${fromTimeFormatted} - ${toTimeFormatted}`);
});
</script>

<style lang="scss">
.ccb-range-timePicker {
  display: flex;

  &__separator {
    padding: 0 10px;
    border: 1px solid var(--ccb-fields-border-color);
    background: var(--ccb-fields-bg-color);
    border-right: none;
    border-left: none;
    min-height: 43px;
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--ccb-text-color);

    span {
      font-size: var(--ccb-fields-button-size);

      @media only screen and (max-width: 480px) {
        font-size: var(--ccb-mobile-fields-button-size);
      }
    }
  }

  &__from {
    input {
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
    }
  }

  &__to {
    input {
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
    }
  }
}
</style>
