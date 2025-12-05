<template>
  <div class="ccb-pdf-layout__item" :style="getBackgroundStyles">
    <div
      class="ccb-pdf-layout__item--wrapper additional_text_block"
      data-id="additional_text_block"
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
          backgroundColor: { data: { status: boolean }; value: string };
          titleColor: { data: { status: boolean }; value: string };
          textColor: { data: { status: boolean }; value: string };
          textAlign: { value: string };
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
    isBody: boolean;
  };
};

const props = defineProps<Props>();
const { pdf, item } = toRefs(props);

const getBackgroundStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body) {
    const body = documentTabs.body;
    styles.backgroundColor = body.data?.backgroundColor?.value ?? "";
  }

  if (item.value?.tabs?.design) {
    const design = item.value.tabs.design;
    if (design.data.backgroundColor.data.status) {
      styles.backgroundColor = design.data.backgroundColor.value;
    } else {
      styles.backgroundColor = "transparent";
    }
  }

  return styles;
});

const getTitleStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body) {
    const body = documentTabs.body;
    styles.color = body.data?.titleColor?.value ?? "";
  }

  if (item.value.tabs?.design) {
    const design = item.value.tabs.design;
    if (design.data.titleColor.data.status) {
      styles.color = design.data.titleColor.value;
    }

    if (design.data.textAlign.value) {
      styles.textAlign =
        design.data.textAlign.value === "justified"
          ? "justify"
          : design.data.textAlign.value;
    }

    if (design.data.titleFontSize?.value) {
      styles.fontSize = `${design.data.titleFontSize.value}px`;
    }
  }

  return styles;
});

const getTitle = computed(() => {
  return item.value.tabs.text.data.title.value;
});

const getTextStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body && item.value.isBody) {
    const body = documentTabs.body;
    styles.color = body.data?.textColor?.value ?? "";
  }

  if (item.value.tabs?.design) {
    const design = item.value.tabs.design;
    if (design.data.textColor.data.status) {
      styles.color = design.data.textColor.value;
    }

    if (design.data.textAlign.value) {
      styles.textAlign =
        design.data.textAlign.value === "justified"
          ? "justify"
          : design.data.textAlign.value;
    }

    if (design.data.fontSize?.value) {
      styles.fontSize = `${design.data.fontSize.value}px`;
    }
  }

  return styles;
});

const getDescription = computed(() => {
  return item.value.tabs.text.data.description.value;
});
</script>
