<template>
  <div class="ccb-range-timePicker">
    <div class="ccb-range-timePicker__from">
      <VueDatePicker
        v-model="from"
        time-picker
        :is-24="format"
        minutes-grid-increment="5"
        @update:model-value="handleFromUpdate"
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
        @update:model-value="handleToUpdate"
      >
        <template #input-icon>
          <i class="ccb-icon-timepicker-light-clock"></i>
        </template>
      </VueDatePicker>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { useCallbackStore } from "@/widget/app/providers/stores/callbackStore.ts";

const callbackStore = useCallbackStore();

const props = defineProps<{
  format: boolean;
  minInterval: string;
  modelValue?: {
    from?: { hours: number; minutes: number; seconds: number };
    to?: { hours: number; minutes: number; seconds: number };
  } | null;
}>();

const emit = defineEmits(["update:modelValue"]);

const parseInterval = (interval: string): number => {
  const hoursMatch = interval.match(/(\d+)h/);
  const minutesMatch = interval.match(/(\d+)m/);

  const hours = hoursMatch ? parseInt(hoursMatch[1]) * 60 : 0;
  const minutes = minutesMatch ? parseInt(minutesMatch[1]) : 0;

  return hours + minutes;
};

const from = ref<Date | { hours: number; minutes: number; seconds: number }>(
  props.modelValue?.from || new Date(),
);

const to = ref<Date | { hours: number; minutes: number; seconds: number }>(
  props.modelValue?.to || new Date(),
);

onMounted(() => {
  if (props.minInterval) {
    const initialFromTime =
      from.value instanceof Date
        ? {
            hours: from.value.getHours(),
            minutes: from.value.getMinutes(),
            seconds: 0,
          }
        : from.value;

    handleFromUpdate(initialFromTime);
  }
});

const changeFormat = (val: string) => {
  const [timePart, period] = val.toLowerCase().split(" ");
  const [hours, minutes] = timePart.split(":").map(Number);

  let convertedHours = hours;
  if (period === "pm" && hours !== 12) {
    convertedHours = hours + 12;
  } else if (period === "am" && hours === 12) {
    convertedHours = 0;
  }

  return {
    hours: convertedHours,
    minutes: minutes,
    seconds: 0,
  };
};

callbackStore.add(
  "updateRangeTimePicker",
  (val: { start: string; end: string }) => {
    from.value = changeFormat(val.start);
    to.value = changeFormat(val.end);
  },
);

const toDateFromTimeObject = (time: {
  hours: number;
  minutes: number;
  seconds?: number;
}): Date => {
  const date = new Date();
  date.setHours(time.hours);
  date.setMinutes(time.minutes);
  date.setSeconds(time.seconds || 0);
  return date;
};

const handleFromUpdate = (newTime: {
  hours: number;
  minutes: number;
  seconds: number;
}) => {
  from.value = newTime;

  const fromDate = toDateFromTimeObject(newTime);

  if (props.minInterval) {
    const interval = parseInterval(props.minInterval);
    if ("hours" in to.value) {
      const toDate = toDateFromTimeObject(to.value);
      const diff = Math.abs(fromDate.getTime() - toDate.getTime()) / 1000 / 60;
      if (interval < diff) {
        return;
      }
    }

    const newToDate = new Date(fromDate);
    newToDate.setMinutes(newToDate.getMinutes() + interval);

    to.value = {
      hours: newToDate.getHours(),
      minutes: newToDate.getMinutes(),
      seconds: 0,
    };
  }
};

const handleToUpdate = (newTime: {
  hours: number;
  minutes: number;
  seconds: number;
}) => {
  to.value = newTime;

  const toDate = toDateFromTimeObject(newTime);
  if (props.minInterval) {
    const interval = parseInterval(props.minInterval);
    if ("hours" in from.value) {
      const fromDate = toDateFromTimeObject(from.value);
      const diff = (toDate.getTime() - fromDate.getTime()) / 1000 / 60;
      if (interval < diff) {
        return;
      }
    }

    const newFromDate = new Date(toDate);
    newFromDate.setMinutes(newFromDate.getMinutes() - interval);

    from.value = {
      hours: newFromDate.getHours(),
      minutes: newFromDate.getMinutes(),
      seconds: 0,
    };
  }
};

watch([from, to], (newTime: unknown) => {
  if (!Array.isArray(newTime)) return;

  const formattedTime = newTime.map((time) => {
    if (time instanceof Date) {
      return {
        hours: time.getHours(),
        minutes: time.getMinutes(),
        seconds: 0,
      };
    }
    return time;
  });

  emit("update:modelValue", formattedTime);
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
    min-height: 44px;
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
