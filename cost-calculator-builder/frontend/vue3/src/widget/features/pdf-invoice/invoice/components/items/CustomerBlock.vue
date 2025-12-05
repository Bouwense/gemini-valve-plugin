<template>
  <div
    class="ccb-pdf-layout__item"
    :style="getBackgroundStyles"
    v-if="getOrderId"
  >
    <div
      class="ccb-pdf-layout__item--wrapper customer_block"
      data-id="customer_block"
    >
      <span class="ccb-pdf-item-title" :style="getTitleStyles">{{
        getTitle
      }}</span>
      <span
        class="ccb-pdf-item-info"
        style="flex-direction: column !important; margin-top: 0"
      >
        <span
          class="ccb-pdf-item-info--item"
          v-for="detail in getCustomerDetails"
          :key="detail.key"
          :class="[detail.key]"
        >
          <span class="ccb-pdf-item-info--item-label" :style="getTextStyles">{{
            detail.label
          }}</span>
          <span
            class="ccb-pdf-item-info--item-value"
            :style="getTextStyles"
            v-if="detail.type === 'formatted-text'"
            v-html="detail.value"
          ></span>
          <span
            class="ccb-pdf-item-info--item-value"
            :style="getTextStyles"
            v-if="detail.type !== 'formatted-text'"
            >{{ detail.value }}</span
          >
        </span>
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, defineProps, computed } from "vue";
import { IPdfSettings } from "@/widget/shared/types/settings";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";
import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore.ts";

type Props = {
  pdf: IPdfSettings;
  item: {
    tabs?: {
      design?: {
        data: {
          backgroundColor?: { data: { status: boolean }; value: string };
          textColor?: { data: { status: boolean }; value: string };
          titleColor?: { data: { status: boolean }; value: string };
          titleFontSize?: { value: number };
        };
      };
      text?: {
        data: {
          title?: { value: string };
        };
      };
      content?: {
        data: {
          title?: { value: string };
        };
      };
    };
  };
  content: {
    customerDetails?: Array<{
      value: string;
      name: string;
      key: string;
      attributes?: {
        name?: string;
      };
    }>;
  };
};

const props = defineProps<Props>();
const { pdf, item } = toRefs(props);

const getBackgroundStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value?.document?.tabs;

  if (documentTabs?.body?.data?.backgroundColor?.value) {
    styles.backgroundColor = documentTabs.body.data.backgroundColor.value;
  }

  if (item.value?.tabs?.design?.data?.backgroundColor?.data?.status) {
    styles.backgroundColor = item.value.tabs.design.data.backgroundColor.value;
  } else {
    styles.backgroundColor = "transparent";
  }

  return styles;
});

const getOrderId = computed(() => {
  const submissionStore = useSubmissionStore();
  return submissionStore.getOrderId;
});

const getOrderData = computed(() => {
  const submissionStore = useSubmissionStore();
  return submissionStore.getOrderData;
});

const getTitle = computed(() => {
  return item.value?.tabs?.text?.data?.title?.value || "";
});

const getTitleStyles = computed(() => {
  const styles: Record<string, string> = {};
  if (item.value?.tabs?.design?.data?.titleColor?.data?.status) {
    styles.color = item.value.tabs.design.data.titleColor.value;
  }
  if (item.value?.tabs?.design?.data?.titleFontSize?.value) {
    styles.fontSize = `${item.value.tabs.design.data.titleFontSize.value}px`;
  }
  return styles;
});

const getTextStyles = computed(() => {
  const styles: Record<string, string> = {};
  if (item.value?.tabs?.design?.data?.textColor?.data?.status) {
    styles.color = item.value.tabs.design.data.textColor.value;
  }
  return styles;
});

const upperFirst = (str: string) => {
  if (!str && typeof str !== "string") return str;
  return str.charAt(0).toUpperCase() + str.slice(1);
};

const getCustomerDetails = computed(() => {
  const orderFormStore = useOrderFormStore();
  let formFields = orderFormStore.getFormFields;

  if (getOrderData.value?.formDetails?.fields) {
    formFields = getOrderData.value?.formDetails?.fields;
  }

  let data = [];
  if (formFields) {
    for (const detail of formFields) {
      const item = {
        value: Array.isArray(detail.value)
          ? detail.value.join(", ")
          : detail.value,
        key: detail.id,
        label: upperFirst(detail.label),
        type: detail.type,
      };

      if (item.value) {
        data.push(item);
      }
    }
  }

  return data;
});
</script>
