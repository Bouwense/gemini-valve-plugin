<template>
  <div
    class="ccb-pdf-layout__item"
    :style="{
      ...getImageHeight,
      backgroundImage: getImageUrl ? `url(${getImageUrl})` : 'none',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      backgroundRepeat: 'no-repeat',
    }"
  >
    <div
      class="ccb-pdf-layout__item--wrapper image_block"
      style="height: 100%"
      data-id="image_block"
    ></div>
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
      default: {
        data: {
          backgroundImage: {
            key: string;
            label: string;
            type: string;
            value: string;
          };
          imageHeight?: {
            extra: string;
            key: string;
            label: string;
            type: string;
            value: string;
          };
        };
      };
    };
  };
};

const props = defineProps<Props>();
const { item } = toRefs(props);

const getImageUrl = computed(() => {
  return item.value.tabs?.default.data.backgroundImage?.value;
});

const getImageHeight = computed(() => {
  const height = item.value.tabs?.default.data.imageHeight?.value;

  return {
    minHeight: `${height}px`,
    height: `${height}px`,
    width: "100%",
  };
});
</script>
