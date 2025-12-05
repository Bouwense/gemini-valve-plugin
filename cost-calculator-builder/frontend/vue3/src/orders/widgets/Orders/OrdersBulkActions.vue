<template>
  <div
    class="ccb-bulk-actions"
    :class="{ 'ccb-bulk-actions-active': selectedCount > 0 }"
  >
    <i
      class="ccb-icon-Cost-Calculator-Admin ccb-bulk-actions-ccb-cancel"
      @click="emit('cancel')"
    ></i>
    <span class="ccb-bulk-actions-selected-count">
      <span class="ccb-count">{{ selectedCount || 0 }}</span>
      <span class="ccb-text">Selected</span>
    </span>
    <span class="ccb-bulk-actions-delete" @click="emit('delete')">
      <i class="ccb-icon-Path-3503"></i>
    </span>
    <span class="ccb-bulk-actions-status-list">
      <span
        class="ccb-bulk-actions-status-list-text"
        @click="showStatusList = true"
        :class="[getStatusColorClass]"
      >
        <span v-if="!selectedStatusLabel">Status</span>
        <span v-else>{{ selectedStatusLabel }}</span>
        <i
          class="ccb-icon-Path-3514 ccb-arrow-icon"
          :class="[getStatusIconClass, { 'ccb-active': showStatusList }]"
        ></i>
      </span>
      <StatusList
        v-if="showStatusList"
        @close="showStatusList = false"
        @select="selectStatus"
        :items="ordersStore.getStatuses"
        class="ccb-bulk-actions-status-list-dropdown"
      />
    </span>
  </div>
</template>

<script setup lang="ts">
import StatusList from "@/orders/shared/ui/common/StatusList.vue";
import { toRefs, ref, computed } from "vue";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";

const props = defineProps<{
  selectedCount: number;
}>();

const ordersStore = useOrdersStore();

const { selectedCount } = toRefs(props);

const emit = defineEmits<{
  (e: "cancel"): void;
  (e: "selectStatus", status_id: number): void;
  (e: "delete"): void;
}>();

const showStatusList = ref<boolean>(false);
const selectedStatusLabel = ref<string>("");
const statusColor = ref<string>("");

const selectStatus = (_: number, status_id: number) => {
  showStatusList.value = false;
  const status = ordersStore.getStatuses.find(
    (status) => status.id === status_id,
  );

  if (status) {
    selectedStatusLabel.value = status.status_name;
    statusColor.value = status.color;
    emit("selectStatus", status.id);
  }
};

const getStatusColorClass = computed(() => {
  if (statusColor.value) {
    return `ccb-color-${statusColor.value}-with-bg`;
  }

  return `ccb-color-gray-with-bg`;
});

const getStatusIconClass = computed(() => {
  if (statusColor.value) {
    return `ccb-color-${statusColor.value}`;
  }

  return `ccb-color-gray`;
});
</script>

<style scoped lang="scss">
.ccb-bulk-actions {
  position: absolute;
  left: 0;
  right: 0;
  top: -120%;
  background-color: #eef2f7;
  z-index: 11;
  display: flex;
  align-items: center;
  column-gap: 36px;
  padding: 0 20px;
  transition: all 0.2s ease-in-out;

  &-active {
    top: 0;
    bottom: 0;
  }

  &-ccb-cancel {
    cursor: pointer;
    font-size: 18px;
    opacity: 0.5;
    transition: all 0.2s ease-in-out;

    &:hover {
      opacity: 0.8;
    }
  }

  &-selected-count {
    display: flex;
    align-items: center;
    column-gap: 4px;

    span {
      color: #162432;
      font-size: 14px;
      font-weight: 500;
      line-height: 1;

      &.ccb-text {
        color: #9196a1;
      }
    }
  }

  &-delete {
    cursor: pointer;
    background: #ffffff;
    padding: 8px;
    width: 38px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    color: #162432;

    font-size: 16px;
  }

  &-status-list {
    &-text {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 200px;
      height: 32px;
      cursor: pointer;
      padding: 0 16px;
      border-radius: 8px;

      .ccb-arrow-icon {
        font-size: 10px;
        transition: all 0.2s ease-in-out;
        top: 1px;
        position: relative;
        opacity: 0.8;

        &.ccb-active {
          transform: rotate(180deg);
        }
      }
    }
  }
}
</style>
