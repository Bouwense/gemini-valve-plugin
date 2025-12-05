<template>
  <div class="ccb-pdf-layout__item order_block" :style="getTableStyles">
    <div
      class="ccb-pdf-layout__item--wrapper order_block"
      data-id="order_block"
      :style="getBorders"
    >
      <div class="pdf-total-summary" style="padding: 20px 30px">
        <div
          class="pdf-total-summary--header"
          :style="getHeadingStyles"
          v-if="showHeading"
        >
          <span class="pdf-total-summary--header-name" style="width: 46%">{{
            getName
          }}</span>
          <span class="pdf-total-summary--header-unit" style="width: 34%">{{
            getOptionUnit
          }}</span>
          <span
            class="pdf-total-summary--header-total"
            style="width: 20%; text-align: right"
            >{{ getValue }}</span
          >
        </div>
        <div class="pdf-total-summary--body">
          <div class="pdf-total-summary--body-items">
            <div
              class="pdf-total-summary--body-item"
              v-for="detail in getOrderDetails"
              :key="detail.label"
              :style="getLines"
            >
              <span
                class="pdf-total-summary--body-item-name"
                style="width: 46%"
              >
                <span
                  class="pdf-total-summary--body-item-name-title"
                  :style="{
                    'margin-left':
                      detail.repeaterAlias !== undefined &&
                      !detail.repeaterAlias.includes('group')
                        ? '10px'
                        : '0px',
                  }"
                  >{{ detail.label }}</span
                >
                <template
                  v-if="
                    ['checkbox', 'toggle', 'checkbox_with_img'].includes(
                      detail.fieldName,
                    )
                  "
                >
                  <span
                    class="pdf-total-summary--body-item-name-extra"
                    v-if="
                      'selectedOption' in detail &&
                      Array.isArray(detail.selectedOption)
                    "
                  >
                    <span
                      class="ccb-pdf-extra-wrap"
                      v-if="
                        'selectedOption' in detail &&
                        Array.isArray(detail.selectedOption)
                      "
                      v-for="selected in detail.selectedOption"
                      :key="selected.optionText"
                      style="padding-left: 10px"
                    >
                      <template v-if="detail.summaryView === 'show_value'">
                        <span class="ccb-pdf-extra-wrap-label">{{
                          selected.optionText
                        }}</span>
                        <span
                          class="ccb-pdf-extra-wrap-value"
                          style="white-space: nowrap"
                          >{{ selected.optionConverted }}</span
                        >
                      </template>
                    </span>
                  </span>
                </template>
                <template
                  v-else-if="
                    [
                      'file_upload',
                      'range',
                      'multi_range',
                      'quantity',
                    ].includes(detail.fieldName)
                  "
                >
                  <span
                    class="pdf-total-summary--body-item-name-extra"
                    v-if="
                      'extraDisplayView' in detail && detail.extraDisplayView
                    "
                  >
                    <span class="ccb-pdf-extra-wrap" style="padding-left: 10px">
                      <span class="ccb-pdf-extra-wrap-label">{{
                        detail.extraDisplayView
                      }}</span>
                    </span>
                  </span>
                </template>
                <template
                  v-else-if="
                    ['datePicker', 'geolocation'].includes(detail.fieldName)
                  "
                >
                  <span
                    class="pdf-total-summary--body-item-name-extra"
                    v-if="
                      'extraDisplayView' in detail &&
                      Array.isArray(detail.extraDisplayView)
                    "
                  >
                    <span
                      class="ccb-pdf-extra-wrap"
                      v-if="
                        'extraDisplayView' in detail &&
                        Array.isArray(detail.extraDisplayView)
                      "
                      style="
                        padding-left: 10px;
                        display: flex;
                        flex-direction: column;
                        gap: 5px;
                      "
                    >
                      <span v-for="s in detail.extraDisplayView" :key="s">
                        <span class="ccb-pdf-extra-wrap-label">{{ s }}</span>
                      </span>
                    </span>
                  </span>
                </template>
              </span>
              <span
                class="pdf-total-summary--body-item-unit"
                style="width: 34%"
              >
                <template
                  v-if="
                    [
                      'dropDown',
                      'dropDown_with_img',
                      'radio',
                      'radio_with_img',
                    ].includes(detail.fieldName)
                  "
                >
                  <template
                    v-if="
                      'summaryView' in detail &&
                      detail.summaryView === 'show_value'
                    "
                  >
                    <span
                      class="ccb-pdf-extra-wrap-label"
                      style="font-weight: 700"
                      v-if="'selectedOption' in detail && detail.selectedOption"
                    >
                      <template v-if="'optionText' in detail.selectedOption">
                        {{ detail.selectedOption.optionText }}
                      </template>
                    </span>
                  </template>
                </template>
              </span>
              <span
                class="pdf-total-summ ary--body-item-total"
                style="width: 17%; text-align: right; font-weight: 700"
              >
                <template v-if="Array.isArray(detail.displayValue)">
                  <ul style="list-style: none">
                    <li v-for="option in detail.displayValue" :key="option">
                      {{ option }}
                    </li>
                  </ul>
                </template>
                <template v-else>
                  {{ detail.displayValue }}
                </template>
              </span>
            </div>

            <div class="pdf-total-summary--body-item-box">
              <div class="pdf-order-block-qr-code" v-if="showQrCode">
                <img :src="qrCodeDataUrl" alt="QR Code" />
              </div>
              <div class="pdf-order-block-payment-info">
                <div class="pdf-order-block-payment-info--totals">
                  <span
                    v-for="total in getTotals"
                    :key="total.label"
                    class="pdf-order-block-payment-info--totals-item"
                    style="box-sizing: border-box"
                  >
                    <template
                      v-if="
                        total.hasDiscount &&
                        total?.discount?.viewType === 'show_with_title'
                      "
                    >
                      <span style="display: flex; flex-direction: column">
                        <span
                          style="display: flex; justify-content: space-between"
                        >
                          <span style="display: flex; column-gap: 3px">
                            <span
                              class="ccb-discounts-inner-label"
                              style="font-weight: 700"
                              >Discount:
                            </span>
                            <span
                              class="ccb-discount-title"
                              style="font-weight: 700"
                              >{{ total.discount.discountName }}</span
                            >
                          </span>
                        </span>
                        <span style="padding-top: 15px">{{ total.label }}</span>
                      </span>
                    </template>
                    <template v-else>
                      <span
                        style="
                          display: flex;
                          column-gap: 4px;
                          align-items: center;
                        "
                      >
                        {{ total.label }}
                        <span
                          class="ccb-discount-off"
                          v-if="
                            total.hasDiscount &&
                            total?.discount?.viewType !== 'show_with_title'
                          "
                          >{{ getDiscountAmount(total) }}</span
                        >
                      </span>
                    </template>

                    <template
                      v-if="
                        total.hasDiscount &&
                        total?.discount?.viewType === 'show_with_title'
                      "
                    >
                      <span
                        style="
                          display: flex;
                          flex-direction: column;
                          align-items: flex-end;
                        "
                      >
                        <span>{{ getDiscountValue(total) }}</span>
                        <span style="padding-top: 15px">{{
                          total.displayValue
                        }}</span>
                      </span>
                    </template>
                    <template v-else>
                      <template
                        v-if="
                          total.hasDiscount &&
                          total?.discount?.viewType === 'show_without_title'
                        "
                      >
                        <span style="display: flex">
                          <span
                            style="
                              font-weight: 500 !important;
                              text-decoration: line-through;
                              margin-right: 4px;
                              display: inline-block;
                              font-size: 80%;
                            "
                            >{{ total.originalDisplayView }}</span
                          >
                          <span>{{ total.displayValue }}</span>
                        </span>
                      </template>
                      <span v-else>{{ total.displayValue }}</span>
                    </template>
                  </span>
                </div>
                <div
                  class="pdf-order-block-payment-info--payment"
                  :style="paymentBorderStyles"
                >
                  <span
                    class="pdf-order-block-payment-info--payment-item"
                    style="box-sizing: border-box"
                  >
                    <span v-if="showPaymentMethod && getPaymentMethodType">
                      Payment method: {{ getPaymentMethodType }}
                    </span>
                    <span v-if="showGrandTotal">{{ getGrandTotalAmount }}</span>
                  </span>
                </div>
                <div
                  class="pdf-order-block-promocodes"
                  v-if="getPromocodes && getPromocodes.length > 0"
                  style="opacity: 0.7; padding: 0 10px"
                >
                  <span>Applied promocodes: </span>
                  <span>
                    <span
                      v-for="(promocode, index) in getPromocodes"
                      :key="index"
                    >
                      {{ promocode
                      }}{{ index < getPromocodes.length - 1 ? ", " : "" }}
                    </span>
                  </span>
                </div>
              </div>
            </div>
            <div class="ccb-payment-info-images">
              <div class="ccb-payment-info-image">
                <img
                  :src="getStampImage"
                  alt="stamp-image"
                  :style="stampStyles"
                  v-if="getStampImage"
                />
              </div>
              <div class="ccb-payment-info-image">
                <img
                  :src="getSignatureImage"
                  alt="signature-image"
                  :style="signatureStyles"
                  v-if="getSignatureImage"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, defineProps, computed, ref } from "vue";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import QRCode from "qrcode";
import { IPdfSettings } from "@/widget/shared/types/settings";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
const fieldsStore = useFieldsStore();
import { Field, IFormulaField } from "@/widget/shared/types/fields";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency.ts";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";

const settingsStore = useSettingsStore();
const currencyInstance = useCurrency();

type Props = {
  pdf: IPdfSettings;
  item: {
    tabs: {
      design?: {
        data: {
          tableBackground?: { data: { status: boolean }; value: string };
          tableText?: { data: { status: boolean }; value: string };
          fontSize?: { value: number };
          headingBackground?: { data: { status: boolean }; value: string };
          headingText?: { data: { status: boolean }; value: string };
          titleFontSize?: { value: number };
        };
      };
      lines?: {
        data: {
          showBorder?: { value: boolean };
          borderBorderSize?: { value: number };
          borderColor?: { value: string };
          borderStyle?: { value: string };
          angleBorderSize?: { value: number };
          showLines?: { value: boolean };
          lineBorderSize?: { value: number };
          lineBorderStyle?: { value: string };
          lineBorderColor?: { value: string };
        };
      };
      images?: {
        data: {
          signatureImage?: { value: string };
          signatureSize?: { value: number };
          stampImage?: { value: string };
          stampSize?: { value: number };
        };
      };
      content?: {
        data: {
          showHeading?: { value: boolean };
          headingName?: { value: string };
          headingUnit?: { value: string };
          headingValue?: { value: string };
          showQrCode?: { value: boolean };
          qrCodeLink?: { value: string };
          showPaymentMethod?: { value: boolean };
          showGrandTotal?: { value: boolean };
        };
      };
    };
  };
  content: {
    paymentMethodType?: string;
    promocodes?: string[];
    total?: number;
    totals?: Array<{
      label: string;
      value: number;
      hidden?: boolean;
      hasDiscount?: boolean;
      discount?: {
        discountView?: string;
        discountTitle?: string;
        discountValue?: string;
        originalConverted?: string;
      };
    }>;
  };
};

const props = defineProps<Props>();
const { item, content } = toRefs(props);

const qrCodeDataUrl = ref("");

const generateQRCode = (link: string) => {
  QRCode.toDataURL(link).then((url: string) => {
    qrCodeDataUrl.value = url;
  });
};

const getGrandTotalAmount = computed(() => {
  let sumTotal = 0;

  fieldsStore.getTotalsList
    .filter((f) => f)
    .forEach((total) => {
      sumTotal += total?.value || 0;
    });

  const settings = currencyInstance.getCurrencyOptions();
  const currency = settings.currency;
  const position = settings.currencyPosition;

  return currencyInstance.parseCurrencyPosition(
    sumTotal.toString(),
    currency,
    position || "left",
  );
});

const getTableStyles = computed(() => {
  let styles: {
    flex: number;
    backgroundColor?: string;
    color?: string;
    fontSize?: string;
  } = { flex: 1 };

  if (item.value?.tabs?.design) {
    const design = item.value.tabs.design;

    if (design.data?.tableBackground?.data?.status) {
      const color = design.data?.tableBackground?.value;
      styles = {
        flex: 1,
        backgroundColor: color,
      };
    }
    if (design.data?.tableText?.data?.status) {
      styles.color = design.data?.tableText?.value;
    }
    if (design.data?.fontSize?.value) {
      styles.fontSize = `${design.data.fontSize.value}px`;
    }
  }

  return styles;
});

const getFieldsFromMap = computed(() => {
  return (data: Map<string, Field>): Field[] => {
    return Array.from(data.values());
  };
});

const getBorders = computed(() => {
  let styles: {
    padding: number;
    height: string;
    border?: string;
    borderRadius?: string;
  } = { padding: 0, height: "max-content" };

  if (item.value?.tabs?.lines?.data?.showBorder?.value) {
    const size = item.value?.tabs?.lines?.data?.borderBorderSize?.value;
    const color = item.value?.tabs?.lines?.data?.borderColor?.value;
    const style = item.value?.tabs?.lines?.data?.borderStyle?.value || "solid";
    const radius = item.value?.tabs?.lines?.data?.angleBorderSize?.value;
    styles.border = `${size}px ${style} ${color}`;
    styles.borderRadius = `${radius}px`;
  }

  return styles;
});

const getSignatureImage = computed(() => {
  return item.value?.tabs?.images?.data?.signatureImage?.value || "";
});

const signatureStyles = computed(() => {
  return {
    width: `${item.value?.tabs?.images?.data?.signatureSize?.value || 0}px`,
  };
});

const stampStyles = computed(() => {
  return {
    width: `${item.value?.tabs?.images?.data?.stampSize?.value || 0}px`,
  };
});

const getStampImage = computed(() => {
  return item.value?.tabs?.images?.data?.stampImage?.value || "";
});

const getHeadingStyles = computed(() => {
  let styles: { color?: string; fontSize?: string; backgroundColor?: string } =
    {};

  if (item.value?.tabs?.design) {
    const design = item.value.tabs.design;

    if (design.data?.headingBackground?.data?.status) {
      const color = design.data?.headingBackground?.value;
      styles = {
        backgroundColor: color,
      };
    }

    if (design.data?.headingText?.data?.status) {
      styles.color = design.data?.headingText?.value;
    }

    if (design.data?.titleFontSize?.value) {
      styles.fontSize = `${design.data.titleFontSize.value}px`;
    }
  }

  return styles;
});

const getPromocodes = computed(() => {
  return content.value?.promocodes || [];
});

const showHeading = computed(() => {
  return item.value?.tabs?.content?.data?.showHeading?.value || false;
});

const getName = computed(() => {
  return item.value?.tabs?.content?.data?.headingName?.value || "";
});

const getOptionUnit = computed(() => {
  return item.value?.tabs?.content?.data?.headingUnit?.value || "";
});

const getValue = computed(() => {
  return item.value?.tabs?.content?.data?.headingValue?.value || "";
});

const getOrderData = computed(() => {
  const submissionStore = useSubmissionStore();
  return submissionStore.getOrderData;
});

const getOrderDetails = computed(() => {
  let fields = fieldsStore.getSummaryList.filter((f: Field) => !f.hidden);

  if (getOrderData.value?.orderDetails) {
    fields = getOrderData.value?.orderDetails;
  }

  let result: Field[] = [];

  fields.forEach((el) => {
    if ("groupElements" in el && Array.isArray(el.groupElements)) {
      el.groupElements.forEach((inner: Map<string, Field>) => {
        result = [...result, ...(getFieldsFromMap.value(inner) as Field[])];
      });
    } else {
      result.push(el);
    }
  });

  if (!settingsStore.general?.hideEmptyForOrdersPdfEmails) {
    result = result.filter((f) => {
      if ("selectedOption" in f && Array.isArray(f.selectedOption)) {
        return f.selectedOption.length > 0;
      } else if (
        "selectedOption" in f &&
        !Array.isArray(f.selectedOption) &&
        f.selectedOption
      ) {
        return f.selectedOption.optionValue;
      } else if (["validated_form", "text"].includes(f.fieldName)) {
        return f.displayValue;
      } else if (
        f.fieldName === "geolocation" &&
        "geoType" in f &&
        f.geoType === "multiplyLocation"
      ) {
        return f.displayValue;
      } else if (
        (f.fieldName === "datePicker" && f.displayValue.length > 0) ||
        (f.fieldName === "timePicker" && f.displayValue.length > 0)
      ) {
        return f.displayValue;
      } else {
        return f.value;
      }
    });
  }
  result = result.filter((f) => f.addToSummary);
  return result;
});

const getLines = computed(() => {
  let styles = {};
  if (item.value?.tabs?.lines?.data?.showLines?.value) {
    const size = item.value?.tabs?.lines?.data?.lineBorderSize?.value;
    const style = item.value?.tabs?.lines?.data?.lineBorderStyle?.value;
    const color = item.value?.tabs?.lines?.data?.lineBorderColor?.value;
    styles = {
      borderBottom: `${size}px ${style} ${color}`,
    };
  }
  return styles;
});

const showQrCode = computed(() => {
  const status = item.value?.tabs?.content?.data?.showQrCode?.value || false;
  if (status) {
    const link =
      item.value?.tabs?.content?.data?.qrCodeLink?.value ||
      "https://google.com";
    generateQRCode(link);
  }
  return status;
});

const getTotals = computed(() => {
  const result = fieldsStore.getTotalsList as IFormulaField[];
  return result?.filter((f) => f)?.filter((f) => !f?.hidden);
});

const getDiscountAmount = computed<(total: any) => string | null>(() => {
  return (total) => {
    if (total.discount) {
      return `${total.discount.extraView}`;
    }

    return null;
  };
});

const getDiscountValue = computed<(total: any) => string | null>(() => {
  return (total) => {
    if (total.discount) {
      return total.discount.extraView || null;
    }
    return null;
  };
});

const showPaymentMethod = computed(() => {
  return item.value?.tabs?.content?.data?.showPaymentMethod?.value || false;
});

const showGrandTotal = computed(() => {
  return item.value?.tabs?.content?.data?.showGrandTotal?.value || false;
});

const paymentBorderStyles = computed(() => {
  let styles = {};
  if (!showPaymentMethod.value && !showGrandTotal.value) return styles;
  if (!getTotals.value?.length) return styles;

  if (item.value?.tabs?.lines?.data?.showLines?.value) {
    const size = item.value?.tabs?.lines?.data?.lineBorderSize?.value;
    const style = item.value?.tabs?.lines?.data?.lineBorderStyle?.value;
    const color = item.value?.tabs?.lines?.data?.lineBorderColor?.value;
    styles = {
      borderTop: `${size}px ${style} ${color}`,
    };
  }
  return styles;
});

const getPaymentMethodType = computed(() => {
  return content.value?.paymentMethodType || "";
});
</script>
