import { computed, ref } from "vue";
import { defineStore } from "pinia";
import {
  NotificationsTypes,
  UpdateNotificationParams,
} from "@/widget/shared/types/settings";
import { useSettingsStore } from "./settingsStore";

export const useNotificationsStore = defineStore("notifications_store", () => {
  const settingsStore = useSettingsStore();
  const notificationStatus = ref<boolean>(false);
  const message = ref<string>("");
  const description = ref<string>("");
  const notificationType = ref<NotificationsTypes>("");

  const updateNotificationStatus = (value: boolean): void => {
    notificationStatus.value = value;
  };

  const updateMessage = (value: string): void => {
    message.value = value;
  };

  const updateNotificationType = (value: NotificationsTypes): void => {
    notificationType.value = value;
  };

  const resetNotifications = (): void => {
    updateNotifications({ status: false, message: "", type: "" });
  };

  const updateNotifications = (data: UpdateNotificationParams): void => {
    if (["success", "finish"].includes(data.type)) {
      settingsStore.setSummaryDisplayShowSummary(true);
    }

    notificationStatus.value = data.status;
    message.value = data.message;
    notificationType.value = data.type;
    description.value = data.description || "";
  };

  const getNotificationType = computed((): NotificationsTypes => {
    return notificationType.value;
  });

  return {
    message,
    description,
    notificationStatus,
    updateNotificationStatus,
    updateMessage,
    resetNotifications,
    notificationType,
    updateNotificationType,
    updateNotifications,
    getNotificationType,
  };
});
