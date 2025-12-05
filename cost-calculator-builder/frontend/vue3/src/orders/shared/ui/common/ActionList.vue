<template>
  <div class="ccb-action-list-wrapper">
    <div class="ccb-action-list-overlay" @click.stop="emit('close')"></div>
    <div
      class="ccb-action-list-dropdown"
      :class="{ 'ccb-order-details': isOrderDetails }"
    >
      <div class="ccb-action-list-list">
        <div
          v-for="item in getCommonItems"
          :key="item.action"
          class="ccb-action-list-item"
        >
          <span
            class="ccb-action-list-item-name"
            @click.stop="selectItem(item.action)"
            >{{ item.label }}</span
          >
        </div>
        <div
          v-for="item in getDangerItems"
          :key="item.action"
          class="ccb-action-list-item ccb-danger"
        >
          <span
            class="ccb-action-list-item-name"
            @click.stop="selectItem(item.action)"
            >{{ item.label }}</span
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IActions } from "@/orders/shared/types/orders.type";

const props = defineProps<{
  items: IActions[];
  isOrderDetails?: boolean;
}>();

const { items, isOrderDetails } = toRefs(props);

const emit = defineEmits<{
  (e: "close"): void;
  (e: "select", value: string): void;
}>();

const selectItem = (value: string) => {
  emit("select", value);
};

const getCommonItems = computed(() => {
  return items.value.filter((item) => !item.is_danger);
});

const getDangerItems = computed(() => {
  return items.value.filter((item) => item.is_danger);
});
</script>

<style lang="scss" scoped>
.ccb-action-list-wrapper {
  position: absolute;
  z-index: 111;

  &.ccb-colors {
    right: 77%;
    top: 18px;
  }
}

.ccb-action-list-value {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ccb-action-list-dropdown {
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
}

.order-details-actions {
  .ccb-action-list-dropdown {
    position: absolute;
    top: 4px;
    width: 246px;
    left: unset;
    right: -12px;
  }
}

.ccb-action-list-item {
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

  &.ccb-danger {
    border-top: 1px solid rgba(22, 36, 50, 0.1);
    color: #d94141;
  }
}

.ccb-action-list-item.selected {
  background: #f5f7fa;
}

.ccb-action-list-item:hover {
  background: #f5f7fa;
}

.ccb-action-list-select {
  z-index: 12;

  .ccb-action-list-item {
    height: 40px;
  }

  .ccb-action-list-dropdown {
    z-index: 12;
  }
}

.ccb-action-list-item-name {
  border-radius: 6px;
  padding: 2px 8px;
  text-indent: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
}

.ccb-action-list-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: transparent;
  z-index: 110;
  cursor: pointer;
}
</style>
