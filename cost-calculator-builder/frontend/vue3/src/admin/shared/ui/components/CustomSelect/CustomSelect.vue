<template>
  <div class="ccb-widget-settings-wrapper" ref="dropdownRef">
    <i
      class="ccb-icon-Path-3485 ccb-select-arrow"
      :class="{ 'ccb-open-arrow': showList }"
    ></i>
    <div class="ccb-widget-settings-block" @click="toggleList">
      <span v-if="getCurrent">{{ getCurrent }}</span>
      <span v-else>{{ placeholder }}</span>
    </div>
    <div v-if="showList" class="ccb-widget-settings-dropdown">
      <div class="ccb-widget-settings-list">
        <div
          v-for="item in items"
          :key="item.value"
          class="ccb-widget-settings-item"
          @click="selectItem(item.value)"
        >
          {{ item.label }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  ref,
  defineEmits,
  toRefs,
  onMounted,
  onUnmounted,
  computed,
} from "vue";

const props = defineProps<{
  items: { value: string; label: string }[];
  current: string;
  placeholder?: string;
}>();

const emit = defineEmits<{
  (e: "update", key: string): void;
}>();

const { items, current, placeholder } = toRefs(props);

const getCurrent = computed(() => {
  const currentItem = items.value.find((item) => item.value === current.value);
  return currentItem?.label || placeholder.value;
});

const showList = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

function toggleList() {
  showList.value = !showList.value;
}

const selectItem = (value: string) => {
  showList.value = false;
  emit("update", value);
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
.ccb-widget-settings-wrapper {
  position: relative;
  z-index: 10;

  .ccb-select-arrow {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 9px;
    color: #1a2734;
    opacity: 0.5;
    margin: 0;
    padding: 0;
    line-height: 1;
    transition: all 0.3s ease;

    &.ccb-open-arrow {
      transform: translateY(-55%) rotate(180deg);
    }
  }
}

.ccb-widget-settings-block {
  height: 40px;
  padding: 0 16px;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #e3e6ea;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  width: 100px;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 1px 4px 0 rgba(16, 30, 54, 0.04);

  &:hover {
    border-color: #b2b8c6;
    box-shadow: 0 2px 8px 0 rgba(16, 30, 54, 0.08);
  }

  span {
    color: #1a2734;
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
  }
}

.ccb-widget-settings-value {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ccb-widget-settings-dropdown {
  position: absolute;
  top: 40px;
  left: 0;
  width: 100%;
  background: #fff;
  border: 1px solid #e3e6ea;
  border-radius: 4px;
  box-shadow: 0 2px 8px 0 rgba(16, 30, 54, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 10;
  min-width: 100px;
}

.ccb-widget-settings-item {
  height: 32px;
  padding: 0 16px;
  cursor: pointer;
  color: rgba(22, 36, 50, 0.85);
  font-size: 14px;
  font-style: normal;
  font-weight: 500;
  display: flex;
  align-items: center;
}

.ccb-widget-settings-item.selected {
  background: #f5f7fa;
}

.ccb-widget-settings-item:hover {
  background: #f5f7fa;
}

.ccb-order-status-select {
  z-index: 12;
  .ccb-widget-settings-block {
    width: 220px;
    justify-content: flex-start;
  }

  .ccb-widget-settings-item {
    height: 40px;
  }

  .ccb-widget-settings-dropdown {
    z-index: 12;
  }
}
</style>
