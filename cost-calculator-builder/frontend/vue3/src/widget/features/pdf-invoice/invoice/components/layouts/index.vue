<template>
  <div class="ccb-pdf-layout-wrapper" :style="getDocumentStyles">
    <component :is="getComponent" :pdf="pdf" :content="content"></component>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, defineAsyncComponent } from "vue";
import { IPdfSettings } from "@/widget/shared/types/settings";

type Props = {
  pdf: IPdfSettings;
  content: Object;
};

const props = defineProps<Props>();
const { pdf, content } = toRefs(props);

const getDocumentStyles = computed(() => {
  const styles = {
    width: "100%",
    color: "",
    backgroundColor: "",
    backgroundImage: "",
  };
  const documentTabs = pdf.value?.document?.tabs;

  if (documentTabs?.body) {
    const body = documentTabs.body;
    styles.color = body.data?.textColor?.value ?? "";
    styles.backgroundColor = body.data?.backgroundColor?.value ?? "";
    styles.backgroundImage = body.data?.backgroundImage?.value
      ? `url('${body.data.backgroundImage.value}')`
      : "";
  }
  return styles;
});

const getComponentStyle = computed(() => {
  const layout = pdf.value?.document?.tabs?.sidebar?.data?.layout?.value;
  const store = {
    no_sidebar: "layout-no-sidebar",
    left_sidebar: "layout-with-left-sidebar",
    right_sidebar: "layout-with-right-sidebar",
  } as const;

  return store[layout as keyof typeof store];
});

const getComponent = computed(() => {
  const style = getComponentStyle.value;

  if (style === "layout-no-sidebar") {
    return defineAsyncComponent(() => import("./LayoutNoSidebar.vue"));
  } else if (style === "layout-with-left-sidebar") {
    return defineAsyncComponent(() => import("./LeftSidebar.vue"));
  } else if (style === "layout-with-right-sidebar") {
    return defineAsyncComponent(() => import("./RightSidebar.vue"));
  }

  return "";
});
</script>

<style></style>
