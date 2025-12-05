<template>
  <div class="ccb-demo-page-wrapper">
    <div class="ccb-demo-page-container">
      <Demo
        title="🔒 Analytics is available in the Pro version."
        description="Upgrade now to unlock insights, track users, optimize sales, and grow your revenue."
        link="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=send_form"
        :image="bannerImageUrl"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Demo from "@/admin/shared/ui/components/Demo/index.ts";

const bannerImageUrl = computed(() => {
  if (
    "ccb_ajax_window" in window &&
    window.ccb_ajax_window &&
    typeof window.ccb_ajax_window === "object"
  ) {
    if ("plugin_url" in window.ccb_ajax_window) {
      return (
        window.ccb_ajax_window.plugin_url +
        "/frontend/dist/img/pro-features/analytics-banner.webp"
      );
    }
  }

  return "";
});
</script>

<style lang="scss">
body {
  background: #d0d5e1 !important;
}

#ccb-admin-app {
  margin-right: 20px;
}

.ccb-analytics-page {
  margin: 15px 0 0;
}

.ccb-demo-page-wrapper {
  background: #fff;
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);
  height: calc(100vh - 72px);
  margin: 20px 0;

  .ccb-demo-page-container {
    height: 100%;
    display: flex;
    flex-direction: column;
    row-gap: 50px;
    max-width: 930px;
    width: 100%;
    padding: 50px 0 0;
    margin: 0 auto;
  }
}
</style>
