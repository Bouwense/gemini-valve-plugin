<template>
  <div
    class="order-details-overlay"
    @click="close"
    v-if="orderDetails?.order_id"
  ></div>
  <div
    class="order-details"
    :class="{
      'ccb-details-active': orderDetails?.order_id,
    }"
  >
    <div class="order-details-header" v-if="orderDetails?.order_id">
      <div class="order-details-header__info">
        <span class="order-details-header__info-details">
          <span class="order-details-header__info-id">
            # {{ orderDetails?.order_id }}
          </span>
          <span class="order-details-header__info-date">
            {{ orderDetails?.formatted_date }}
          </span>
        </span>

        <span class="order-details-header__info-actions">
          <span class="order-details-header__info-download">
            <i
              class="ccb-icon-Union-15"
              @click="showActionList = !showActionList"
            ></i>
            <ActionList
              class="order-details-actions"
              v-if="showActionList && orderDetails"
              @close="showActionList = false"
              @select="selectActions"
              :items="getActionItems"
            />
          </span>
          <span class="order-details-header__info-close" @click="close">
            <i class="ccb-icon-close"></i>
          </span>
        </span>
      </div>

      <div class="order-details-header__label">
        {{ orderDetails?.calc_title }}
      </div>
    </div>

    <div class="order-details-content" v-if="orderDetails">
      <template v-for="sortedSetting in sortedOrdersSettings">
        <OrdersFormDetails
          v-if="showDetails('form_details', sortedSetting.key)"
          :order-details="orderDetails.order_form_details"
        />

        <OrdersCalculatorFields
          v-if="showDetails('calculator_details', sortedSetting.key)"
          :fields="orderDetails.calculator_fields"
          :totals="orderDetails.totals"
          :other-totals="orderDetails.other_totals"
          :total_amount="orderDetails.total_amount"
          :currency="orderDetails.currency"
        />

        <OrdersAttachments
          v-if="
            showDetails('attachments', sortedSetting.key) &&
            getAllAttachments?.length > 0
          "
          :attachments="getAllAttachments"
          @downloadAll="downloadAll"
        />

        <OrdersNotes
          v-if="showDetails('notes', sortedSetting.key)"
          :notes="orderDetails?.notes"
          @create-note="createNote"
          @update-note="updateNote"
          @delete-note="deleteNote"
        />
      </template>
    </div>

    <div class="order-details-footer">
      <span
        class="status-badge"
        @click.stop="showStatusList = !showStatusList"
        :class="`ccb-color-${getColorClass(orderDetails?.payment_status_slug || 'gray')}-wrapper`"
      >
        <span
          :class="`ccb-color-${getColorClass(orderDetails?.payment_status_slug || 'gray')}`"
          >{{ orderDetails?.payment_status }}</span
        >
        <i
          class="ccb-icon-Path-3514"
          :class="`ccb-color-${getColorClass(orderDetails?.payment_status_slug || 'gray')}`"
        ></i>
      </span>

      <StatusList
        v-if="showStatusList && orderDetails"
        @close="showStatusList = false"
        @select="selectStatus"
        :items="ordersStore.getStatuses"
        :id="+orderDetails?.order_id"
        :has-option="false"
        :is-order-details="true"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import JSZip from "jszip";
import { saveAs } from "file-saver";
import { toRefs, computed, ref, isRef, Ref } from "vue";
import {
  OrdersFormDetails,
  OrdersCalculatorFields,
  OrdersAttachments,
  OrdersNotes,
} from "@/orders/widgets/Orders";
import {
  IOrders,
  IActions,
  IAttachments,
  ICalculatorFields,
} from "@/orders/shared/types/orders.type";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";
import { IOrdersSettings } from "@/orders/shared/types/orders.settings";
import { debounce } from "@/orders/shared/utils/useDebounce";
import StatusList from "@/orders/shared/ui/common/StatusList.vue";
import ActionList from "@/orders/shared/ui/common/ActionList.vue";

const ordersStore = useOrdersStore();

const showStatusList = ref<boolean>(false);
const showActionList = ref<boolean>(false);

type Props = {
  orderDetails: IOrders | null;
};

type LocalAttachments = {
  name: string;
  url: string;
};

const props = defineProps<Props>();
const { orderDetails } = toRefs(props);

const emit = defineEmits(["close"]);

const ordersSettings = computed(() => {
  return ordersStore.getOrdersSettings;
});

const sortedOrdersSettings = computed(() => {
  return Object.values(ordersSettings.value)
    .sort((a, b) => a.sort_id - b.sort_id)
    .map((setting) => {
      return {
        key: setting.key,
        sort_id: setting.sort_id,
      };
    });
});

const showDetails = computed(() => {
  return (key: string, sortedKey: string) => {
    const sorted = sortedOrdersSettings.value.find(
      (setting) => setting.key === sortedKey,
    );

    const orderDetailsItem = ordersSettings.value[key as keyof IOrdersSettings];
    if (sorted && orderDetailsItem) {
      return (
        +sorted.sort_id === +orderDetailsItem.sort_id && orderDetailsItem.value
      );
    }
    return false;
  };
});

const close = () => {
  showActionList.value = false;
  showStatusList.value = false;
  emit("close");
};

const getColorClass = computed(() => {
  return (slug: string) => {
    const status = ordersStore.getStatuses.find(
      (status) => status.slug === slug,
    );
    if (status) {
      return status.color;
    }

    return "gray";
  };
});

const handleUpdateOrderStatus = debounce((id: number, status_id: number) => {
  ordersStore.updateOrderStatus(id, status_id);
}, 500);

const selectStatus = (id: number, status_id: number) => {
  handleUpdateOrderStatus(id, status_id);
  const status = ordersStore.getStatuses.find(
    (status) => status.id === status_id,
  );
  if (status && orderDetails.value) {
    orderDetails.value.payment_status = status.status_name;
    orderDetails.value.payment_status_slug = status.slug;
  }
  showStatusList.value = false;
};

const selectActions = (action: string) => {
  if (action === "download_attachments") {
    downloadAll();
  } else if (action === "delete" && orderDetails.value?.order_id) {
    ordersStore.deleteOrder(orderDetails.value.order_id);
    close();
  }

  showActionList.value = false;
};

const getActionItems = computed((): IActions[] => {
  return [
    {
      label: "Download Attachments",
      action: "download_attachments",
      is_danger: false,
    },
    {
      label: "Delete Order from Database",
      action: "delete",
      is_danger: true,
    },
  ];
});

const getAllAttachments = computed(() => {
  const result: IAttachments[] = [];
  const fileFields: ICalculatorFields[] =
    orderDetails.value?.calculator_fields.filter((field) =>
      field.alias.includes("file_upload"),
    ) || [];

  for (const field of fileFields) {
    for (const option of field.options) {
      result.push(option as IAttachments);
    }
  }

  const localAttachments: LocalAttachments[] = [];
  for (const attachment of result) {
    const path = attachment.url;
    const name = path.split("/").pop() || "";
    localAttachments.push({
      name,
      url: attachment.url,
    });
  }

  return localAttachments;
});

async function downloadAll(): Promise<void> {
  try {
    const zip = new JSZip();

    const files: LocalAttachments[] =
      (isRef(getAllAttachments.value)
        ? (getAllAttachments.value as Ref<LocalAttachments[]>).value
        : getAllAttachments.value) || [];

    if (files.length === 0) {
      console.warn("No files to download");
      return;
    }

    await Promise.all(
      files.map(async (file, index) => {
        const res = await fetch(file.url);
        if (!res.ok)
          throw new Error(`Failed to fetch ${file.url}: ${res.status}`);
        const buffer = await res.arrayBuffer();

        let fileName = file.name;
        const fileCount = files.filter(
          (f, i) => i < index && f.name === file.name,
        ).length;
        if (fileCount > 0) {
          const nameParts = fileName.split(".");
          const ext = nameParts.pop();
          fileName = `${nameParts.join(".")}_${fileCount + 1}.${ext}`;
        }

        zip.file(fileName, buffer);
        return fileName;
      }),
    );

    const blob = await zip.generateAsync({ type: "blob" }, (_) => {});

    saveAs(blob, "all-files.zip");
  } catch (err) {
    console.error("downloadAll error", err);
  }
}

const createNote = async (content: string) => {
  if (orderDetails.value?.order_id && content.trim()) {
    const notes = await ordersStore.createOrderNote(
      +orderDetails.value?.order_id,
      content,
    );
    if (Array.isArray(notes) && notes.length > 0) {
      orderDetails.value.notes = notes;
    }
  }
};

const updateNote = async (note_id: number, content: string) => {
  if (orderDetails.value?.order_id && note_id && content.trim()) {
    const notes = await ordersStore.updateOrderNote(note_id, content);
    if (Array.isArray(notes) && notes.length > 0) {
      orderDetails.value.notes = notes;
    }
  }
};

const deleteNote = async (note_id: number) => {
  if (orderDetails.value?.order_id && note_id) {
    const notes = await ordersStore.deleteOrderNote(note_id);
    if (Array.isArray(notes)) {
      orderDetails.value.notes = notes;
    }
  }
};
</script>

<style scoped lang="scss">
.order-details {
  overflow-y: auto;
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  background: #fefeff;
  width: 420px;
  border-radius: 16px 0 0 16px;
  box-shadow: 0 5px 30px 0 rgba(0, 0, 0, 0.15);
  z-index: 1000;
  right: -460px;
  transition: 0.15s ease-in-out;
  padding: 24px;

  display: flex;
  flex-direction: column;
  row-gap: 20px;

  &.ccb-details-active {
    right: 0;
  }

  &-header {
    display: flex;
    flex-direction: column;
    row-gap: 6px;

    &__info {
      display: flex;
      justify-content: space-between;
      position: relative;
      column-gap: 12px;

      &-details {
        display: flex;
        column-gap: 10px;
      }

      &-download {
        cursor: pointer;

        i {
          display: inline-flex;
          transform: rotate(90deg);
          color: #b9bdc2;
          font-size: 14px;
        }

        &:hover {
          color: #9ea3aa;
        }
      }

      &-id {
        color: #162432;
        font-size: 14px;
        font-weight: 700;
        line-height: normal;
      }

      &-date {
        color: rgba(22, 36, 50, 0.85);
        font-size: 14px;
        font-weight: 500;
        line-height: normal;
      }

      &-close {
        cursor: pointer;
        color: #b9bdc2;
        font-size: 14px;
        transition: 0.2s linear;

        &:hover {
          color: #9ea3aa;
        }
      }

      &-actions {
        display: flex;
        column-gap: 14px;
      }
    }

    &__label {
      color: #162432;
      font-size: 20px;
      font-weight: 700;
      line-height: 1;
    }
  }

  &-footer {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    padding: 16px 24px;
    background: #fff;
    border-radius: 0 0 16px 16px;
    border-top: 1px solid rgba(22, 36, 50, 0.1);
  }
}

.status-badge {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  text-align: center;
  display: flex;
  align-items: center;
  column-gap: 4px;
  cursor: pointer;
  height: 32px;
  display: flex;
  align-items: center;
  width: 100%;
  justify-content: space-between;
  padding: 0 16px;

  i {
    font-size: 12px;
    position: relative;
    top: 1px;
  }
}

.order-details-overlay {
  background: rgba(0, 0, 0, 0.5) !important;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: transparent;
  z-index: 110;
  cursor: pointer;
}

.order-details-content {
  width: 100%;
  display: flex;
  flex-direction: column;
  row-gap: 20px;
  overflow-y: auto;
  height: calc(100% - 100px);
  padding-bottom: 12px;
}
</style>
