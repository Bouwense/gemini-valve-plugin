<template>
  <div class="ccb-option-list-wrapper">
    <div class="ccb-option-list-overlay" @click.stop="emit('close')"></div>
    <div class="ccb-option-list-dropdown">
      <div class="ccb-option-list-list">
        <div v-for="item in items" :key="item.id" class="ccb-option-list-item">
          <label
            class="ccb-option-list-item-name"
            :class="getColorClass(item.color)"
            :for="`status-${item.id}`"
            >{{ toShortName(item.status_name) }}
          </label>
          <span>
            <div class="ccb-select-box">
              <input
                :id="`status-${item.id}`"
                type="checkbox"
                v-model="selectedStatusesValue"
                :value="item.id"
              />
            </div>
          </span>
        </div>
      </div>
      <div class="ccb-option-list-footer">
        <button class="ccb-status-cancel" @click.stop="clearSelectedStatuses">
          Clear
        </button>
        <button class="ccb-status-save" @click.stop="saveSelectedStatuses">
          OK
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, ref } from "vue";
import { IStatuses } from "@/orders/shared/types/orders.type";

const props = defineProps<{
  items: IStatuses[];
  selectedStatuses: number[];
}>();

const { items, selectedStatuses } = toRefs(props);

const selectedStatusesValue = ref<number[]>(selectedStatuses.value);

const emit = defineEmits<{
  (e: "close"): void;
  (e: "select", ids: number[]): void;
}>();

const clearSelectedStatuses = () => {
  selectedStatusesValue.value = [];
};

const saveSelectedStatuses = () => {
  emit("select", selectedStatusesValue.value);
};

const getColorClass = computed(() => {
  return (color: string) => {
    return `ccb-color-${color}-with-bg`;
  };
});

const toShortName = computed(() => {
  return (name: string) => {
    return name.length > 15 ? name.substring(0, 12) + "..." : name;
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
  width: 190px;
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
  display: flex;
  justify-content: space-between;
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

.ccb-option-list-footer {
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 16px;
  border-top: 1px solid rgba(22, 36, 50, 0.1);
  background: #fefeff;
  column-gap: 8px;

  button {
    height: 24px;
    box-sizing: border-box;
    padding: 0 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    color: rgba(22, 36, 50, 0.85);
    font-size: 12px;
    font-weight: 500;
    line-height: 1;
    border-radius: 8px;
    background: #ffffff;
    transition: all 200ms linear;

    &.ccb-status-cancel {
      border: 2px solid rgba(22, 36, 50, 0.07);

      &:hover {
        border-color: rgba(22, 36, 50, 0.15);
      }
    }

    &.ccb-status-save {
      background: rgba(22, 36, 50, 0.05);

      &:hover {
        background: rgba(22, 36, 50, 0.1);
      }
    }
  }
}

.ccb-select-box {
  input {
    width: 18px;
    height: 18px;
    border-radius: 3px;
    border: 2px solid #dddddd !important;
    position: relative;
    top: 3px;
    outline: none !important;
    box-shadow: none !important;

    &:checked {
      border-color: #00b163 !important;
      background-color: #00b163 !important;

      &:before {
        filter: brightness(0) invert(1);
      }
    }
  }
}

.ccb-option-list-list {
  margin-bottom: 4px;
}

.ccb-orders-filter {
  .ccb-option-list-dropdown {
    left: -54px !important;
    top: 4px !important;
  }
}
</style>
