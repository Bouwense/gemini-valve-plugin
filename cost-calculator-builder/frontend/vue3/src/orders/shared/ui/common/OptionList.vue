<template>
  <div class="ccb-option-list-wrapper">
    <div class="ccb-option-list-dropdown">
      <div class="ccb-option-list-list">
        <div
          v-for="item in items"
          :key="item.value"
          class="ccb-option-list-item"
          @click="selectItem(item.value)"
        >
          {{ item.label }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
import { IColors } from "@/orders/shared/types/colors.type";

const props = defineProps<{
  items: IColors[];
  id: number | string;
}>();

const { items, id } = toRefs(props);

const emit = defineEmits<{
  (e: "close"): void;
  (e: "select", idx: number | string, value: string): void;
}>();

const selectItem = (value: string) => {
  emit("select", id.value, value);
};
</script>

<style lang="scss" scoped>
.ccb-option-list-wrapper {
  position: relative;
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
  position: absolute;
  top: 0;
  left: 0;
  width: 192px;
  background: #fefeff;
  border: none;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 10;
  min-width: 100px;
  box-shadow: 0 5px 30px 0 rgba(0, 0, 0, 0.15);
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
</style>
