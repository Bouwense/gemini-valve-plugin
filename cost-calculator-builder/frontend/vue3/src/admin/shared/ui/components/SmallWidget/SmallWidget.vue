<template>
  <div class="ccb-dashboard-card card-wrapper">
    <Skeleton v-if="analyticsStore.isLoading" />
    <div
      class="ccb-dashboard-card-wrapper"
      :class="{ 'is-loading': analyticsStore.isLoading }"
    >
      <div class="ccb-dashboard-content">
        <div class="ccb-dashboard-content-header">
          <div class="ccb-dashboard-title">
            {{ cart.title }}
            <span
              class="ccb-dashboard-currency"
              v-if="currency && cart.key === 'revenue'"
            >
              ({{ currency }})
            </span>
          </div>
          <div class="ccb-dashboard-sub">
            <WidgetSettings
              :items="items"
              @update="updateWidgetSettings"
              :current="cart.key"
            />
          </div>
        </div>
        <div class="ccb-dashboard-content-footer">
          <div class="ccb-dashboard-value">
            {{ formatValue(cart.value) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { useAnalyticsStore } from "@/admin/store/analytics/useAnalyticsStore";
import { WidgetSettings } from "@/admin/shared/ui/components/WidgetSettings";
import { Skeleton } from "@/admin/shared/ui/components/Skeleton";
import { IAnalyticsCarts } from "@/admin/shared/types/analytics/analyticsCarts.type";

const analyticsStore = useAnalyticsStore();

const emit = defineEmits<{
  (e: "change", action: string, key: string): void;
}>();

const props = defineProps<{
  cart: IAnalyticsCarts;
  items: { value: string; label: string }[];
  currency?: string;
}>();

const { cart, items } = toRefs(props);

const updateWidgetSettings = (action: string, key: string) => {
  emit("change", action, key);
};

const formatValue = computed(() => {
  return (number: string) => {
    const num = parseFloat(number);
    if (num > 1000) {
      return (num / 1000).toFixed(1) + "k";
    }
    return num;
  };
});
</script>

<style lang="scss" scoped>
.ccb-dashboard-card {
  min-width: 46%;
  max-width: 50%;
  min-height: 80px !important;
  height: auto;
}

.ccb-dashboard-content {
  justify-content: space-between;
}

.ccb-dashboard-value {
  font-size: 18px !important;
}

.ccb-dashboard-title {
  width: 80%;
}
</style>
