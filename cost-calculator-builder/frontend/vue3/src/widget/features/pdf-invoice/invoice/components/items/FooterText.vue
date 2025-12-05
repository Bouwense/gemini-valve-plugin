<template>
  <div class="ccb-pdf-layout__item" :style="getBackgroundStyles">
    <div
      class="ccb-pdf-layout__item--wrapper additional_text_block"
      data-id="footer_text"
    >
      <span class="ccb-pdf-item-description" :style="getTextStyles.value">{{
        getDescription
      }}</span>
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
      text?: {
        data: {
          description?: { value: string };
        };
      };
    };
  };
  content: Object;
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
  }

  return styles;
});

const getTextStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value?.document?.tabs;

  if (documentTabs?.body?.data?.textColor?.value) {
    styles.color = documentTabs.body.data.textColor.value;
  }

  if (item.value?.tabs?.design?.data?.textColor?.data?.status) {
    styles.color = item.value.tabs.design.data.textColor.value;
  }

  if (item.value?.tabs?.design?.data?.textAlign?.value) {
    styles.textAlign =
      item.value.tabs.design.data.textAlign.value === "justified"
        ? "justify"
        : item.value.tabs.design.data.textAlign.value;
  }

  if (item.value?.tabs?.design?.data?.fontSize?.value) {
    styles.fontSize = `${item.value.tabs.design.data.fontSize.value}px`;
  }

  return styles;
});

const getDescription = computed(() => {
  return item.value?.tabs?.text?.data?.description?.value || "";
});
</script>
