<template>
  <div class="ccb-select-with-date-wrapper" ref="dropdownRef">
    <div
      class="ccb-select-with-date-block"
      @click="toggleList"
      @mouseenter="hovered = true"
      @mouseleave="hovered = false"
    >
      <span class="ccb-select-with-date-value">
        {{ selectedLabel || placeholder }}
      </span>
      <span class="ccb-select-with-date-icon" v-if="!showRemove">
        <!-- Calendar SVG -->
        <svg
          width="20"
          height="20"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <rect
            x="3"
            y="6"
            width="14"
            height="11"
            rx="2"
            fill="none"
            stroke="#b2b8c6"
            stroke-width="1.5"
          />
          <rect x="7" y="2" width="1.5" height="3" rx="0.75" fill="#b2b8c6" />
          <rect
            x="11.5"
            y="2"
            width="1.5"
            height="3"
            rx="0.75"
            fill="#b2b8c6"
          />
        </svg>
      </span>
      <span
        class="ccb-select-with-date-remove"
        v-if="showRemove"
        @click.stop="clearSelection"
      >
        <!-- Cross SVG -->
        <svg
          width="18"
          height="18"
          viewBox="0 0 18 18"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M13.5 4.5L4.5 13.5"
            stroke="#b2b8c6"
            stroke-width="1.5"
            stroke-linecap="round"
          />
          <path
            d="M4.5 4.5L13.5 13.5"
            stroke="#b2b8c6"
            stroke-width="1.5"
            stroke-linecap="round"
          />
        </svg>
      </span>
    </div>
    <div
      v-if="showList"
      class="ccb-select-with-date-dropdown"
      :class="{ 'custom-selected': selectedValue === 'custom' }"
    >
      <div class="ccb-select-with-date-list">
        <div
          v-for="item in items"
          :key="item.value"
          class="ccb-select-with-date-item"
          :class="{ selected: selectedValue === item.value }"
          @click="selectItem(item)"
        >
          {{ item.label }}
        </div>
      </div>
      <div
        v-if="selectedValue === 'custom'"
        class="ccb-select-with-date-content"
      >
        <!-- Custom content here -->
        <div style="padding: 24px; color: #1a2734">
          <VueDatePicker
            v-model="date"
            range
            multi-calendars
            :vertical="true"
            inline
            auto-apply
            :enable-time-picker="false"
            @update:model-value="updateCustomDate"
          />
        </div>
      </div>
      <div
        class="ccb-select-with-date-footer"
        v-if="selectedValue === 'custom'"
      >
        <button
          class="ccb-select-with-date-footer-button ccb-cancel-button"
          @click="cancelSelection"
        >
          <span>Cancel</span>
        </button>
        <button
          class="ccb-select-with-date-footer-button ccb-apply-button"
          @click="applySelection"
        >
          <span>Apply</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  ref,
  computed,
  defineEmits,
  toRefs,
  Ref,
  onMounted,
  onUnmounted,
} from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

const props = defineProps<{
  placeholder: string;
  items: { value: string; label: string }[];
  defaultValue: string;
}>();

const emit = defineEmits<{
  (e: "update", value: string, extra?: string): void;
}>();

const { placeholder, items, defaultValue } = toRefs(props);

const showList = ref(false);
const hovered = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const selectedValue: Ref<string> = ref(defaultValue.value);

const date = ref<Date[]>([new Date(), new Date()]);
const dateString = ref<string>("");
const tempDateString = ref<string>("");
const applyDate = ref<boolean>(false);

const selectedLabel = computed(() => {
  if (applyDate.value) {
    return dateString.value;
  }

  const found = items.value.find((i) => i.value === selectedValue.value);
  return found ? found.label : "";
});

const showRemove = computed(() => selectedValue.value && hovered.value);

function toggleList() {
  showList.value = !showList.value;
}

function selectItem(item: { value: string; label: string }) {
  selectedValue.value = item.value;

  if (item.value === "custom") {
    return;
  }

  if (item.value !== "custom") {
    showList.value = false;
    date.value = [];
    dateString.value = "";
    tempDateString.value = "";
    applyDate.value = false;
  }

  emit("update", item.value);
}

const cancelSelection = () => {
  showList.value = false;
};

const applySelection = () => {
  showList.value = false;
  dateString.value = tempDateString.value;
  tempDateString.value = "";
  applyDate.value = true;
  emit("update", "custom", dateString.value);
};

const clearSelection = () => {
  date.value = [];
  showList.value = false;
  dateString.value = "";
  tempDateString.value = "";
  applyDate.value = false;
  selectedValue.value = defaultValue.value;
  emit("update", defaultValue.value);
};

const updateCustomDate = (date: Date[]) => {
  const formatDate = (date: Date): string => {
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
  };

  if (date && date.length >= 2) {
    const formattedRange = `${formatDate(date[0])} - ${formatDate(date[1])}`;
    tempDateString.value = formattedRange;
  } else if (date && date.length === 1) {
    const formattedDate = formatDate(date[0]);
    tempDateString.value = formattedDate;
  }
};

const closeDropdown = (): void => {
  showList.value = false;
};

const handleClickOutside = (event: MouseEvent): void => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<style lang="scss" scoped>
.ccb-select-with-date-wrapper {
  position: relative;
  width: 340px;
}
.ccb-select-with-date-block {
  width: 100%;
  background: #fff;
  border: 1px solid #e3e6ea;
  border-radius: 4px;
  padding: 0 16px 0 20px;
  font-size: 17px;
  color: #1a2734;
  font-weight: 500;
  height: 40px;
  min-height: 40px;
  display: flex;
  align-items: center;
  box-shadow: 0 1px 4px 0 rgba(16, 30, 54, 0.04);
  cursor: pointer;
  position: relative;
  user-select: none;
  transition:
    border 0.2s,
    box-shadow 0.2s;
}
.ccb-select-with-date-block:hover,
.ccb-select-with-date-block:focus {
  border-color: #b2b8c6;
  box-shadow: 0 2px 8px 0 rgba(16, 30, 54, 0.08);
}
.ccb-select-with-date-value {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ccb-select-with-date-icon,
.ccb-select-with-date-remove {
  flex: 0 0 auto;
  margin-left: 12px;
  display: flex;
  align-items: center;
  transition: opacity 0.15s;
}
.ccb-select-with-date-remove {
  cursor: pointer;
  &:hover svg path {
    stroke: #e74c3c;
  }
}

.ccb-select-with-date-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background: #fff;
  border: 1.5px solid #e3e6ea;
  border-radius: 12px;
  margin-top: 6px;
  box-shadow: 0 2px 8px 0 rgba(16, 30, 54, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 12;

  &.custom-selected {
    flex-direction: row;
    min-width: 600px;
    flex: 1;
    padding-bottom: 64px;

    left: unset;
    right: 0;

    .ccb-select-with-date-list {
      min-width: 220px;
    }
  }

  .ccb-select-with-date-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 64px;
    display: flex;
    align-items: center;
    border-top: 1px solid rgba(22, 36, 50, 0.1);
    justify-content: flex-end;
    padding-right: 16px;
    column-gap: 10px;

    .ccb-select-with-date-footer-button {
      background: #fff;
      border: 2px solid rgba(22, 36, 50, 0.05);
      border-radius: 10px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 12px;
      border-radius: 8px;
      font-size: 14px;
      font-style: normal;
      font-weight: 500;
      line-height: 16px;
      cursor: pointer;
      border-radius: 8px;
      color: rgba(22, 36, 50, 0.85);
      transition: 300ms ease;

      &.ccb-cancel-button {
        background: #f1f1f1;

        &:hover {
          background: #e3e6ea;
        }
      }

      &.ccb-apply-button {
        background: #00b163;
        color: #fff;

        &:hover {
          background-color: hsl(from #00b163 h s calc(l - 5));
        }
      }
    }
  }
}

.ccb-select-with-date-item {
  height: 40px;
  padding: 0 16px;
  cursor: pointer;
  color: rgba(22, 36, 50, 0.85);
  font-size: 14px;
  font-style: normal;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.ccb-select-with-date-item.selected {
  background: #f5f7fa;
}

.ccb-select-with-date-item:hover {
  background: #f5f7fa;
}

.ccb-select-with-date-content {
  background: #fff;
  flex: 1;
  border-left: 1px solid rgba(22, 36, 50, 0.1);
}
</style>
