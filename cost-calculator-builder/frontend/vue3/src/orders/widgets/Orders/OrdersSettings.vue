<template>
  <div class="ccb-orders-settings-wrapper" ref="dropdownRef">
    <div
      v-if="showList"
      class="ccb-orders-settings-overlay"
      @click="closeDropdown"
    ></div>

    <button class="btn settings-btn" @click="toggleList">
      <i class="ccb-icon-Union-28"></i>
    </button>

    <div v-if="showList" class="ccb-orders-settings-dropdown">
      <div class="ccb-orders-settings-list">
        <div
          v-for="item in items"
          :key="item.value"
          class="ccb-orders-settings-item"
          @click="selectItem(item.value as 'status' | 'table' | 'orders' | '')"
        >
          {{ item.label }}
        </div>
      </div>
    </div>
    <OrdersModal
      :settings-type="selectedItem"
      @close="selectedItem = ''"
      v-if="selectedItem"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, defineEmits, computed } from "vue";
import { OrdersModal } from "./modal";

const emit = defineEmits<{
  (e: "update", key: string): void;
}>();

const items = computed(() => {
  return [
    {
      label: "Status settings",
      value: "status",
    },
    {
      label: "Table Settings",
      value: "table",
    },
    {
      label: "Orders settings",
      value: "orders",
    },
  ];
});

const showList = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
const selectedItem = ref<"status" | "table" | "orders" | "">("");

const toggleList = () => {
  showList.value = !showList.value;
};

const closeDropdown = () => {
  showList.value = false;
};

const selectItem = (value: "status" | "table" | "orders" | "") => {
  selectedItem.value = value;
  showList.value = false;
  emit("update", value);
};
</script>

<style lang="scss" scoped>
.ccb-orders-settings-wrapper {
  position: relative;
  z-index: 111;

  button.settings-btn {
    height: 34px;
    align-items: center;
    justify-content: center;
    width: 56px;
    transition: 200ms linear;
    cursor: pointer;

    background: #f1f3f7;
    border-radius: 8px;
    padding: 8px 10px;
    border: 0;

    &:hover {
      background: #e1e5ee;
    }
  }
}

.ccb-orders-settings-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: transparent;
  z-index: 110;
  cursor: pointer;
}

.ccb-orders-settings-value {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ccb-orders-settings-dropdown {
  position: absolute;
  top: 42px;
  left: -240%;
  width: 192px;
  background: #fefeff;
  border: none;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 111;
  min-width: 100px;
  box-shadow: 0 5px 30px 0 rgba(0, 0, 0, 0.15);
}

.ccb-orders-settings-item {
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

.ccb-orders-settings-item.selected {
  background: #f5f7fa;
}

.ccb-orders-settings-item:hover {
  background: #f5f7fa;
}

.ccb-order-status-select {
  z-index: 12;

  .ccb-orders-settings-item {
    height: 40px;
  }

  .ccb-orders-settings-dropdown {
    z-index: 12;
  }
}
</style>
