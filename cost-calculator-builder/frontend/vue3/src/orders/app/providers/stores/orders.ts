import { defineStore } from "pinia";
import { fetchOrdersData } from "@/orders/shared/api/fetchOrders";
import { IFetchOrdersResponse } from "@/orders/shared/types/api/response.type";
import {
  IOrders,
  IError,
  IOrdersParams,
  IStatuses,
  IOrderNote,
} from "@/orders/shared/types/orders.type";
import { ITableSettings } from "@/orders/shared/types/table.settings";
import { IOrdersSettings } from "@/orders/shared/types/orders.settings";
import { toast } from "vue3-toastify";
import {
  updateOrderStatus,
  saveTableSettings,
  saveOrdersSettings,
  restoreOrdersSettings,
  restoreTablesSettings,
  deleteOrder,
  createOrderNote,
  updateOrderNote,
  deleteOrderNote,
  updateOrdersStatus,
  deleteOrders,
  updateStatuses,
} from "@/orders/shared/api";

interface IOrdersStore {
  orders: IOrders[];
  total: number;
  requestLoader: boolean;
  tableSettings: ITableSettings;
  tempTableSettings: ITableSettings;
  ordersSettings: IOrdersSettings;
  tempOrdersSettings: IOrdersSettings;
  statuses: IStatuses[];
  tempStatuses: IStatuses[];
  errorIdx: number[][];
  emptyColorErrors: number[];
  emptyTitleErrors: number[];
  errors: IError[];
  params: IOrdersParams;
  firstLoad: boolean;
  firstLoadOrdersCount: number;
  deleteOrderIds: number[];
  default_pending_status: number;
  default_complete_status: number;
}

export const useOrdersStore = defineStore("orders_store", {
  state: (): IOrdersStore => ({
    firstLoad: true,
    firstLoadOrdersCount: 10,
    deleteOrderIds: [],
    errorIdx: [],
    emptyColorErrors: [],
    emptyTitleErrors: [],
    errors: [],
    orders: [],
    total: 0,
    requestLoader: false,
    tableSettings: {} as ITableSettings,
    tempTableSettings: {} as ITableSettings,
    ordersSettings: {} as IOrdersSettings,
    tempOrdersSettings: {} as IOrdersSettings,
    statuses: [],
    tempStatuses: [],
    params: {
      filter: {},
      search: {
        id: "",
        name: "",
        email: "",
        calculator: "",
        payment: "",
      },
      sort: {
        id: "desc",
        amount: "",
      },
    },
    default_complete_status: 0,
    default_pending_status: 0,
  }),

  getters: {
    getDeleteOrderIds: (state: IOrdersStore): number[] => state.deleteOrderIds,
    getOrders: (state: IOrdersStore): any[] => state.orders,
    getTotal: (state: IOrdersStore): number => state.total,
    getTableSettings: (state: IOrdersStore): ITableSettings =>
      state.tableSettings,
    getTempTableSettings: (state: IOrdersStore): ITableSettings =>
      state.tempTableSettings,
    getOrdersSettings: (state: IOrdersStore): IOrdersSettings =>
      state.ordersSettings,
    getTempOrdersSettings: (state: IOrdersStore): IOrdersSettings =>
      state.tempOrdersSettings,
    getStatuses: (state: IOrdersStore): IStatuses[] => state.statuses,
    getTempStatuses: (state: IOrdersStore): IStatuses[] => state.tempStatuses,
    getErrorIdx: (state: IOrdersStore): number[][] => state.errorIdx,
    getEmptyColorErrors: (state: IOrdersStore): number[] =>
      state.emptyColorErrors,
    getEmptyTitleErrors: (state: IOrdersStore): number[] =>
      state.emptyTitleErrors,
    getErrors: (state: IOrdersStore): IError[] => state.errors,
    getParams: (state: IOrdersStore): IOrdersParams => state.params,
    getRequestLoader: (state: IOrdersStore): boolean => state.requestLoader,
    getFirstLoadOrdersCount: (state: IOrdersStore): number =>
      state.firstLoadOrdersCount,
    getDefaultCompleteStatus: (state: IOrdersStore): number =>
      state.default_complete_status,
    getDefaultPendingStatus: (state: IOrdersStore): number =>
      state.default_pending_status,
  },

  actions: {
    setDeleteOrderIds(ids: number): void {
      this.deleteOrderIds = [...this.deleteOrderIds, ids];
    },

    resetDeleteOrderIds(): void {
      this.deleteOrderIds = [];
    },

    setErrorIdx(ids: number[][]): void {
      this.errorIdx = ids;
    },

    setEmptyColorErrors(ids: number[]): void {
      this.emptyColorErrors = ids;
    },

    setEmptyTitleErrors(ids: number[]): void {
      this.emptyTitleErrors = ids;
    },

    setErrors(errors: IError[]): void {
      this.errors = errors;
    },

    setOrders(orders: IOrders[]): void {
      const parsedOrders = orders.map((o) => {
        const calcFields = o.calculator_fields.map((c) => {
          return {
            ...c,
            add_to_summary: parseInt(c.add_to_summary.toString()) === 1,
            is_basic: parseInt(c.is_basic.toString()) === 1,
            sort_id: +c.sort_id,
          };
        });

        return {
          ...o,
          calculator_fields: calcFields,
        };
      });

      this.orders = parsedOrders;
    },

    setTotal(value: number): void {
      this.total = value;
    },

    setTempTableSettings(settings: ITableSettings): void {
      this.tempTableSettings = settings;
    },

    setTableSettings(settings: ITableSettings): void {
      for (const key in settings) {
        const value = settings[key as keyof ITableSettings].value;
        if (typeof value === "string") {
          settings[key as keyof ITableSettings].value = value === "1";
        }
        settings[key as keyof ITableSettings].sort_id =
          +settings[key as keyof ITableSettings].sort_id;
      }

      this.tableSettings = settings;
      this.tempTableSettings = JSON.parse(JSON.stringify(settings));
    },

    setTempOrdersSettings(settings: IOrdersSettings): void {
      this.tempOrdersSettings = settings;
    },

    setOrdersSettings(settings: IOrdersSettings): void {
      for (const key in settings) {
        const value = settings[key as keyof IOrdersSettings].value;
        if (typeof value === "string") {
          settings[key as keyof IOrdersSettings].value = value === "1";
        }
        settings[key as keyof IOrdersSettings].sort_id =
          +settings[key as keyof IOrdersSettings].sort_id;
      }

      this.ordersSettings = settings;
      this.tempOrdersSettings = JSON.parse(JSON.stringify(settings));
    },

    setStatuses(statuses: IStatuses[], parse: boolean = true): void {
      if (parse) {
        statuses = statuses.map((status) => {
          return {
            ...status,
            id: +status.id,
            sort_id: +status.sort_id,
            is_default: parseInt(status.is_default.toString()) === 1,
            is_completed: parseInt(status.is_completed.toString()) === 1,
          };
        });
      }
      this.statuses = statuses;
      this.tempStatuses = JSON.parse(JSON.stringify(statuses));
    },

    setTempStatuses(statuses: IStatuses[]): void {
      this.tempStatuses = statuses;
    },

    resetStatuses(): void {
      this.setTempStatuses(JSON.parse(JSON.stringify(this.getStatuses)));
    },

    setParams(params: IOrdersParams): void {
      this.params = params;
    },

    setDefaultCompleteStatus(status: number): void {
      this.default_complete_status = status;
    },

    setDefaultPendingStatus(status: number): void {
      this.default_pending_status = status;
    },

    async fetchOrders() {
      this.requestLoader = true;

      const limit = parseInt(
        localStorage.getItem("orders_page_size") || "10",
        10,
      );
      const page = parseInt(
        localStorage.getItem("orders_current_page") || "1",
        10,
      );

      const response = await fetchOrdersData<IFetchOrdersResponse>({
        action: "ccb_orders_list",
        nonce: window?.ccb_nonces?.ccb_orders_list || "",
        limit,
        page,
        params: this.params,
      });

      if (response.success) {
        if (response.data.statuses.length > 0) {
          for (const status of response.data.statuses) {
            if (+status.is_completed) {
              this.setDefaultCompleteStatus(+status.id);
            }

            if (+status.is_default) {
              this.setDefaultPendingStatus(+status.id);
            }
          }
        }

        this.setOrders(response.data.orders);
        this.setTotal(response.data.total);
        this.setTableSettings(response.data.table_settings);
        this.setOrdersSettings(response.data.details_settings);
        if (Array.isArray(response.data.statuses)) {
          this.setStatuses(response.data.statuses);
        }

        if (this.firstLoad) {
          this.firstLoad = false;
          this.firstLoadOrdersCount = response.data.orders.length;
        }
      }

      this.requestLoader = false;
    },

    async saveStatuses() {
      this.requestLoader = true;
      const response = await updateStatuses({
        action: "ccb_update_status_list",
        nonce: window?.ccb_nonces?.ccb_update_status_list || "",
        statuses: this.getStatuses,
      });

      if (response.success) {
        if (response.data.statuses.length > 0) {
          for (const status of response.data.statuses) {
            if (+status.is_completed) {
              this.setDefaultCompleteStatus(+status.id);
            }
            if (+status.is_default) {
              this.setDefaultPendingStatus(+status.id);
            }
          }
        }

        this.setStatuses(response.data.statuses);
        toast(response.message, {
          type: "success",
        });
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
        });
      }

      this.requestLoader = false;
    },

    async updateOrderStatus(id: number, status_id: number): Promise<void> {
      this.requestLoader = true;

      const status = this.statuses.find((status) => status.id === status_id);

      this.setOrders(
        this.getOrders.map((order) => {
          return +order.order_id === +id
            ? {
                ...order,
                payment_status: status?.status_name || "",
                payment_status_slug: status?.slug || "",
              }
            : order;
        }),
      );

      const response = await updateOrderStatus({
        action: "ccb_update_order_status",
        nonce: window?.ccb_nonces?.ccb_update_order_status || "",
        order_id: id,
        status_id: status_id,
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
        });
        this.fetchOrders();
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
        });
      }

      this.requestLoader = false;
    },

    async saveTableSettings(): Promise<void> {
      this.requestLoader = true;

      const response = await saveTableSettings({
        action: "ccb_save_table_settings",
        nonce: window?.ccb_nonces?.ccb_save_table_settings || "",
        settings: this.getTableSettings,
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
        });
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
        });
      }

      this.requestLoader = false;
    },

    async saveOrdersSettings(): Promise<void> {
      this.requestLoader = true;

      const response = await saveOrdersSettings({
        action: "ccb_save_orders_settings",
        nonce: window?.ccb_nonces?.ccb_save_orders_settings || "",
        settings: this.getOrdersSettings,
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
        });
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
        });
      }

      this.requestLoader = false;
    },

    async restoreOrdersSettings(): Promise<void> {
      this.requestLoader = true;

      const response = await restoreOrdersSettings({
        action: "ccb_restore_orders_settings",
        nonce: window?.ccb_nonces?.ccb_restore_orders_settings || "",
      });

      if (response.success) {
        this.setTempOrdersSettings(response.data.settings);
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
        });
      }

      this.requestLoader = false;
    },

    async restoreTableSettings(): Promise<void> {
      this.requestLoader = true;

      const response = await restoreTablesSettings({
        action: "ccb_restore_table_settings",
        nonce: window?.ccb_nonces?.ccb_restore_table_settings || "",
      });

      if (response.success) {
        this.setTempTableSettings(response.data.settings);
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
        });
      }

      this.requestLoader = false;
    },

    async deleteOrder(id: number): Promise<void> {
      this.requestLoader = true;

      const response = await deleteOrder({
        action: "ccb_delete_order",
        nonce: window?.ccb_nonces?.ccb_delete_order || "",
        id: id,
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
        });
        this.fetchOrders();
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
        });
        this.requestLoader = false;
      }
    },

    async createOrderNote(
      order_id: number,
      content: string,
    ): Promise<IOrderNote[]> {
      const response = await createOrderNote({
        action: "ccb_create_order_note",
        nonce: window?.ccb_nonces?.ccb_create_order_note || "",
        order_id: order_id,
        content: content?.trim() || "",
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
          autoClose: 1000,
        });

        return response?.data?.notes || [];
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
          autoClose: 1000,
        });
      }

      return [];
    },

    async updateOrderNote(
      note_id: number,
      content: string,
    ): Promise<IOrderNote[]> {
      const response = await updateOrderNote({
        action: "ccb_update_order_note",
        nonce: window?.ccb_nonces?.ccb_update_order_note || "",
        note_id: note_id,
        content: content?.trim() || "",
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
          autoClose: 1000,
        });

        return response?.data?.notes || [];
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
          autoClose: 1000,
        });
      }

      return [];
    },

    async deleteOrderNote(note_id: number): Promise<IOrderNote[]> {
      const response = await deleteOrderNote({
        action: "ccb_delete_order_note",
        nonce: window?.ccb_nonces?.ccb_delete_order_note || "",
        note_id: note_id,
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
          autoClose: 1000,
        });

        return response?.data?.notes || [];
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
          autoClose: 1000,
        });
      }

      return [];
    },

    async updateOrdersStatus(
      order_ids: number[],
      status_id: number,
    ): Promise<void> {
      this.requestLoader = true;

      const response = await updateOrdersStatus({
        action: "ccb_update_orders_status",
        nonce: window?.ccb_nonces?.ccb_update_orders_status || "",
        order_ids: order_ids,
        status_id: status_id,
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
        });
        this.fetchOrders();
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
          autoClose: 1000,
        });
        this.requestLoader = false;
      }
    },

    async deleteOrders(order_ids: number[]): Promise<void> {
      this.requestLoader = true;

      const response = await deleteOrders({
        action: "ccb_delete_orders",
        nonce: window?.ccb_nonces?.ccb_delete_orders || "",
        order_ids: order_ids,
      });

      if (response.success) {
        toast(response.message, {
          type: "success",
        });
        this.fetchOrders();
      } else {
        toast(response?.message || "Something went wrong", {
          type: "error",
          autoClose: 1000,
        });
        this.requestLoader = false;
      }
    },
  },
});
