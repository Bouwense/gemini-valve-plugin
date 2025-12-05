<template>
  <div class="ccb-orders-status-modal-content">
    <div class="ccb-orders-status-modal-content--header">
      <h1>Status Settings</h1>
    </div>
    <div class="ccb-orders-status-modal-content--body">
      <div class="ccb-orders-statuses">
        <div class="ccb-orders-statuses--header">
          <span>Title</span>
          <span>Color</span>
        </div>
        <div class="ccb-orders-statuses--items">
          <template v-for="status in statuses" :key="`status-${status.id}`">
            <div class="ccb-orders-statuses--item">
              <div class="ccb-order-status">
                <div class="ccb-order-status-name ccb-order-item-wrapper">
                  <input
                    type="text"
                    placeholder="Status name"
                    v-model="status.status_name"
                    @input="() => updateStatusName(status.id)"
                    :class="{
                      field_error:
                        checkStatusError(status) ||
                        checkEmptyTitleErrorWarning(status.id),
                    }"
                  />
                </div>

                <div
                  class="ccb-order-status-color ccb-order-item-wrapper"
                  :class="{ field_error: checkColorError(status) }"
                >
                  <span
                    @click="openOptionsList(status.id)"
                    class="ccb-order-status-color-item"
                  >
                    <span :class="getColorClass(status.color)">
                      {{ getColorName(status.color) }}
                    </span>
                  </span>

                  <i
                    class="ccb-icon-Path-3485"
                    :class="{ 'ccb-arrow-up': optionVisibility[status.id] }"
                  ></i>
                </div>

                <ColorPicker
                  :id="status.id"
                  :items="items"
                  v-if="optionVisibility[status.id]"
                  @close="optionVisibility[status.id] = false"
                  @select="selectColor"
                  :color="status.color"
                  :key="status.color"
                />
              </div>

              <span
                class="ccb-btn-action"
                :class="{
                  'disable-remove':
                    disableDeleteStatus ||
                    status.is_default ||
                    status.is_completed,
                }"
              >
                <i class="ccb-icon-close" @click="deleteStatus(status)"></i>
              </span>
            </div>
          </template>
        </div>
        <div class="ccb-orders-statuses--footer">
          <span class="ccb-add-status-btn" @click="addStatus">
            <i class="ccb-icon-Path-3453"></i>
            <span> Add </span>
          </span>
        </div>
        <div class="ccb-orders-statuses--content">
          <div class="ccb-orders-statuses--content-item">
            <CustomDropDown
              :items="getStatusesForDropdown('initial')"
              title="Assign an initial status to new orders"
              :selected="getDefaultStatus"
              @select="updateDefaultStatus"
              id="default"
            />
          </div>
          <div class="ccb-orders-statuses--content-item">
            <CustomDropDown
              :items="getStatusesForDropdown('completed')"
              title="Assign a status for completed orders"
              :selected="getCompletedStatus"
              @select="updateDefaultStatus"
              id="completed"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";
import { IStatuses } from "@/orders/shared/types/orders.type";
import { generateSlug } from "@/orders/shared/utils/generate-slug.utils";
import ColorPicker from "@/orders/shared/ui/common/ColorPicker.vue";
import CustomDropDown from "@/orders/shared/ui/common/CustomDropDown.vue";

const emit = defineEmits<{
  (e: "clear-errors"): void;
  (e: "delete-status", id: number): void;
}>();

const optionVisibility = ref<Record<string, boolean>>({});

const ordersStore = useOrdersStore();

const statuses = computed({
  get() {
    return ordersStore.getTempStatuses?.sort((a, b) => a.sort_id - b.sort_id);
  },

  set(value: IStatuses[]) {
    ordersStore.setTempStatuses(value);
  },
});

const items = computed(() => {
  return [
    { value: "", label: "Select color" },
    {
      value: "red",
      label: "Red",
    },
    {
      value: "scarlet",
      label: "Scarlet",
    },
    {
      value: "ember",
      label: "Ember",
    },
    {
      value: "orange",
      label: "Orange",
    },
    {
      value: "yellow",
      label: "Yellow",
    },
    {
      value: "acid",
      label: "Acid",
    },
    {
      value: "lime",
      label: "Lime",
    },
    {
      value: "green",
      label: "Green",
    },
    {
      value: "teal",
      label: "Teal",
    },
    {
      value: "cyan",
      label: "Cyan",
    },
    {
      value: "blue",
      label: "Blue",
    },
    {
      value: "ultra",
      label: "Ultra",
    },
    {
      value: "indigo",
      label: "Indigo",
    },
    {
      value: "violet",
      label: "Violet",
    },
    {
      value: "magenta",
      label: "Magenta",
    },
    {
      value: "gray",
      label: "Gray",
    },
  ];
});

const getColorClass = computed(() => {
  return (color: string) => {
    return `ccb-color-${color}-with-bg`;
  };
});

const getColorName = computed(() => {
  return (color: string) => {
    return items.value.find((item) => item.value === color)?.label;
  };
});

const selectColor = (id: number | string, color: string) => {
  const newStatuses: IStatuses[] = [];

  for (const status of statuses.value) {
    if (status.id === id) {
      status.color = color;
    }
    newStatuses.push(status);
  }
  statuses.value = newStatuses;

  statuses.value.forEach((status) => {
    optionVisibility.value[status.id] = false;
  });

  checkColorErrors(id);
};

const openOptionsList = (id: number | string) => {
  statuses.value.forEach((status) => {
    if (status.id !== id) {
      optionVisibility.value[status.id] = false;
    }
  });

  optionVisibility.value[id] = !optionVisibility.value[id];
};

const addStatus = () => {
  const temp_statuses = statuses.value.sort((a, b) => b.id - a.id);
  const last = temp_statuses[0];

  const statusTemp = statuses.value;
  statusTemp.push({
    id: +last.id + 1,
    sort_id: statuses.value.length + 1,
    status_name: "",
    color: "",
    slug: "",
    is_default: false,
    is_completed: false,
    count: 0,
  });

  statuses.value = statusTemp;
  emit("clear-errors");
};

const updateDefaultStatus = (id: string, key: string | number): void => {
  statuses.value = statuses.value.map((status: IStatuses) => {
    if (key === "default") {
      status.is_default = +status.id === +id;
    } else if (key === "completed") {
      status.is_completed = +status.id === +id;
    }
    return status;
  });
};

const updateStatusName = (id: number | string) => {
  statuses.value = statuses.value.map((status: IStatuses) => {
    if (status.id === id) {
      return {
        ...status,
        slug: generateSlug(status.status_name),
      };
    }

    return status;
  });

  checkErrors(id);
  checkEmptyTitleErrors(id);
};

const checkErrors = (id: number | string) => {
  const errorsIdx = ordersStore.getErrorIdx.filter((idx) => {
    return !idx.includes(+id);
  });
  ordersStore.setErrorIdx(errorsIdx);
};

const checkColorErrors = (id: number | string) => {
  const emptyColorErrors = ordersStore.getEmptyColorErrors.filter(
    (idx) => idx !== +id,
  );
  ordersStore.setEmptyColorErrors(emptyColorErrors);
};

const checkEmptyTitleErrors = (id: number | string) => {
  const emptyTitleErrors = ordersStore.getEmptyTitleErrors.filter(
    (idx) => idx !== +id,
  );
  ordersStore.setEmptyTitleErrors(emptyTitleErrors);
};

const deleteStatus = (status: IStatuses) => {
  if (status.is_default || status.is_completed) {
    return;
  }

  statuses.value = statuses.value.filter((s: IStatuses) => s.id !== status.id);

  emit("clear-errors");
  emit("delete-status", status.id);
};

const checkStatusError = computed(() => {
  return (status: IStatuses) => {
    const idxList = ordersStore.getErrorIdx;
    let result = false;

    for (const idx of idxList) {
      if (idx.includes(status.id)) {
        result = true;
        break;
      }
    }
    return result;
  };
});

const checkColorError = computed(() => {
  return (status: IStatuses) => {
    return ordersStore.getEmptyColorErrors.includes(status.id);
  };
});

const checkEmptyTitleErrorWarning = computed(() => {
  return (id: number | string) => {
    return ordersStore.getEmptyTitleErrors.includes(+id);
  };
});

const getStatusesForDropdown = computed(() => {
  return (key: "initial" | "completed") => {
    if (key === "initial") {
      return statuses.value
        .filter((status) => +status.is_completed === 0)
        .map((status) => ({
          value: status.id.toString(),
          label: status.status_name,
        }));
    } else {
      return statuses.value
        .filter((status) => +status.is_default === 0)
        .map((status) => ({
          value: status.id.toString(),
          label: status.status_name,
          is_default: status.is_default,
          is_completed: status.is_completed,
        }));
    }
  };
});

const getDefaultStatus = computed(() => {
  return (
    statuses.value.find((status) => status.is_default)?.id?.toString() || ""
  );
});

const getCompletedStatus = computed(() => {
  return (
    statuses.value.find((status) => status.is_completed)?.id?.toString() || ""
  );
});

const disableDeleteStatus = computed(() => {
  const currentPendingStatus =
    statuses.value.find((status) => status.is_default)?.id || 0;
  const currentCompletedStatus =
    statuses.value.find((status) => status.is_completed)?.id || 0;

  return (
    +currentPendingStatus !== ordersStore.getDefaultPendingStatus ||
    +currentCompletedStatus !== ordersStore.getDefaultCompleteStatus
  );
});
</script>
<style lang="scss" scoped>
.ccb-orders-status-modal-content {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  row-gap: 24px;

  &--header {
    display: flex;
    align-items: center;
  }

  &--info {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 8px 16px;
    border-radius: 6px;
    background: #eef1f7;

    color: #001931;
    font-size: 14px;
    font-weight: 500;
  }

  &--body {
    display: flex;
    flex-direction: column;
    row-gap: 12px;
  }

  .ccb-orders-statuses {
    display: flex;
    flex-direction: column;
    row-gap: 10px;
    width: 100%;

    &--header {
      display: flex;
      align-items: center;
      justify-content: space-between;

      span {
        color: #001931;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
      }
    }

    &--items {
      display: flex;
      flex-direction: column;
      row-gap: 10px;
      width: 100%;
    }

    &--item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      height: 40px;
      padding: 0 8px;
      border-radius: 6px;
      background: #eef1f7;

      .ccb-btn-action {
        cursor: pointer;
        width: 20px;
        height: 20px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;

        i {
          cursor: pointer;
          font-size: 14px;
          color: #9facb9;
          transition: color 200ms linear;

          &:hover {
            color: #8292a3;
          }
        }

        &.disable-remove {
          pointer-events: none;
          opacity: 0.4;
        }
      }
    }

    &--content {
      display: flex;
      flex-direction: column;
      row-gap: 16px;

      &-item {
        width: 100%;
      }
    }

    &--footer {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      width: 100%;
      height: 24px;
      margin-bottom: 12px;

      .ccb-add-status-btn {
        display: inline-flex;
        align-items: center;
        flex-direction: row;
        column-gap: 4px;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 8px;
        background: rgba(22, 36, 50, 0.05);
        color: var(--Body-1, rgba(22, 36, 50, 0.85));
        font-size: 12px;
        font-style: normal;
        font-weight: 500;

        i {
          font-size: 14px;
          color: #9facb9;
        }
      }
    }
  }

  .ccb-order-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: 100%;
    padding: 0;
    margin-right: 8px;
    column-gap: 8px;

    .ccb-order-item-wrapper {
      width: 50%;
      height: 32px;
      display: flex;
      align-items: center;
    }

    &-name {
      margin: 0;
      input {
        width: 100%;
        height: 100%;
        background: transparent;
        outline: none;
        box-shadow: none;
        border-radius: 4px;
        border: 1px solid #dddddd;
        background: #ffffff;

        color: #001931;
        font-size: 14px;
        font-weight: 500;

        line-height: 0;
        padding: 0 8px;
        min-height: auto;

        &:focus {
          border-color: #00b163;
        }
      }

      input.field_error {
        border-color: #d94141;
        color: #d94141;
      }
    }

    &-color {
      position: relative;

      i {
        position: absolute;
        right: 10px;
        font-size: 10px;
        opacity: 0.5;
        transition: all 200ms ease-in-out;

        &.ccb-arrow-up {
          transform: rotate(180deg);
        }
      }

      .ccb-order-status-color-item {
        cursor: pointer;
        display: flex;
        width: 100%;
        height: 100%;
        align-items: center;
        padding: 0 8px;
        background-color: #ffffff;
        border-radius: 4px;
        border: 1px solid #dddddd;
        background: #ffffff;

        color: #001931;
        font-size: 14px;
        font-weight: 500;

        span {
          padding: 0 8px;
          border-radius: 4px;
        }
      }

      &.field_error {
        span {
          border-color: #d94141;
          color: #d94141;
        }
      }
    }
  }
}
</style>
