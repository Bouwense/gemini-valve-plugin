<template>
  <div class="ccb-notifications" :class="type">
    <component
      :is="getCurrentComponents"
      :message="message"
      :description="description"
    />
  </div>
</template>

<script lang="ts" setup>
import { computed, defineAsyncComponent, toRefs, h } from "vue";
import { NotificationsTypes } from "@/widget/shared/types/settings";

type Props = {
  type: NotificationsTypes;
  message: string;
  description?: string;
};

const props = defineProps<Props>();
const { type, message, description } = toRefs(props);

const getCurrentComponents = computed(() => {
  if (type.value !== "error") {
    return defineAsyncComponent(
      () =>
        import(
          "@/widget/shared/ui/components/Notifications/styles/Success.vue"
        ),
    );
  } else if (type.value === "error") {
    return defineAsyncComponent(
      () =>
        import("@/widget/shared/ui/components/Notifications/styles/Error.vue"),
    );
  }

  return h("div");
});
</script>

<style scoped lang="scss">
.ccb-notifications {
  padding: 20px;
  border-radius: 8px;
  margin-top: 10px;
  box-shadow: 0 0 0 #cccccc !important;
  &.finish,
  &.success {
    background-color: var(--ccb-orders-notice-color);
  }
}
</style>
