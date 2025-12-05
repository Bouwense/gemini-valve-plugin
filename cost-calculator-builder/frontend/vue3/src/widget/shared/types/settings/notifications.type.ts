export type NotificationsTypes = "" | "finish" | "success" | "error";
export type UpdateNotificationParams = {
  type: NotificationsTypes;
  message: string;
  status: boolean;
  description?: string;
};
