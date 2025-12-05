import { defineStore } from "pinia";
import { usePaymentStore } from "@/widget/app/providers/stores/paymentsStore";
import {
  OrdersResponse,
  useOrder,
} from "@/widget/actions/pro-features/orders/composable/useOrder";
import {
  PaymentMethods,
  IOrderDateThankYouPage,
} from "@/widget/shared/types/orders";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";
import { useNotificationsStore } from "@/widget/app/providers/stores/notificationsStore";
import { usePaymentAfterSubmitStore } from "./paymentAfterSubmit";
import { useAppStore } from "@/widget/app/providers/stores/appStore";
import { ContactFormFields } from "@/widget/shared/types/orders/contact-form-fields.type";
import { useOrderFormStore } from "./orderFormStore";

type SubmissionState = {
  orderId: number | undefined;
  sendPaymentType: string;
  orderData: IOrderDateThankYouPage | undefined;
};

export const useSubmissionStore = () => {
  const store = defineStore("submission_store", {
    state: (): SubmissionState => ({
      orderId: undefined,
      orderData: undefined,
      sendPaymentType: "",
    }),

    actions: {
      reset(): void {
        this.sendPaymentType = "";
        this.orderId = undefined;
      },

      setSendPaymentType(type: string): void {
        this.sendPaymentType = type;
      },

      setOrderId(id: number | undefined): void {
        this.orderId = id;
      },

      setOrderData(data: any): void {
        this.orderData = data;
      },

      resetSubmissions() {
        const paymentStore = usePaymentStore();
        const notifications = useNotificationsStore();

        this.reset();
        paymentStore.reset();
        notifications.resetNotifications();
        this.orderId = undefined;
      },

      submissionCreateOrder(orderInputs?: ContactFormFields[]): void {
        const notificationsStore = useNotificationsStore();
        const settingsStore = useSettingsStore();
        const paymentStore = usePaymentStore();
        const paymentAfterSubmitStore = usePaymentAfterSubmitStore();

        if (paymentAfterSubmitStore.getSubmit) {
          paymentAfterSubmitStore.setSubmit(false);
        }

        if (
          settingsStore.formFields?.summaryDisplay?.enable &&
          ["show_summary_block", "show_summary_block_with_pdf"].includes(
            settingsStore.formFields?.summaryDisplay?.actionAfterSubmit,
          )
        ) {
          if (!settingsStore.getSummaryDisplayShowSummary) {
            settingsStore.setSummaryDisplayShowSummary(true);

            if (
              settingsStore.formFields?.summaryDisplay?.actionAfterSubmit ===
                "show_summary_block_with_pdf" ||
              settingsStore.formFields?.summaryDisplay?.actionAfterSubmit ===
                "show_summary_block"
            ) {
              paymentAfterSubmitStore.setSubmit(true);
            }

            return;
          }
        }

        const {
          createOrder,
          completeOrder,
          makePayment,
          razorpayPaymentReceived,
        } = useOrder();

        notificationsStore.resetNotifications();

        const pmRecord: Record<string, PaymentMethods> = {
          cash: "cash_payment",
        };

        const appStore = useAppStore();

        if (!orderInputs?.length) {
          appStore.setSubmissionLoader(true);
        }

        const paymentType = paymentStore.paymentType as unknown as string;
        const type: string = paymentType ? paymentType : "no_payments";

        if (type === "stripe") {
          if (
            paymentStore?.stripeInstance &&
            "createPaymentMethod" in paymentStore.stripeInstance &&
            typeof paymentStore.stripeInstance.createPaymentMethod ===
              "function"
          ) {
            const orderFormStore = useOrderFormStore();
            const formFields = orderFormStore.getFormFields;
            const billingDetails: Record<string, string> = {};
            for (const field of formFields) {
              if (field.type === "email") billingDetails["email"] = field.value;
              if (field.type === "name") billingDetails["name"] = field.value;
              if (field.type === "phone") billingDetails["phone"] = field.value;
            }

            paymentStore.stripeInstance
              .createPaymentMethod({
                card: paymentStore.cardComponent,
                type: "card",
                billing_details: billingDetails,
              })
              .then(async (cardResult: any): Promise<void> => {
                if (
                  cardResult.error !== undefined &&
                  cardResult.error.message !== undefined
                ) {
                  appStore.setSubmissionLoader(false);
                  notificationsStore.updateNotifications({
                    status: true,
                    type: "error",
                    message: cardResult.error.message,
                  });
                } else {
                  const paymentId: string = cardResult.paymentMethod.id || "";
                  paymentStore.setPaymentMethodId(paymentId);
                  createOrder("stripe").then(
                    (response: OrdersResponse | void): void => {
                      let stripeClientSecret: string = "";
                      if (response?.success) {
                        if (
                          response?.requiresAction &&
                          paymentStore?.stripeInstance
                        ) {
                          paymentStore.stripeInstance
                            .handleCardAction(settingsStore.getStripeSecretKey)
                            .then(
                              async (
                                card_action_result: any,
                              ): Promise<void> => {
                                if (card_action_result.error) {
                                  appStore.setSubmissionLoader(false);
                                  notificationsStore.updateNotifications({
                                    status: true,
                                    type: "error",
                                    message: "Your card was not authenticated!",
                                  });
                                } else if (
                                  card_action_result.paymentIntent.status ===
                                  "requires_confirmation"
                                ) {
                                  paymentStore.setPaymentMethodId("");
                                  paymentStore.setPaymentIntentId(
                                    response.paymentIntentId || "",
                                  );

                                  makePayment(
                                    "stripe",
                                    Number(response.orderId || 0),
                                  ).then((intent: OrdersResponse) => {
                                    if (intent.success) {
                                      stripeClientSecret =
                                        intent.clientSecret || "";
                                    } else {
                                      appStore.setSubmissionLoader(false);
                                      notificationsStore.updateNotifications({
                                        status: true,
                                        type: intent.success
                                          ? "finish"
                                          : "error",
                                        message: intent.message,
                                      });
                                    }
                                  });
                                }
                              },
                            );
                        } else {
                          completeOrder(response.orderId, "complete");
                          stripeClientSecret = response.clientSecret || "";
                        }
                      } else {
                        appStore.setSubmissionLoader(false);
                        notificationsStore.updateNotifications({
                          status: true,
                          type: "error",
                          message: response?.message || "Something went wrong",
                        });
                      }

                      if (stripeClientSecret && paymentStore?.stripeInstance) {
                        paymentStore.stripeInstance
                          .retrievePaymentIntent(stripeClientSecret)
                          .then((): void => {
                            appStore.setSubmissionLoader(false);
                            notificationsStore.updateNotifications({
                              status: true,
                              type: "finish",
                              message: "Your order has been placed",
                            });
                          });
                      }
                    },
                  );
                }
              })
              .catch((err: any) => {
                appStore.setSubmissionLoader(false);
                notificationsStore.updateNotifications({
                  status: true,
                  type: "error",
                  message: err.message,
                });
              });
          }
        } else if (type === "razorpay") {
          createOrder("razorpay").then((response: OrdersResponse | void) => {
            if (response?.success && "data" in response) {
              const data: any = response.data;
              data.key = settingsStore.getRazorpayKeyId;
              data.handler = (res: any): void => {
                if (res) {
                  notificationsStore.updateNotifications({
                    status: true,
                    type: "finish",
                    message: "Your order has been placed",
                  });

                  const orderId = data?.notes?.ccb_order_id;
                  razorpayPaymentReceived(
                    orderId,
                    settingsStore.getRazorpayPaymentCurrency,
                  );
                }
              };

              if (
                "Razorpay" in window &&
                typeof window.Razorpay === "function"
              ) {
                const RazorpayConstructor = window.Razorpay as unknown as new (
                  options: any,
                ) => any;
                const rzp = new RazorpayConstructor({
                  ...data,
                  modal: {
                    ondismiss() {
                      appStore.setSubmissionLoader(false);
                      notificationsStore.updateNotifications({
                        type: "error",
                        message: "Payment cancelled",
                        status: true,
                      });
                    },
                  },
                });

                rzp?.on("payment.failed", (res: any): void => {
                  appStore.setSubmissionLoader(false);
                  notificationsStore.updateNotifications({
                    type: "error",
                    message:
                      (res?.error?.reason as string) || "Something went wrong",
                    status: true,
                  });
                });
                rzp?.open();
              }
            } else {
              notificationsStore.updateNotifications({
                status: true,
                type: "error",
                message: response?.message || "Something went wrong",
              });
            }
          });
        } else {
          createOrder(pmRecord[type] || type, orderInputs).then(
            (res: OrdersResponse | void) => {
              if (res) {
                appStore.setSubmissionLoader(false);
                if (!paymentAfterSubmitStore.getSubmit) {
                  const notificationType =
                    type === "woocommerce" ? "success" : "finish";

                  if (
                    res.success &&
                    settingsStore.formFields?.summaryDisplay?.enable &&
                    [
                      "show_summary_block",
                      "show_summary_block_with_pdf",
                    ].includes(
                      settingsStore.formFields?.summaryDisplay
                        ?.actionAfterSubmit,
                    )
                  ) {
                    appStore.updateThankYouPageStatus(true);
                  }

                  notificationsStore.updateNotifications({
                    type: res?.success ? notificationType : "error",
                    message: res.message
                      ? res.message
                      : res?.data?.message || "",
                    status: true,
                  });
                }
              }
            },
          );
        }
      },
    },

    getters: {
      getOrderId: (state: SubmissionState): number | undefined => state.orderId,
      getOrderData: (state: SubmissionState): any | undefined =>
        state.orderData,
      getSendPaymentType: (state: SubmissionState): string =>
        state.sendPaymentType,
    },
  });

  return store();
};
