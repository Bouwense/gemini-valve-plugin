<template>
  <div class="ccb-option-list-wrapper">
    <div class="ccb-option-list-overlay" @click.stop="emit('close')"></div>
    <div class="ccb-option-list-dropdown">
      <div class="ccb-option-colors-list">
        <template v-for="item in items" :key="item.value">
          <div
            v-if="item.value"
            class="ccb-option-list-item"
            :class="getColorClass(item.value)"
            @click.stop="selectItem(item.value)"
          >
            <span
              class="ccb-option-list-item-color"
              v-if="color === item.value"
            >
              <i class="ccb-icon-radius-check"></i>
            </span>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IColors } from "@/orders/shared/types/colors.type";

const props = defineProps<{
  id: number;
  items: IColors[];
  color: string;
}>();

const { id, items, color } = toRefs(props);

const emit = defineEmits<{
  (e: "close"): void;
  (e: "select", idx: number, value: string): void;
}>();

const selectItem = (value: string) => {
  emit("select", +id.value, value);
};

const getColorClass = computed(() => {
  return (value: string) => {
    return `ccb-color-${value}-box`;
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
  top: 18px;
  left: 125px;
  width: 156px;
  height: 156px;
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

.ccb-option-colors-list {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-wrap: wrap;
  gap: 4px;
}

.ccb-option-list-item {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.ccb-option-list-item.selected {
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

.ccb-option-list-item-color {
  i {
    color: #fff;
    font-size: 10px;
  }
}
</style>
