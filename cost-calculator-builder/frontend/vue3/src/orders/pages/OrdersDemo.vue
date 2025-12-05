<template>
  <div class="ccb-orders-page">
    <AdminHeader
      :logo_url="getLogoUrl"
      :version="getVersion"
      :items="getMenuItems"
      :current_page="getCurrentPage"
    />
    <div class="ccb-orders-demo-page">
      <div class="ccb-orders-demo-container">
        <div class="ccb-orders-demo-title-box">
          <span class="ccb-orders-demo-title"
            >Get to PRO to manage orders!</span
          >
          <span class="ccb-orders-demo-description"
            >View, organize, and manage customer orders effortlessly</span
          >
        </div>
        <div class="ccb-orders-demo-button-box">
          <a
            href="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=send_form"
            target="_blank"
            >Upgrade now</a
          >
        </div>
        <div class="ccb-orders-demo-image-box">
          <img :src="getImageUrl" alt="orders-demo" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { AdminHeader } from "@/common/features";
import { IAdminMenu } from "@/common/shared/types/IAdminMenu";

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

const getImageUrl = computed(() => {
  if (
    "ccb_ajax_window" in window &&
    window?.ccb_ajax_window &&
    typeof window?.ccb_ajax_window === "object" &&
    "plugin_url" in window?.ccb_ajax_window
  ) {
    return (
      window?.ccb_ajax_window?.plugin_url +
      "/frontend/dist/img/pro-features/orders-banner.png"
    );
  }

  return "";
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

.ccb-orders-demo-page {
  padding: 16px;
  width: 100%;
  height: calc(100% - 58px);

  border-radius: 8px;
  background: #eaedf5;
  box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.25);
}

.ccb-orders-demo-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  justify-content: center;
  align-items: center;
}

.ccb-orders-demo-title-box {
  display: flex;
  flex-direction: column;
  row-gap: 5px;
  margin-bottom: 16px;

  span {
    color: #001931;
    text-align: center;
    font-size: 20px;
    font-weight: 700;
    line-height: 24px;
  }

  .ccb-orders-demo-description {
    font-size: 14px;
    font-weight: 500;
    opacity: 0.5;
    line-height: 20px;
  }
}

.ccb-orders-demo-button-box {
  margin-bottom: 30px;

  a {
    text-decoration: none !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    cursor: pointer;
    padding: 0 12px;
    height: 40px;
    border-radius: 8px;
    background: #1ab163;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    line-height: 1;
    display: flex;
    justify-content: center;
    align-items: center;
  }
}

.ccb-orders-demo-image-box {
  max-width: 694px;
  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}
</style>
