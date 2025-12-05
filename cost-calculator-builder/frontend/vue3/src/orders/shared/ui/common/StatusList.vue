<template>
  <div class="ccb-option-list-wrapper">
    <div class="ccb-option-list-overlay" @click.stop="emit('close')"></div>
    <div
      class="ccb-option-list-dropdown"
      :class="{ 'ccb-order-details': isOrderDetails }"
    >
      <div class="ccb-option-list-list">
        <div
          v-for="item in items"
          :key="item.id"
          class="ccb-option-list-item"
          @click.stop="selectItem(item.id)"
        >
          <span
            class="ccb-option-list-item-name"
            :class="getColorClass(item.color)"
            >{{ item.status_name }}</span
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IStatuses } from "@/orders/shared/types/orders.type";

const props = defineProps<{
  items: IStatuses[];
  id?: number;
  isOrderDetails?: boolean;
}>();

const { items, id, isOrderDetails } = toRefs(props);

const emit = defineEmits<{
  (e: "close"): void;
  (e: "select", idx: number, value: number): void;
}>();

const selectItem = (value: number) => {
  emit("select", id.value ? +id.value : 0, value);
};

const getColorClass = computed(() => {
  return (color: string) => {
    return `ccb-color-${color}-with-bg`;
  };
});
</script>

<style lang="scss" scoped>
.ccb-option-list-wrapper {
  position: absolute;
  z-index: 111;

  &.ccb-colors {
    right: 77%;
    top: 18px;
  }
}

.ccb-option-list-value {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ccb-option-list-dropdown {
  padding: 4px;
  position: absolute;
  top: 14px;
  left: 0;
  width: 152px;
  background: #fefeff;
  border: none;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 111;
  min-width: 100px;
  box-shadow: 0 5px 30px 0 rgba(0, 0, 0, 0.15);

  &.ccb-order-details {
    top: -205px;
  }
}

.ccb-option-list-item {
  height: 40px;
  padding: 0 16px;
  cursor: pointer;
  color: rgba(22, 36, 50, 0.85);
  font-size: 14px;
  font-style: normal;
  font-weight: 500;
  display: flex;
  align-items: center;
  cursor: pointer;
}

.ccb-option-list-item.selected {
  background: #f5f7fa;
}

.ccb-option-list-item:hover {
  background: #f5f7fa;
}

.ccb-option-list-select {
  z-index: 12;

  .ccb-option-list-item {
    height: 40px;
  }

  .ccb-option-list-dropdown {
    z-index: 12;
  }
}

.ccb-option-list-item-name {
  border-radius: 6px;
  padding: 2px 8px;
  text-indent: 0;
}

.ccb-option-list-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: transparent;
  z-index: 110;
  cursor: pointer;
}

.ccb-bulk-actions-status-list-dropdown {
  .ccb-option-list-dropdown {
    top: 5px !important;
    left: 0 !important;
    width: 200px !important;
  }
}
</style>
