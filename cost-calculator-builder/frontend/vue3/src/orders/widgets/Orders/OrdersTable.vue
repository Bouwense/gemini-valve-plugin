<template>
  <div class="ccb-orders-table-container">
    <div class="toolbar" v-if="ordersStore.getFirstLoadOrdersCount > 0">
      <div class="tabs" role="tablist">
        <span class="tab-count">
          <i class="ccb-icon-Chart-1"></i>
          <span>{{ getTotal }} Orders</span>
        </span>
        <button
          class="tab"
          data-filter="all"
          @click="filterOrders('all')"
          :class="{ 'ccb-filter-active': currentFilter === 'all' }"
        >
          All
        </button>
        <template v-for="(status, idx) in getStatuses">
          <button
            class="tab"
            :data-filter="status.slug"
            @click="filterOrders(status.slug)"
            v-if="idx < 4"
            :class="{
              'ccb-filter-active': currentFilter === status.slug,
              'ccb-disable-filter': getSelectedFilterStatuses?.length > 0,
            }"
          >
            <span> {{ status.status_name }}</span>
            <span>({{ status.count }})</span>
          </button>
        </template>
        <div class="ccb-more-status-wrapper">
          <span
            @click="showMoreStatuses = !showMoreStatuses"
            v-if="getStatuses.length > 4"
            class="ccb-more-status-btn"
          >
            <span>More</span>
            <i class="ccb-icon-Path-3514"></i>
          </span>
          <StatusList
            v-if="showMoreStatuses"
            :id="0"
            @close="showMoreStatuses = false"
            @select="selectStatusMoreStatus"
            :items="getMoreStatuses"
          />
        </div>
      </div>
      <div class="actions">
        <a :href="getWooUrl" target="_blank" class="woocommerce-link">
          WooCommerce Orders
          <i class="ccb-icon-click-out"></i>
        </a>
        <div class="actions-buttons">
          <button
            class="btn export-btn"
            style="opacity: 0.4; pointer-events: none"
          >
            <i class="ccb-icon-Path-34581"></i>
          </button>
          <OrdersSettings />
        </div>
      </div>
    </div>

    <div class="table-container" v-if="ordersStore.getFirstLoadOrdersCount > 0">
      <div class="table-header-row" :class="gridClasses">
        <OrdersBulkActions
          :selected-count="selectedOrderIds.length"
          @cancel="cancelBulkActions"
          @delete="deleteBulkActions"
          @select-status="selectStatusBulkActions"
          :key="selectedOrderIds.length"
        />
        <div
          class="header-cell checkbox-cell"
          v-if="getSortedSettings?.length > 0"
        >
          <div class="ccb-select-box">
            <input
              id="select-all"
              type="checkbox"
              v-model="isSelectAll"
              @change="selectAllOrders"
            />
          </div>
        </div>

        <template v-for="sortedSettings in getSortedSettings">
          <div
            v-if="showHeaderCell('order_id', sortedSettings.key)"
            class="header-cell"
          >
            <div class="sort-box">
              <input
                type="number"
                placeholder="#"
                class="header-input"
                @input="
                  updateSearch('id', ($event.target as HTMLInputElement).value)
                "
              />
              <span class="sort-by">
                <i
                  class="ccb-icon-Path-3514 ccb-up-arrow"
                  :class="{ active: sortSettings.id === 'asc' }"
                  @click="updateSort('asc', 'id')"
                ></i>
                <i
                  class="ccb-icon-Path-3514 ccb-down-arrow"
                  :class="{ active: sortSettings.id === 'desc' }"
                  @click="updateSort('desc', 'id')"
                ></i>
              </span>
            </div>
          </div>

          <div
            v-if="showHeaderCell('type', sortedSettings.key)"
            class="header-cell"
          >
            <input
              type="text"
              placeholder="Name"
              class="header-input"
              @input="
                updateSearch('type', ($event.target as HTMLInputElement).value)
              "
            />
          </div>

          <div
            v-if="showHeaderCell('email', sortedSettings.key)"
            class="header-cell"
          >
            <input
              type="text"
              placeholder="Email"
              class="header-input"
              @input="
                updateSearch('email', ($event.target as HTMLInputElement).value)
              "
            />
          </div>
          <div
            v-if="showHeaderCell('date', sortedSettings.key)"
            class="header-cell ccb-disable-filter"
          >
            <input type="text" placeholder="Date" class="header-input" />
          </div>

          <div
            v-if="showHeaderCell('calculator', sortedSettings.key)"
            class="header-cell"
          >
            <input
              type="text"
              placeholder="Calculator"
              class="header-input"
              @input="
                updateSearch(
                  'calculator',
                  ($event.target as HTMLInputElement).value,
                )
              "
            />
          </div>

          <div
            v-if="showHeaderCell('payment', sortedSettings.key)"
            class="header-cell"
          >
            <div class="header-select-box">
              <select
                placeholder="Payment"
                class="header-input"
                @change="
                  updateSearch(
                    'payment',
                    ($event.target as HTMLSelectElement).value,
                  )
                "
              >
                <option value="">Payment</option>
                <option
                  v-for="payment in getPayments"
                  :value="payment.key"
                  :key="payment.key"
                >
                  {{ payment.label }}
                </option>
              </select>
              <i class="ccb-icon-Path-3514"></i>
            </div>
          </div>

          <div
            v-if="showHeaderCell('status', sortedSettings.key)"
            class="header-cell"
            :class="{ 'ccb-disable-filter': currentFilter !== 'all' }"
          >
            <div class="header-select-box" style="position: relative">
              <span
                class="status-list-toggle"
                :class="{ 'multi-status-list-active': toggleMultiStatusList }"
                @click="toggleMultiStatusList = !toggleMultiStatusList"
              >
                <span
                  v-if="
                    (ordersStore.getParams.search.status?.length || 0) === 0
                  "
                  >Status</span
                >
                <span v-else
                  >{{
                    ordersStore.getParams.search.status?.length || 0
                  }}
                  Selected</span
                >
                <i class="ccb-icon-Path-3514"></i>
              </span>
              <StatusListMulti
                v-if="toggleMultiStatusList"
                @close="toggleMultiStatusList = false"
                @select="selectMultiStatus"
                :items="ordersStore.getStatuses"
                class="ccb-orders-filter"
                :selected-statuses="ordersStore.getParams.search.status || []"
              />
            </div>
          </div>

          <div
            v-if="showHeaderCell('amount', sortedSettings.key)"
            class="header-cell amount-header"
          >
            <div class="sort-box" style="column-gap: 8px">
              <span>Amount</span>
              <span class="sort-by">
                <i
                  class="ccb-icon-Path-3514 ccb-up-arrow"
                  :class="{ active: sortSettings.amount === 'asc' }"
                  @click="updateSort('asc', 'amount')"
                ></i>
                <i
                  class="ccb-icon-Path-3514 ccb-down-arrow"
                  :class="{ active: sortSettings.amount === 'desc' }"
                  @click="updateSort('desc', 'amount')"
                ></i>
              </span>
            </div>
          </div>
        </template>
        <div v-if="getRequestLoader" class="ccb-loading-bar"></div>
      </div>

      <div class="table-body" v-if="ordersStore.getOrders?.length > 0">
        <div
          class="table-row"
          :class="gridClasses"
          v-for="order in ordersStore.getOrders"
          :key="order.order_id"
          @click.stop="() => selectOrder(order.order_id)"
        >
          <div class="table-cell checkbox-cell" data-label="">
            <div class="ccb-select-box">
              <input
                :id="`select-${order.order_id}`"
                type="checkbox"
                v-model="selectedOrderIds"
                :value="order.order_id"
                @click.stop="updateSelectedOrderIds(order.order_id)"
              />
            </div>
          </div>

          <template v-for="sortedSettings in getSortedSettings">
            <div
              v-if="showHeaderCell('order_id', sortedSettings.key)"
              class="table-cell"
              data-label="#"
            >
              #{{ order.order_id }}
            </div>
            <div
              v-if="showHeaderCell('type', sortedSettings.key)"
              class="table-cell"
              data-label="Name"
            >
              {{ order.client_name }}
            </div>
            <div
              v-if="showHeaderCell('email', sortedSettings.key)"
              class="table-cell"
              data-label="Email"
            >
              {{ order.client_email }}
            </div>
            <div
              v-if="showHeaderCell('date', sortedSettings.key)"
              class="table-cell"
              data-label="Date"
            >
              {{ order.created_at }}
            </div>
            <div
              v-if="showHeaderCell('calculator', sortedSettings.key)"
              class="table-cell"
              data-label="Calculator"
              style="display: flex; align-items: center; column-gap: 4px"
            >
              <span>{{ order.calc_title }}</span>
              <i
                class="ccb-icon-Layer-2"
                v-if="hasFiles(order.calculator_fields)"
              ></i>
            </div>
            <div
              v-if="showHeaderCell('payment', sortedSettings.key)"
              class="table-cell"
              data-label="Payment"
            >
              {{ order.payment_type_label }}
            </div>

            <div
              v-if="showHeaderCell('status', sortedSettings.key)"
              class="table-cell"
              data-label="Status"
            >
              <span
                class="status-badge status-new"
                @click.stop="openStatusList(order.order_id)"
                :class="`ccb-color-${getColorClass(order.payment_status_slug)}-wrapper`"
              >
                <span
                  :class="`ccb-color-${getColorClass(order.payment_status_slug)}`"
                  >{{ order.payment_status }}</span
                >
                <i
                  class="ccb-icon-Path-3514"
                  :class="`ccb-color-${getColorClass(order.payment_status_slug)}`"
                ></i>
              </span>
              <StatusList
                v-if="showStatusList[order.order_id]"
                @close="showStatusList[order.order_id] = false"
                @select="selectStatus"
                :items="ordersStore.getStatuses"
                :id="+order.order_id"
              />
            </div>

            <div
              v-if="showHeaderCell('amount', sortedSettings.key)"
              class="table-cell amount-cell"
              data-label="Amount"
            >
              {{ formatCurrency(order) }}
            </div>
          </template>
        </div>
      </div>
      <EmptyOrders
        v-else
        label="No orders found"
        description="Use less strict filters"
      />
    </div>

    <OrdersPagination
      :key="getTotal"
      v-if="+getTotal > 9 && ordersStore.getFirstLoadOrdersCount > 0"
    />

    <OrdersDetails
      :order-details="orderDetails"
      @close="closeOrderDetails"
      v-if="ordersStore.getFirstLoadOrdersCount > 0"
    />

    <EmptyOrders
      v-if="ordersStore.getFirstLoadOrdersCount === 0"
      label="No orders found"
      description="Promote your products or services, and new orders will appear soon"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import {
  OrdersBulkActions,
  OrdersDetails,
  OrdersPagination,
  OrdersSettings,
} from "@/orders/widgets/Orders";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";
import { IOrders } from "@/orders/shared/types/orders.type";
import StatusList from "@/orders/shared/ui/common/StatusList.vue";
import StatusListMulti from "@/orders/shared/ui/common/StatusListMulti.vue";
import { ITableSettings } from "@/orders/shared/types/table.settings";
import { arraysEqualAsSets } from "@/orders/shared/utils/array-compare.utils";
import { IStatuses } from "@/orders/shared/types/orders.type";
import EmptyOrders from "@/orders/shared/ui/common/EmptyOrders.vue";
import { debounce } from "@/orders/shared/utils/useDebounce";
import { ICalculatorFields } from "@/orders/shared/types/orders.type";
import { currencyConvertor } from "@/orders/shared/utils/useCurrencyConvertor";

const currentFilter = ref<string>("all");
const ordersStore = useOrdersStore();
const showMoreStatuses = ref<boolean>(false);
const selectedOrderIds = ref<number[]>([]);
const isSelectAll = ref<boolean>(false);

const showStatusList = ref<Record<number, boolean>>({});
const toggleMultiStatusList = ref<boolean>(false);
const orderDetails = ref<IOrders | null>(null);

const getTotal = computed(() => {
  if (ordersStore.getTotal < 10) {
    localStorage.setItem("orders_current_page", "1");
  }
  return ordersStore.getTotal;
});

const tableSettings = computed(() => {
  return ordersStore.getTableSettings;
});

const sortSettings = computed(() => {
  return ordersStore.getParams.sort;
});

const handleFetchOrders = debounce(() => {
  ordersStore.fetchOrders();
}, 500);

const updateSort = (sort: "asc" | "desc", key: "id" | "amount") => {
  const params = ordersStore.getParams;
  if (params.sort[key] !== sort) {
    params.sort[key] = sort;

    if (key === "id") {
      params.sort.amount = "";
    } else if (key === "amount") {
      params.sort.id = "";
    }

    ordersStore.setParams(params);
    handleFetchOrders();
  }
};

const updateSearch = (key: string, value: string | number) => {
  const params = ordersStore.getParams;
  const filter_keys = ["id", "calculator", "email", "type", "payment"];
  if (filter_keys.includes(key) && !Array.isArray(value)) {
    if (key === "id") {
      params.search.id = value as string;
    } else if (key === "calculator") {
      params.search.calculator = value as string;
    } else if (key === "email") {
      params.search.email = value as string;
    } else if (key === "type") {
      params.search.name = value as string;
    } else if (key === "payment") {
      params.search.payment = value as string;
    }

    ordersStore.setParams(params);
    handleFetchOrders();
  }
};

const getSortedSettings = computed(() => {
  const res = Object.values(tableSettings.value)
    .sort((a, b) => a.sort_id - b.sort_id)
    .map((setting) => {
      return {
        key: setting.key,
        sort_id: setting.sort_id,
      };
    });

  return res;
});

const visibleColumnsCount = computed(() => {
  let count = 1;
  const settings = tableSettings.value;

  if (settings?.order_id?.value) count++;
  if (settings?.type?.value) count++;
  if (settings?.email?.value) count++;
  if (settings?.date?.value) count++;
  if (settings?.calculator?.value) count++;
  if (settings?.payment?.value) count++;
  if (settings?.status?.value) count++;
  if (settings?.amount?.value) count++;

  return count;
});

const gridClasses = computed(() => {
  const totalColumns = 9;
  const visibleCount = visibleColumnsCount.value;

  if (visibleCount < totalColumns) {
    return `cols-${visibleCount}`;
  }
  return "";
});

const selectAllOrders = () => {
  if (isSelectAll.value) {
    selectedOrderIds.value =
      ordersStore.getOrders?.map((order) => +order.order_id) || [];
  } else {
    selectedOrderIds.value = [];
  }
};
const selectOrder = (id: number) => {
  const order = ordersStore.getOrders.find((order) => order.order_id === id);
  orderDetails.value = order || null;
};

const closeOrderDetails = () => {
  orderDetails.value = null;
};

const openStatusList = (id: number) => {
  for (const key in showStatusList.value) {
    if (+key !== +id) {
      showStatusList.value[+key.toString()] = false;
    }
  }
  showStatusList.value[+id.toString()] = !showStatusList.value[+id.toString()];
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
  showStatusList.value[id] = false;
  filterOrders(currentFilter.value);
};

const selectMultiStatus = (status_ids: number[]) => {
  const parmas = ordersStore.getParams;
  const paramsList = Array.isArray(parmas.search.status)
    ? parmas.search.status
    : [];

  if (!arraysEqualAsSets(paramsList, status_ids)) {
    parmas.search.status = status_ids;
    ordersStore.setParams(parmas);
    handleFetchOrders();
  }

  toggleMultiStatusList.value = false;
};

const updateSelectedOrderIds = (id: number) => {
  if (selectedOrderIds.value.includes(+id)) {
    selectedOrderIds.value = selectedOrderIds.value.filter(
      (item) => +item !== +id,
    );
  } else {
    selectedOrderIds.value.push(+id);
  }
};

const cancelBulkActions = () => {
  selectedOrderIds.value = [];
  isSelectAll.value = false;
};

const selectStatusBulkActions = (status_id: number) => {
  ordersStore.updateOrdersStatus(selectedOrderIds.value, status_id);
  selectedOrderIds.value = [];
  isSelectAll.value = false;
};

const getStatuses = computed(() => {
  return ordersStore.getStatuses;
});

const filterOrders = (slug: string) => {
  if (currentFilter.value !== slug) {
    currentFilter.value = slug;
    const status = ordersStore.getStatuses.find(
      (status) => status.slug === slug,
    );

    if (status) {
      const parmas = ordersStore.getParams;
      parmas.filter.status = status.id;
      ordersStore.setParams(parmas);
      handleFetchOrders();
    } else if (slug === "all") {
      const parmas = ordersStore.getParams;
      delete parmas.filter.status;
      ordersStore.setParams(parmas);
      handleFetchOrders();
    }
  }
};

const selectStatusMoreStatus = (_: number, status_id: number) => {
  const status = ordersStore.getStatuses.find(
    (status) => +status.id === +status_id,
  );
  if (status) {
    filterOrders(status.slug);
  }

  showMoreStatuses.value = false;
};

const showHeaderCell = computed(() => {
  return (key: string, sortedKey: string) => {
    const sorted = getSortedSettings.value.find(
      (setting) => setting.key === sortedKey,
    );

    const tableSettingsItem = tableSettings.value[key as keyof ITableSettings];

    if (sorted && tableSettingsItem) {
      return (
        +sorted.sort_id === +tableSettingsItem.sort_id &&
        tableSettingsItem.value
      );
    }

    return false;
  };
});

const getWooUrl = computed(() => {
  if (
    "ccb_ajax_window" in window &&
    window?.ccb_ajax_window &&
    typeof window?.ccb_ajax_window === "object" &&
    "woo_url" in window?.ccb_ajax_window
  ) {
    return (window?.ccb_ajax_window?.woo_url as string) || "";
  }

  return "";
});

const getPayments = computed(() => {
  return [
    { key: "no_payments", label: "No Payment" },
    { key: "paypal", label: "Paypal" },
    { key: "stripe", label: "Stripe" },
    { key: "woocommerce", label: "WooCommerce" },
    { key: "razorpay", label: "Razorpay" },
    { key: "cash_payment", label: "Cash Payment" },
  ];
});

const getSelectedFilterStatuses = computed(() => {
  return Array.isArray(ordersStore.getParams.search.status)
    ? ordersStore.getParams.search.status
    : [];
});

const getMoreStatuses = computed(() => {
  const result: IStatuses[] = [];
  for (const status of getStatuses.value) {
    const clone_status = JSON.parse(JSON.stringify(status));
    if (clone_status.id > 4) {
      const status_name =
        clone_status.status_name.length >= 11
          ? clone_status.status_name.substring(0, 8) + "..."
          : clone_status.status_name;
      clone_status.status_name = status_name + `(${clone_status.count})`;
      result.push(clone_status);
    }
  }
  return result;
});

const getRequestLoader = computed(() => {
  return ordersStore.getRequestLoader;
});

const deleteBulkActions = () => {
  ordersStore.deleteOrders(selectedOrderIds.value);
  selectedOrderIds.value = [];
  isSelectAll.value = false;
};

const hasFiles = computed(() => {
  return (calculator_fields: ICalculatorFields[]) => {
    const fileFields: ICalculatorFields[] = calculator_fields.filter((field) =>
      field.alias.includes("file_upload"),
    );

    if (fileFields?.length > 0) {
      return fileFields.filter((field) => field.options?.length > 0).length > 0;
    }
    return false;
  };
});

const formatCurrency = computed(() => {
  return (order: IOrders) => {
    return currencyConvertor(Number(order.total_amount), {
      currency: order.currency.currency_sign,
      currencyPosition: order.currency.currency_position,
      numAfterInteger: order.currency.number_of_decimals,
      thousandsSeparator: order.currency.thousand_separator,
      decimalSeparator: order.currency.decimal_separator,
    });
  };
});
</script>

<style scoped lang="scss">
.ccb-orders-table-container {
  margin: 0;
  padding: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  width: 100%;
  box-sizing: border-box;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
  height: 100%;
}

.table-container {
  overflow-x: auto;
  width: 100%;
  height: calc(100% - 58px);
}

.table-header-row,
.table-row {
  display: grid;
  grid-template-columns: 40px 1fr 2fr 2fr 2fr 2fr 1.5fr 1.5fr 1.5fr;
  gap: 12px;
  padding: 0 8px;
  min-width: 800px;
  width: 100%;
}

.table-header-row.flexible-columns,
.table-row.flexible-columns {
  grid-template-columns: 40px repeat(auto-fit, minmax(80px, 1fr));
  min-width: 100%;
}

.table-header-row.cols-9,
.table-row.cols-9 {
  grid-template-columns: 40px repeat(8, 1fr);
}
.table-header-row.cols-8,
.table-row.cols-8 {
  grid-template-columns: 40px repeat(7, 1fr);
}
.table-header-row.cols-7,
.table-row.cols-7 {
  grid-template-columns: 40px repeat(6, 1fr);
}
.table-header-row.cols-6,
.table-row.cols-6 {
  grid-template-columns: 40px repeat(5, 1fr);
}
.table-header-row.cols-5,
.table-row.cols-5 {
  grid-template-columns: 40px repeat(4, 1fr);
}
.table-header-row.cols-4,
.table-row.cols-4 {
  grid-template-columns: 40px repeat(3, 1fr);
}
.table-header-row.cols-3,
.table-row.cols-3 {
  grid-template-columns: 40px repeat(2, 1fr);
}
.table-header-row.cols-2,
.table-row.cols-2 {
  grid-template-columns: 40px 1fr;
}

.table-header-row {
  position: sticky;
  top: 0;
  z-index: 10;
  height: 48px;
  background-color: #eef2f7;
}

.header-cell {
  padding: 8px;
  display: flex;
  align-items: center;
}

.header-select-box {
  position: relative;
  width: 100%;

  i {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(22, 36, 50, 0.3);
    font-size: 10px;
  }
}

.header-input {
  width: 100%;
  padding: 6px 8px;
  border-radius: 4px;
  background: white;
  font-size: 12px;
  color: #374151;
  outline: none;
  transition: all 0.2s;
  font-weight: 500;

  height: unset !important;
  min-height: unset !important;
  box-shadow: none !important;
  outline: none !important;
  border: none !important;
  padding-top: 0 !important;
  padding-bottom: 0 !important;
  line-height: 2 !important;
}

select.header-input {
  position: relative;
  &::after {
    content: "\e92c";
    font-family: "ccb-fonts" !important;
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
  }

  &:hover {
    color: #374151;
  }
}

input.header-input::-webkit-outer-spin-button,
input.header-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input.header-input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}

.amount-header {
  justify-content: flex-end;
}

.amount-input {
  text-align: right;
}

.table-body {
  background: white;
  padding-bottom: 16px;
}

.table-row {
  border-bottom: 1px solid #f9fafb;
  transition: background-color 0.1s;

  &:last-child {
    border-bottom: none !important;
  }
}

.table-row:hover {
  background-color: #f9fafb;
}

.table-cell {
  padding: 12px 8px;
  font-size: 13px;
  color: #374151;
  display: flex;
  align-items: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;

  i {
    font-size: 14px;
    color: #9ca3af;
    cursor: pointer;
    transition: all 0.2s;

    &:hover {
      color: #6b7280;
    }
  }
}

.table-row .table-cell {
  text-indent: 4px;
}

.amount-cell {
  justify-content: flex-end;
  font-weight: 600;
  color: #111827;
}

.checkbox-cell {
  justify-content: center;
}

.custom-checkbox {
  display: none;
}

.custom-checkbox + label {
  width: 16px;
  height: 16px;
  border: 1.5px solid #d1d5db;
  border-radius: 4px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.custom-checkbox:checked + label {
  background: #3b82f6;
  border-color: #3b82f6;
}

.custom-checkbox:checked + label::after {
  content: "✓";
  color: white;
  font-size: 11px;
  font-weight: bold;
}

.custom-checkbox + label:hover {
  border-color: #9ca3af;
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

  i {
    font-size: 8px;
    position: relative;
    top: 1px;
  }
}

@media (max-width: 1280px) {
  .table-container {
    overflow-x: scroll;
  }
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
}
.tabs {
  display: flex;
  gap: 8px;
  align-items: center;
  width: 65%;
}

.tab {
  background: #fff;
  border-radius: 8px;
  padding: 0 12px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  gap: 2px;
  align-items: center;
  display: flex;
  align-items: center;
  height: 34px;

  border-radius: 8px;
  border: 2px solid rgba(22, 36, 50, 0.08);
  color: rgba(22, 36, 50, 0.85);
  font-size: 14px;
  font-weight: 500;
  transition: 200ms linear;

  &:hover {
    border: 2px solid rgba(22, 36, 50, 0.16);
  }
}

.tab span {
  color: #6b7280;
  font-size: 13px;
}

.tab.ccb-filter-active {
  border-radius: 8px;
  background: #1ab163;
  border-color: #1ab163;
  color: #fff;

  span {
    color: #fff;
  }

  &:hover {
    border-color: #179b57;
    background: #179b57;
  }
}

.woocommerce-link,
.tab-count {
  padding: 0 13px;
  height: 34px;
  align-items: center;
  display: flex;
  border-radius: 8px;
  background: #e8f7ef;
  color: #1ab163;
  font-size: 14px;
  font-weight: 500;
  line-height: 16px;
  text-wrap: nowrap;
  gap: 6px;
}

.tab-count {
  margin-right: 16px;
}

.woocommerce-link {
  text-decoration: none;
  outline: none !important;
  box-shadow: none !important;
  border: none !important;
  cursor: pointer;
  transition: 200ms linear;

  &:hover {
    color: #179b57;
  }
}

.actions {
  display: flex;
  gap: 8px;
  justify-content: space-between;
  width: 35%;
}

.btn {
  background: #f1f3f7;
  border-radius: 8px;
  padding: 8px 10px;
  border: 0;
  cursor: pointer;
}

.actions-buttons {
  display: flex;
  gap: 8px;

  button {
    padding: 0;
    height: 34px;
    align-items: center;
    justify-content: center;
    width: 56px;
    transition: 200ms linear;
    cursor: pointer;

    &:hover {
      background: #e1e5ee;
    }
  }
}

.sort-by {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-left: 8px;

  i {
    font-size: 8px;
    color: rgba(22, 36, 50, 0.3);
    cursor: pointer;

    &.ccb-up-arrow {
      transform: rotate(180deg);
    }

    &:hover {
      color: rgba(22, 36, 50, 0.6);
    }

    &.active {
      color: rgba(22, 36, 50, 0.6);
    }
  }
}

.sort-box {
  display: flex;
  align-items: center;
}

.ccb-disable-filter {
  opacity: 0.5;

  &,
  & * {
    pointer-events: none;
  }
}

.status-list-toggle {
  display: flex;
  align-items: center;
  cursor: pointer;
  justify-content: space-between;
  width: 100%;
  border-radius: 4px;
  background-color: #ffffff;
  padding: 6px 8px;
  line-height: 1;

  i {
    top: 51% !important;
    font-size: 10px;
    color: rgba(22, 36, 50, 0.3);
    transition: all 200ms linear;
  }

  &.multi-status-list-active {
    i {
      top: 30% !important;
      transform: rotate(180deg);
    }
  }
}

.ccb-more-status-wrapper {
  position: relative;

  .ccb-more-status-btn {
    height: 34px;
    padding: 0 12px;
    border-radius: 8px;
    background: rgba(22, 36, 50, 0.05);
    display: flex;
    align-items: center;
    cursor: pointer;
    column-gap: 8px;

    span {
      color: rgba(22, 36, 50, 0.85);
      font-size: 14px;
      font-weight: 500;
      line-height: 1;
    }

    i {
      font-size: 12px;
      color: rgba(22, 36, 50, 0.5);
      transition: all 200ms linear;
      top: 1px;
      position: relative;
    }

    &:hover {
      i {
        color: rgba(22, 36, 50, 0.7);
      }
    }
  }
}

.ccb-loading-bar {
  position: absolute;
  left: 0;
  top: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(90deg, #1ab163 0%, #67e8f9 50%, #1ab163 100%);
  background-size: 200% 100%;
  animation: loadingSlide 1.2s linear infinite;
  opacity: 0.9;
}

@keyframes loadingSlide {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
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
</style>
