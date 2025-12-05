import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import { useDiscountsStore } from "@/widget/app/providers/stores/discountsStore.ts";
import {
  ICompleteOrderData,
  IFileData,
  IOrderData,
  IOrderDetails,
  IOrderDetailsHelperResult,
  IOrderDetailsType,
  ISubmitsRequestParams,
  PaymentMethods,
} from "@/widget/shared/types/orders";
import { handleSubmissionRequest } from "@/widget/shared/api/handleOrderSubmission.ts";
import {
  Field,
  IFileUploadField,
  IOptions,
  IRepeaterField,
  IGroupField,
} from "@/widget/shared/types/fields";
import { usePaymentStore } from "@/widget/app/providers/stores/paymentsStore.ts";
import { usePaymentAfterSubmitStore } from "@/widget/app/providers/stores/paymentAfterSubmit.ts";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { ContactFormFields } from "@/widget/shared/types/orders/contact-form-fields.type";
import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { getNonce } from "@/widget/shared/utils/nonce.utils";

type OrdersResponseData = {
  success: boolean;
  message: string;
  clientSecret?: string;
  requiresAction?: boolean;
  paymentIntentId?: string;
  orderId?: number;
  page?: string;
  url?: string;
  data?: {
    orderId: number;
    message: string;
  };
};

export type OrdersResponse = OrdersResponseData;

interface IOrderResult {
  makePayment: (
    type: PaymentMethods,
    orderId: number,
  ) => Promise<OrdersResponse>;
  createOrder: (
    type: PaymentMethods,
    orderInputs?: ContactFormFields[],
  ) => Promise<OrdersResponse | void>;
  completeOrder: (orderId: number | undefined, status: string) => Promise<void>;
  razorpayPaymentReceived: (
    orderId: number | undefined,
    currency: string,
  ) => Promise<void>;
}

const prepareOrderData = (
  type: PaymentMethods,
  orderInputs?: ContactFormFields[],
): IOrderData => {
  const appStore = useAppStore();
  const paymentStore = usePaymentStore();
  const discountsStore = useDiscountsStore();
  const orderFormStore = useOrderFormStore();

  const legacyFormFields = [];
  if (orderInputs?.length) {
    for (const orderInput of orderInputs) {
      legacyFormFields.push({
        ...orderInput,
      });
    }
  } else {
    for (const formField of orderFormStore.getFormFields) {
      if (formField.type !== "space") {
        legacyFormFields.push({
          ...formField,
          attributes: {
            ...formField.attributes,
            label: formField.label,
          },
        });
      }
    }
  }

  const data: IOrderDetails = getOrderDetails();

  const orderDetails: IOrderDetailsType = data.fields;
  const hiddenOrderDetails: IOrderDetailsType = data.hiddenFields;
  const paymentMethodId: string =
    (paymentStore.paymentMethodId as unknown as string) || "";
  const paymentIntentId: string =
    (paymentStore.paymentIntentId as unknown as string) || "";

  let descriptions: Field[] = [];

  for (const field of getClearFields()) {
    if (field.fieldName === "repeater" && "groupElements" in field) {
      const groupElements: Field[] = [];
      field.groupElements.forEach((element) => {
        if ("entries" in element && typeof element.entries === "function") {
          for (const [_, existingField] of element.entries()) {
            groupElements.push(existingField as Field);
          }
        }
      });

      descriptions.push({
        ...field,
        // @ts-ignore
        groupElements,
      });
    } else if (field.fieldName === "group" && "groupElements" in field) {
      const groupElements: Field[] = [];
      field.groupElements.forEach((element) => {
        if ("entries" in element && typeof element.entries === "function") {
          for (const [_, existingField] of element.entries()) {
            groupElements.push(existingField as Field);
          }
        }
      });

      descriptions = [...descriptions, ...groupElements];
    } else {
      descriptions.push(field);
    }
  }

  return {
    orderDetails,
    status: "pending",
    hiddenOrderDetails,
    paymentMethod: type,
    descriptions: descriptions,
    calcName: appStore.getCalcTitle,
    id: Number(appStore.getCalcId || 0),
    promocodes: discountsStore.getUsedPromocodes,
    formDetails: {
      fields: legacyFormFields,
    },
    ...(paymentMethodId ? { paymentMethodId: paymentMethodId || "" } : {}),
    ...(paymentIntentId ? { paymentIntentId: paymentIntentId || "" } : {}),
  };
};

const getUsedFiles = (): IFileData[] => {
  const fieldStore = useFieldsStore();

  let pureFields: IFileUploadField[] = fieldStore.getFields.filter(
    (f) => f.fieldName === "file_upload",
  ) as IFileUploadField[];

  let repeaterFields: IRepeaterField[] = fieldStore.getFields.filter(
    (f) => f.fieldName === "repeater",
  ) as IRepeaterField[];

  repeaterFields.forEach((repeater: IRepeaterField) => {
    let aliasCounter: Record<string, number> = {};

    const elements: IFileUploadField[] = [];
    repeater.groupElements.forEach((element) => {
      for (const [_, existingField] of element.entries()) {
        if (existingField.fieldName === "file_upload") {
          elements.push(existingField as IFileUploadField);
        }
      }
    });

    const fileElements = elements.map((f: IFileUploadField) => {
      if (!aliasCounter[f.alias]) {
        aliasCounter[f.alias] = 0;

        const uniqueAlias = `${f.alias}_${aliasCounter[f.alias]}`;
        aliasCounter[f.alias]++;
        if (!f.options) f.options = { value: undefined };

        if (window.ccb_rep_files)
          f.options.value = window.ccb_rep_files[uniqueAlias];

        f.customAlias = uniqueAlias;
        f.inRepeater = true;
      }
      return f;
    });

    pureFields.push(...fileElements);
  });

  const result: IFileData[] = [];

  for (const field of pureFields) {
    if (field.alias.includes("file_upload")) {
      result.push({
        alias: field.customAlias || field.alias,
        files: (field.inRepeater ? field.options?.value : field.files) || [],
        inRepeater: field.inRepeater || false,
      });
    }
  }

  return result;
};

const getClearFields = (): Field[] => {
  const fieldStore = useFieldsStore();
  const fields = fieldStore.getFields;
  const result: Field[] = [];

  for (const field of fields) {
    if (
      field.fieldName === "group" &&
      "groupElements" in field &&
      !field.hidden
    ) {
      const groupField = field as IGroupField;
      groupField.groupElements.forEach((groupFieldMap: Map<string, Field>) => {
        Array.from(groupFieldMap.values()).forEach((groupInnerField: Field) => {
          if (
            groupInnerField.alias &&
            !["total", "html", "line", "page_break"].includes(
              groupInnerField.fieldName,
            ) &&
            (groupInnerField.fieldName === "repeater" ||
              groupInnerField.addToSummary === true ||
              groupInnerField.addToSummary === false ||
              groupInnerField.alias.indexOf("file") !== -1) &&
            (!groupInnerField.hidden || groupInnerField.calculateHidden) &&
            hideEmptyFields(groupInnerField)
          ) {
            result.push(groupInnerField);
          }
        });
      });
      continue;
    }

    if (
      field.alias &&
      !["total", "html", "line", "page_break"].includes(field.fieldName) &&
      (field.fieldName === "repeater" ||
        field.addToSummary === true ||
        field.addToSummary === false ||
        field.alias.indexOf("file") !== -1) &&
      (!field.hidden || field.calculateHidden) &&
      hideEmptyFields(field)
    ) {
      result.push(field);
    }
  }

  return result;
};

const getOrderDetails = (): IOrderDetails => {
  const filteredFields: Field[] = getClearFields();

  const includeFieldsAliases: string[] = filteredFields.map(
    (f: Field) => f.alias,
  );

  const excludedFields = filteredFields.filter(
    (f: Field) => f.alias && !includeFieldsAliases.includes(f.alias),
  );

  return {
    fields: orderDetailsHelper(filteredFields),
    hiddenFields: orderDetailsHelper(excludedFields),
  };
};

const orderDetailsHelper = (fields: Field[]): IOrderDetailsType => {
  const data: IOrderDetailsType = [];

  for (const field of fields) {
    if (field.fieldName === "repeater") {
      const elements: Field[] = [];
      (field as IRepeaterField).groupElements.forEach((element) => {
        for (const [_, existingField] of element.entries()) {
          elements.push({ ...existingField } as Field);
        }
      });

      data.push({
        alias: field.alias,
        groupTitle: field.label,
        groupElements: orderDetailsHelper(elements),
        length: (field as IRepeaterField).groupElements?.length,
        value: field.displayValue,
      });
    } else if (field.fieldName === "group") {
      (field as IGroupField).groupElements.forEach((element) => {
        for (const [_, existingField] of element.entries()) {
          const item: IOrderDetailsHelperResult = {
            ...existingField,
            title: existingField.label || "",
            originalValue: existingField.value || 0,
            value: existingField.displayValue?.toString() || "",
            options:
              "options" in existingField
                ? (existingField.options as IOptions[]).map(
                    (option: IOptions) => ({
                      label: option.optionText || "",
                      value: option.optionConverted || "",
                      converted: option.optionConverted || "",
                    }),
                  )
                : [],
          };

          if ("options" in item && Array.isArray(item.options)) {
            if (
              "selectedOption" in item &&
              Array.isArray(item.selectedOption)
            ) {
              item.options = item.selectedOption.map((option: IOptions) => ({
                label: option.optionText,
                value: option.optionConverted,
                converted: option.optionConverted,
                optionValue: option.optionValue,
              }));
            }
          }

          data.push(item);
        }
      });
    } else {
      const item: IOrderDetailsHelperResult = {
        alias: field.alias,
        title: field.label,
        value: field.displayValue,
        addToSummary: field.addToSummary,
        originalValue: field.value || 0,
        ...(typeof field.repeaterIdx !== "undefined"
          ? {
              idx: field.repeaterIdx,
            }
          : {}),
        ...(typeof field.repeaterAlias !== "undefined"
          ? {
              repeaterAlias: field.repeaterAlias,
            }
          : {}),
        ...(field.fieldName === "geolocation" && "geoType" in field
          ? {
              geoType: field.geoType,
            }
          : {}),
      };

      if ("selectedOption" in field && Array.isArray(field.selectedOption)) {
        item.options = field.selectedOption.map((o: IOptions) => ({
          label: o.optionText,
          value: o.optionConverted,
          converted: o.optionConverted,
          optionValue: o.optionValue,
        }));
      }

      if (
        "selectedOption" in field &&
        field.selectedOption &&
        !Array.isArray(field.selectedOption)
      ) {
        item.options = [
          {
            label: field.selectedOption.optionText,
            value: field.selectedOption.optionConverted,
            converted: field.selectedOption.optionConverted,
            optionValue: field.selectedOption.optionValue,
          },
        ];
      }

      if (
        field.alias.includes("file_upload") &&
        !field.useCurrency &&
        "extraDisplayView" in field
      ) {
        item.value = field.displayValue || "";
      } else if ("options" in field && Array.isArray(field.options)) {
        if ("selectedOption" in field && Array.isArray(field.selectedOption)) {
          item.options = field.selectedOption.map((option: IOptions) => ({
            label: option.optionText,
            value: option.optionConverted,
            converted: option.optionConverted,
            optionValue: option.optionValue,
          }));
        }
      }

      if ("summaryView" in field && field.summaryView) {
        item.summary_value = field.displayValue || "";
        item.summary_view = field.summaryView;
        if ("extraDisplayView" in field) {
          item.extraView = field.extraDisplayView;
        }
      }

      if ("extraDisplayView" in field && field.extraDisplayView) {
        item.option_unit = field.extraDisplayView.toString();
      }

      if (field.fieldName !== "line") {
        data.push(item);
      }
    }
  }

  return data;
};

const makePayment = async (
  type: PaymentMethods,
  orderId: number | undefined,
): Promise<OrdersResponse> => {
  const submissionStore = useSubmissionStore();
  const paymentStore = usePaymentStore();
  const data: IOrderData = prepareOrderData(type);
  data.orderId = orderId;

  const createOrderParams: ISubmitsRequestParams = {
    data,
    nonce: getNonce("ccb_payment"),
    action: "ccb_payment",
    files: getUsedFiles(),
  };

  const response: OrdersResponse =
    await handleSubmissionRequest<OrdersResponse>(createOrderParams);

  if (response.success) {
    if (type === "paypal") {
      window.location.href = response.url || "";
    }
  }

  paymentStore.updatePaymentType("");
  submissionStore.setOrderId(response.orderId);
  return response;
};

const makeWooCommercePayment = async (
  type: PaymentMethods,
  orderId: number | undefined,
): Promise<OrdersResponse> => {
  const submissionStore = useSubmissionStore();
  const paymentStore = usePaymentStore();
  const data: IOrderData = prepareOrderData(type);
  data.orderId = orderId;

  const createOrderParams: ISubmitsRequestParams = {
    data,
    nonce: getNonce("ccb_woocommerce_payment"),
    action: "ccb_woocommerce_payment",
    files: getUsedFiles(),
  };

  const response: OrdersResponse =
    await handleSubmissionRequest<OrdersResponse>(createOrderParams);

  paymentStore.updatePaymentType("");
  submissionStore.setOrderId(response.orderId);
  const settingsStore = useSettingsStore();

  if (settingsStore.getWooCheckoutSettings?.redirectTo !== "stay") {
    window.location.href = response.page || "";
    return {
      success: true,
      message: "Redirecting...",
    };
  } else if (settingsStore.getWooCheckoutSettings?.redirectTo === "stay") {
    return {
      success: true,
      message: "Stay on page",
    };
  }

  return response;
};

const createOrder = async (
  type: PaymentMethods,
  orderInputs?: ContactFormFields[],
): Promise<OrdersResponse> => {
  const settingsStore = useSettingsStore();
  const submissionStore = useSubmissionStore();
  const paymentAfterSubmitStore = usePaymentAfterSubmitStore();
  const data: IOrderData = prepareOrderData(type, orderInputs);

  const orderFormStore = useOrderFormStore();
  if (type === "no_payments" && settingsStore.getRecaptchaSettings?.enable) {
    const token = orderFormStore.getCaptchaToken;
    if (!token) {
      return {
        success: false,
        message: "Captcha token is required",
      };
    }

    data.captcha = {
      token,
      captchaSend: true,
    };

    orderFormStore.setCaptchaToken("");
  }

  if (type === "woocommerce") {
    if (!settingsStore.getWooCheckoutSettings?.productId) {
      return {
        success: false,
        message: "Invalid Product Id",
      };
    }

    const productId = settingsStore.getWooCheckoutSettings?.productId;
    const currentProductId =
      settingsStore.getWooProductsSettings?.currentProductId;
    data.product_id =
      productId === "current_product" ? currentProductId : +productId;

    if (settingsStore.getWooProductsSettings?.isVariable) {
      const attributes = settingsStore.getWooProductsSettings?.attributeKeys;
      if (attributes?.length && attributes.length > 0) {
        if (!data.product_attributes) {
          data.product_attributes = {};
        }

        for (const attribute of attributes) {
          const attr: HTMLInputElement | null = document.querySelector(
            `[name^="${attribute}"]`,
          );

          if (attr) {
            data.product_attributes[attribute] = attr.value;
          }
        }
      }
    }
  }

  if (
    paymentAfterSubmitStore.isPaymentAfterSubmit &&
    paymentAfterSubmitStore.getLastOrderId &&
    type &&
    type !== "no_payments"
  ) {
    submissionStore.setSendPaymentType(type);

    if (type === "woocommerce") {
      return await makeWooCommercePayment(
        type,
        paymentAfterSubmitStore.getLastOrderId,
      );
    }

    return await makePayment(type, paymentAfterSubmitStore.getLastOrderId);
  } else {
    data.isPaymentAfterSubmit = paymentAfterSubmitStore.isPaymentAfterSubmit;
    data.orderId = paymentAfterSubmitStore.getLastOrderId;

    const createOrderParams: ISubmitsRequestParams = {
      data,
      nonce: getNonce("ccb_add_order"),
      action: "create_cc_order",
      files: getUsedFiles(),
    };

    const response: OrdersResponse =
      await handleSubmissionRequest<OrdersResponse>(createOrderParams);

    if (!response.success) {
      return response;
    }

    if (response.success) {
      if (type === "paypal") {
        window.location.href = response.url || "";
      }
    }

    if (
      paymentAfterSubmitStore.isPaymentAfterSubmit &&
      type === "no_payments" &&
      response.data
    ) {
      paymentAfterSubmitStore.setLastOrderId(response.data.orderId);
      paymentAfterSubmitStore.setSubmit(true);
    } else if (
      paymentAfterSubmitStore.isPaymentAfterSubmit &&
      type &&
      type !== "no_payments"
    ) {
      const paymentStore = usePaymentStore();
      paymentAfterSubmitStore.setSubmit(false);
      paymentStore.updatePaymentType("");
    } else if (
      type === "woocommerce" &&
      settingsStore.getWooCheckoutSettings?.redirectTo !== "stay"
    ) {
      window.location.href = response.page || "";
      return {
        success: true,
        message: "Redirecting...",
      };
    } else if (
      type === "woocommerce" &&
      settingsStore.getWooCheckoutSettings?.redirectTo === "stay"
    ) {
      return {
        success: true,
        message: "Stay on page",
      };
    }

    submissionStore.setSendPaymentType(type);
    submissionStore.setOrderId(
      response.orderId ? response.orderId : response.data?.orderId,
    );

    return response;
  }
};

const completeOrder = async (
  orderId: number | undefined,
  status: string,
): Promise<void> => {
  if (orderId) {
    const submissionStore = useSubmissionStore();
    const data: ICompleteOrderData = {
      orderId,
      status: status,
    };

    submissionStore.setOrderId(orderId);
    const createOrderParams: ISubmitsRequestParams = {
      data,
      nonce: getNonce("ccb_update_order"),
      action: "update_order_status",
    };

    await handleSubmissionRequest(createOrderParams);
  }
};

const razorpayPaymentReceived = async (
  orderId: number | undefined,
  currency: string,
): Promise<void> => {
  const data: IOrderData = prepareOrderData("razorpay");
  data.orderId = orderId;
  data.paymentCurrency = currency;

  const createOrderParams: ISubmitsRequestParams = {
    data,
    nonce: getNonce("ccb_razorpay_receive"),
    action: "ccb_razorpay_payment_received",
  };

  await handleSubmissionRequest(createOrderParams);
};

const hideEmptyFields = (existingField: Field): string | boolean => {
  const settings = useSettingsStore();

  if (!settings.general?.hideEmptyForOrdersPdfEmails) {
    if (
      "selectedOption" in existingField &&
      Array.isArray(existingField.selectedOption)
    ) {
      return (existingField.selectedOption.length > 0) as boolean;
    } else if (
      "selectedOption" in existingField &&
      !Array.isArray(existingField.selectedOption) &&
      existingField.selectedOption
    ) {
      return (existingField.selectedOption.optionValue || "") as string;
    } else if (["validated_form", "text"].includes(existingField.fieldName)) {
      return (existingField.displayValue || "") as string;
    } else if (
      existingField.fieldName === "geolocation" &&
      "geoType" in existingField &&
      existingField.geoType === "multiplyLocation"
    ) {
      return (existingField.displayValue || "") as string;
    } else if (
      (existingField.fieldName === "datePicker" &&
        existingField.displayValue.length > 0) ||
      (existingField.fieldName === "timePicker" &&
        existingField.displayValue.length > 0)
    ) {
      return (existingField.displayValue || "") as string;
    } else {
      return (existingField.value || "") as string;
    }
  }

  return true;
};

export function useOrder(): IOrderResult {
  return {
    createOrder,
    completeOrder,
    makePayment,
    razorpayPaymentReceived,
  };
}
