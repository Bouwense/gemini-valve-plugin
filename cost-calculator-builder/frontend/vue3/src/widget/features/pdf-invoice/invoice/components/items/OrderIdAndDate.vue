<template>
  <div class="ccb-pdf-layout__item" :style="getBackgroundStyles">
    <div
      class="ccb-pdf-layout__item--wrapper order_id_and_date"
      data-id="order_id_and_date"
    >
      <span class="ccb-pdf-item-info" :style="getTextAlignStyles">
        <span
          class="ccb-pdf-item-info--item id"
          v-if="showOrderId && getOrderId"
        >
          <span class="ccb-pdf-item-info--item-label" :style="getTextStyles">{{
            orderIdLabel
          }}</span>
          <span class="ccb-pdf-item-info--item-value" :style="getTextStyles">{{
            getOrderId
          }}</span>
        </span>
        <span class="ccb-pdf-item-info--item date" v-if="showOrderDate">
          <span class="ccb-pdf-item-info--item-label" :style="getTextStyles">{{
            orderDateLabel
          }}</span>
          <span class="ccb-pdf-item-info--item-value" :style="getTextStyles"
            >{{ formatDate }} {{ formatTime }}</span
          >
        </span>
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, defineProps, computed } from "vue";
import { IPdfSettings } from "@/widget/shared/types/settings";
type Props = {
  pdf: IPdfSettings;
  item: {
    tabs: {
      design?: {
        data: {
          backgroundColor?: { data: { status: boolean }; value: string };
          textColor?: { data: { status: boolean }; value: string };
          textAlign?: { value: string };
          fontSize?: { value: number };
        };
      };
      id?: {
        data: {
          showOrderId?: { data: { status: boolean }; value: string };
        };
      };
      date?: {
        data: {
          showDate?: { data: { status: boolean }; value: string };
          format?: {
            data: {
              dateFormat?: { value: string };
              timeFormat?: { value: string };
            };
          };
        };
      };
    };
  };
  content: {
    id?: string;
    date?: string;
  };
};

const props = defineProps<Props>();
const { pdf, item, content } = toRefs(props);

const getBackgroundStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body?.data?.backgroundColor?.value) {
    styles.backgroundColor = documentTabs.body.data.backgroundColor.value;
  }

  if (item.value.tabs?.design?.data?.backgroundColor?.data?.status) {
    styles.backgroundColor = item.value.tabs.design.data.backgroundColor.value;
  } else {
    styles.backgroundColor = "transparent";
  }

  return styles;
});

const getTextStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body?.data?.textColor?.value) {
    styles.color = documentTabs.body.data.textColor.value;
  }

  if (item.value.tabs?.design?.data?.textColor?.data?.status) {
    styles.color = item.value.tabs.design.data.textColor.value;
  }

  if (item.value.tabs?.design?.data?.textAlign?.value) {
    styles.textAlign =
      item.value.tabs.design.data.textAlign.value === "justified"
        ? "justify"
        : item.value.tabs.design.data.textAlign.value;
  }

  if (item.value.tabs?.design?.data?.fontSize?.value) {
    styles.fontSize = `${item.value.tabs.design.data.fontSize.value}px`;
  }

  return styles;
});

const getTextAlignStyles = computed(() => {
  const options = {
    left: "start",
    right: "end",
    center: "center",
    justify: "start",
  } as const;

  const textAlign = getTextStyles.value.textAlign || "left";
  return { justifyContent: options[textAlign as keyof typeof options] };
});

const showOrderId = computed(() => {
  return item.value?.tabs?.id?.data?.showOrderId?.data?.status;
});

const getOrderId = computed(() => {
  return content.value?.id || "";
});

const orderIdLabel = computed(() => {
  return item.value?.tabs?.id?.data?.showOrderId?.value;
});

const showOrderDate = computed(() => {
  return item.value?.tabs?.date?.data?.showDate?.data?.status;
});

const orderDateLabel = computed(() => {
  return item.value?.tabs?.date?.data?.showDate?.value;
});

const formatDate = computed(() => {
  const format =
    item.value.tabs?.date?.data?.format?.data?.dateFormat?.value ||
    "dd/mm/yyyy";
  let date = new Date();
  if (content.value?.date) {
    date = new Date(content.value.date);
  }
  const dd = String(date.getDate()).padStart(2, "0");
  const mm = String(date.getMonth() + 1).padStart(2, "0");
  const yyyy = String(date.getFullYear());

  return format.replace("dd", dd).replace("mm", mm).replace("yyyy", yyyy);
});

const formatTime = computed(() => {
  const format =
    item.value.tabs?.date?.data?.format?.data?.timeFormat?.value || "hh:mm:ss";
  let date = new Date();
  if (content.value?.date) {
    date = new Date(content.value.date);
  }

  const hh = String(date.getHours()).padStart(2, "0");
  const min = String(date.getMinutes()).padStart(2, "0");
  const ss = String(date.getSeconds()).padStart(2, "0");

  return format.replace("hh", hh).replace("mm", min).replace("ss", ss);
});
</script>
