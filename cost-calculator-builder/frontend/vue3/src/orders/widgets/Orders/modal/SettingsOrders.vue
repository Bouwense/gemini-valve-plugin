<template>
  <div class="ccb-orders-settings-table-modal-content">
    <div class="ccb-orders-settings-table-modal-content--header">
      <h1>Order Details Settings</h1>
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
            <ToggleItem name="table" :option="option" @change="updateOption" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { animations } from "@formkit/drag-and-drop";
import { useDragAndDrop } from "@formkit/drag-and-drop/vue";
import ToggleItem from "@/orders/shared/ui/common/ToggleItem.vue";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";
import {
  IOrdersSettings,
  IOrdersSettingsItem,
} from "@/orders/shared/types/orders.settings";

const ordersStore = useOrdersStore();

const options_list = computed<IOrdersSettingsItem[]>(() => {
  const data = ordersStore.getTempOrdersSettings;
  return Object.values(data).sort((a, b) => a.sort_id - b.sort_id);
});

const [parent, options] = useDragAndDrop<IOrdersSettingsItem>(
  options_list.value,
  {
    plugins: [animations()],
    dragHandle: ".ccb-orders-settings-table-item--icon",
    dragEffectAllowed: "move",
    dragDropEffect: "none",

    onSort(event: any): void {
      const settings = ordersStore.getTempOrdersSettings;

      for (let i = 0; i < event.values.length; i++) {
        const item = event.values[i];
        settings[item.key as keyof IOrdersSettings].sort_id = i + 1;
      }

      ordersStore.setTempOrdersSettings(settings);
    },
  },
);

const updateOption = (_: string, key: string, value: boolean) => {
  const ordersSettings = ordersStore.getTempOrdersSettings;
  if (key in ordersSettings) {
    ordersSettings[key as keyof IOrdersSettings].value = value;
  }
  ordersStore.setTempOrdersSettings(ordersSettings);
};
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
        font-size: 18px;
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
      height: 28px;
      display: flex;
      align-items: center;

      &[aria-selected="true"] {
        opacity: 0.3;
      }
    }
  }
}
</style>
