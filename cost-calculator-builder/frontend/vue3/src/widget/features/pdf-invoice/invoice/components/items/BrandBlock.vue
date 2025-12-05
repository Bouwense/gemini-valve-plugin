<template>
  <div class="ccb-pdf-layout__item" :style="getBackgroundStyles">
    <div
      class="ccb-pdf-layout__item--wrapper brand_block"
      data-id="brand_block"
      :style="getWrapperTextAlign"
    >
      <div class="ccb-pdf-item-img" :style="getItemsTextAlign">
        <img
          :src="getCompanyLogo"
          alt="company logo"
          :style="companyLogoStyles"
          v-if="getCompanyLogo"
        />
      </div>
      <div
        class="ccb-pdf-item-text-box"
        v-if="showCompanyName || showCompanySlogan"
      >
        <span
          class="ccb-pdf-item-text-box--title"
          :style="getNameStyles"
          v-if="showCompanyName"
          >{{ getCompanyName }}</span
        >
        <span
          class="ccb-pdf-item-text-box--description"
          :style="getSloganStyles"
          v-if="showCompanySlogan"
          >{{ getCompanySlogan }}</span
        >
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, defineProps, computed } from "vue";
import { IPdfSettings } from "@/widget/shared/types/settings";
type Props = {
  pdf: IPdfSettings;
  item: {
    isBody: boolean;
    tabs: {
      design?: {
        data: {
          backgroundColor: { data: { status: boolean }; value: string };
          textAlign: { value: string };
        };
      };
      logo: {
        data: {
          logoImage: { value: string };
          logoWidthSize: { value: number };
          logoHeightSize: { value: number };
        };
      };
      name?: {
        data: {
          showCompanyName: { data: { status: boolean }; value: string };
          fontColor?: { data: { status: boolean }; value: string };
          fontSize?: { value: number };
        };
      };
      slogan?: {
        data: {
          showSlogan: { data: { status: boolean }; value: string };
          fontColor?: { data: { status: boolean }; value: string };
          fontSize?: { value: number };
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

const getWrapperTextAlign = computed(() => {
  const styles: Record<string, string> = {};
  if (item.value.tabs?.design) {
    styles.justifyContent = item.value.tabs.design.data.textAlign.value;
  }
  return styles;
});

const getItemsTextAlign = computed(() => {
  const styles: Record<string, string> = {};
  if (item.value.tabs?.design) {
    const value = item.value.tabs.design.data.textAlign.value;
    styles.textAlign = value === "justify" ? "center" : value;
  }

  return styles;
});

const getCompanyLogo = computed(() => {
  return item.value.tabs.logo.data.logoImage.value;
});

const companyLogoStyles = computed(() => {
  return {
    width: `${item.value.tabs.logo.data.logoWidthSize.value}px`,
    height: `${item.value.tabs.logo.data.logoHeightSize.value}px`,
  };
});

const showCompanyName = computed(() => {
  return item.value.tabs?.name?.data?.showCompanyName?.data?.status;
});

const showCompanySlogan = computed(() => {
  return item.value.tabs?.slogan?.data?.showSlogan?.data?.status;
});

const getNameStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body && item.value.isBody) {
    const body = documentTabs.body;
    styles.color = body.data?.textColor?.value ?? "";
  }

  if (item.value.tabs?.design) {
    styles.textAlign = item.value.tabs.design.data.textAlign.value;
  }

  if (item.value.tabs?.name?.data?.fontColor?.data.status) {
    styles.color = item.value.tabs.name.data.fontColor.value;
  }

  if (item.value.tabs?.name?.data?.fontSize?.value) {
    styles.fontSize = `${item.value.tabs.name.data.fontSize.value}px`;
  }

  return styles;
});

const getCompanyName = computed(() => {
  return item.value.tabs?.name?.data?.showCompanyName?.value;
});

const getSloganStyles = computed(() => {
  const styles: Record<string, string> = {};
  const documentTabs = pdf.value.document?.tabs;

  if (documentTabs?.body) {
    const body = documentTabs.body;
    styles.color = body.data?.textColor?.value ?? "";
  }

  if (item.value.tabs?.design) {
    styles.textAlign = item.value.tabs.design.data.textAlign.value;
  }

  if (item.value.tabs?.slogan?.data?.fontColor?.data.status) {
    styles.color = item.value.tabs.slogan.data.fontColor.value;
  }

  if (item.value.tabs?.slogan?.data?.fontSize?.value) {
    styles.fontSize = `${item.value.tabs.slogan.data.fontSize.value}px`;
  }

  return styles;
});

const getCompanySlogan = computed(() => {
  return item.value.tabs?.slogan?.data?.showSlogan?.value;
});
</script>
