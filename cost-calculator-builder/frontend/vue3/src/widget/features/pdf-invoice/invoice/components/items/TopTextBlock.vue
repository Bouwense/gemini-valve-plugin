<template>
  <div class="ccb-pdf-layout__item" :style="getBackgroundStyles">
    <div
      class="ccb-pdf-layout__item--wrapper top_text_block"
      data-id="top_text_block"
    >
      <span class="ccb-pdf-item-title" :style="getTitleStyles">{{
        getTitle
      }}</span>
      <span class="ccb-pdf-item-description" :style="getTextStyles">{{
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
          titleColor?: { data: { status: boolean }; value: string };
          textColor?: { data: { status: boolean }; value: string };
          textAlign?: { value: string };
          titleFontSize?: { value: number };
          fontSize?: { value: number };
        };
      };
      text: {
        data: {
          title: { value: string };
          description: { value: string };
        };
      };
    };
  };
};

const props = defineProps<Props>();
const { pdf, item } = toRefs(props);

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

const getTitleStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body?.data?.titleColor?.value) {
    styles.color = documentTabs.body.data.titleColor.value;
  }

  if (item.value.tabs?.design?.data?.titleColor?.data?.status) {
    styles.color = item.value.tabs.design.data.titleColor.value;
  }

  if (item.value.tabs?.design?.data?.textAlign?.value) {
    styles.textAlign =
      item.value.tabs.design.data.textAlign.value === "justified"
        ? "justify"
        : item.value.tabs.design.data.textAlign.value;
  }

  if (item.value.tabs?.design?.data?.titleFontSize?.value) {
    styles.fontSize = `${item.value.tabs.design.data.titleFontSize.value}px`;
  }

  return styles;
});

const getTitle = computed(() => {
  return item.value.tabs.text.data.title.value;
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

const getDescription = computed(() => {
  return item.value.tabs.text.data.description.value;
});
</script>
