<template>
  <div class="ccb-orders-page">
    <AdminHeader
      :logo_url="getLogoUrl"
      :version="getVersion"
      :items="getMenuItems"
      :current_page="getCurrentPage"
    />
    <OrdersTable />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from "vue";
import { AdminHeader } from "@/common/features";
import { IAdminMenu } from "@/common/shared/types/IAdminMenu";
import { OrdersTable } from "@/orders/widgets/Orders";
import { useOrdersStore } from "@/orders/app/providers/stores/orders";

const getLogoUrl = computed(() => {
  if (
    "ccb_ajax_window" in window &&
    window?.ccb_ajax_window &&
    typeof window?.ccb_ajax_window === "object" &&
    "plugin_url" in window?.ccb_ajax_window
  ) {
    return window?.ccb_ajax_window?.plugin_url + "/frontend/dist/img/calc.svg";
  }

  return "";
});

const getVersion = computed(() => {
  if (
    "ccb_ajax_window" in window &&
    window?.ccb_ajax_window &&
    typeof window?.ccb_ajax_window === "object" &&
    "plugin_version" in window?.ccb_ajax_window &&
    typeof window?.ccb_ajax_window?.plugin_version === "string"
  ) {
    return window?.ccb_ajax_window?.plugin_version || "";
  }

  return "";
});

const getMenuItems = computed(() => {
  if (
    "ccb_ajax_window" in window &&
    window?.ccb_ajax_window &&
    typeof window?.ccb_ajax_window === "object" &&
    "menu_items" in window?.ccb_ajax_window
  ) {
    return Object.values(
      window?.ccb_ajax_window?.menu_items || {},
    ) as IAdminMenu[];
  }

  return [] as IAdminMenu[];
});

const getCurrentPage = computed(() => {
  if (
    "ccb_ajax_window" in window &&
    window?.ccb_ajax_window &&
    typeof window?.ccb_ajax_window === "object" &&
    "current_page" in window?.ccb_ajax_window
  ) {
    return (window?.ccb_ajax_window?.current_page || "") as string;
  }

  return "";
});

onMounted(() => {
  const ordersStore = useOrdersStore();
  ordersStore.fetchOrders();
});
</script>

<style scoped lang="scss">
.ccb-orders-page {
  margin: 15px 0 0;
  display: flex;
  flex-direction: column;
  row-gap: 15px;
  height: 100%;
}
</style>
