<template>
  <div class="ccb-widget-settings-wrapper" ref="dropdownRef">
    <div class="ccb-widget-settings-block" @click="toggleList">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <div v-if="showList" class="ccb-widget-settings-dropdown">
      <div class="ccb-widget-settings-list">
        <div
          v-for="item in items"
          :key="item.value"
          class="ccb-widget-settings-item"
          @click="selectItem(item)"
        >
          {{ item.label }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, defineEmits, toRefs, onMounted, onUnmounted } from "vue";

const props = defineProps<{
  items: { value: string; label: string }[];
  current?: string;
}>();

const emit = defineEmits<{
  (e: "update", action: string, key: string): void;
}>();

const { items, current } = toRefs(props);

const showList = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

function toggleList() {
  showList.value = !showList.value;
}

function selectItem(item: { value: string; label: string }) {
  showList.value = false;
  emit("update", item.value, current.value || "");
}

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
  width: 10px;
  height: 10px;
}

.ccb-widget-settings-block {
  display: flex;
  flex-direction: column;
  gap: 2px;
  cursor: pointer;
  span {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: rgba(22, 36, 50, 0.35);
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
  top: -6px;
  left: -100%;
  width: 100%;
  background: #fff;
  border: 1.5px solid #e3e6ea;
  border-radius: 12px;
  box-shadow: 0 2px 8px 0 rgba(16, 30, 54, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 10;
  min-width: 110px;
  transform: translateX(-95%);
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
</style>
