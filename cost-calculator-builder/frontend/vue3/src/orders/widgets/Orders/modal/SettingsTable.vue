<template>
  <div class="ccb-orders-settings-table-modal-content">
    <div class="ccb-orders-settings-table-modal-content--header">
      <h1>Table Settings</h1>
    </div>
    <div class="ccb-orders-settings-table-modal-content--body">
      <div class="ccb-orders-settings-table-items" ref="parent">
        <div
          class="ccb-orders-settings-table-item"
          v-for="option in options"
          :key="option.key"
        >
          <div class="ccb-orders-settings-table-item--icon">
            <i class="ccb-icon-drag-dots"></i>
          </div>
          <div class="ccb-orders-settings-table-item--toggle">
            <ToggleItem
              name="table-settings"
              :option="option"
              @change="updateOption"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";
import ToggleItem from "@/orders/shared/ui/common/ToggleItem.vue";
import {
  ITableSettings,
  ITableSettingsItem,
} from "@/orders/shared/types/table.settings";
import { animations } from "@formkit/drag-and-drop";
import { useDragAndDrop } from "@formkit/drag-and-drop/vue";

const ordersStore = useOrdersStore();

const options_list = computed<ITableSettingsItem[]>(() => {
  const data = ordersStore.getTempTableSettings;
  return Object.values(data).sort((a, b) => a.sort_id - b.sort_id);
});

const updateOption = (_: string, key: string, value: boolean) => {
  const tableSettings = ordersStore.getTempTableSettings;
  if (key in tableSettings) {
    tableSettings[key as keyof ITableSettings].value = value;
  }
  ordersStore.setTempTableSettings(tableSettings);
};

const [parent, options] = useDragAndDrop<ITableSettingsItem>(
  options_list.value,
  {
    plugins: [animations()],
    dragHandle: ".ccb-orders-settings-table-item--icon",
    dragEffectAllowed: "move",
    dragDropEffect: "none",

    onSort(event: any): void {
      const settings = JSON.parse(
        JSON.stringify(ordersStore.getTempTableSettings),
      );

      for (let i = 0; i < event.values.length; i++) {
        const item = event.values[i];
        settings[item.key as keyof ITableSettings].sort_id = i + 1;
      }

      ordersStore.setTempTableSettings(settings);
    },
  },
);
</script>
<style lang="scss" scoped>
.ccb-orders-settings-table-modal-content {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  row-gap: 24px;

  &--header {
    display: flex;
    align-items: center;
  }

  &--body {
    display: flex;
    flex-direction: column;
    row-gap: 12px;
  }

  .ccb-orders-settings-table-items {
    display: flex;
    flex-direction: column;
    row-gap: 10px;

    .ccb-orders-settings-table-item--icon {
      display: flex;
    }

    .ccb-orders-settings-table-item {
      i {
        font-size: 16px;
        opacity: 0.5;
        cursor: grab;
        transition: all 200ms ease-in-out;
        &:hover {
          opacity: 0.7;
        }
      }

      display: flex;
      align-items: center;
      width: 100%;
      column-gap: 12px;

      &[aria-selected="true"] {
        opacity: 0.3;
      }
    }
  }
}
</style>
