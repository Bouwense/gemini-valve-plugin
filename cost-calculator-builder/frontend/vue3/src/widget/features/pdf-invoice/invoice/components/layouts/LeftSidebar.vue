<template>
  <div
    class="ccb-pdf-layout ccb-with-left-sidebar"
    :style="getBorderStyles(pdf)"
  >
    <div class="ccb-pdf-layout__sidebar" :style="getDocumentSidebarStyles(pdf)">
      <div class="ccb-pdf-layout__items">
        <template v-for="item in getSidebarItems">
          <component
            :is="getComponentName(item.key)"
            :item="item"
            :content="content"
            :pdf="pdf"
          ></component>
        </template>
      </div>
    </div>

    <div class="ccb-pdf-layout__content">
      <div class="ccb-pdf-layout__items">
        <template v-for="item in getBodyItems">
          <component
            :is="getComponentName(item.key)"
            :item="item"
            :content="content"
            :pdf="pdf"
          ></component>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, defineProps, computed } from "vue";
import { pdfMixin } from "@/widget/features/pdf-invoice/pdf-mixins";
import { IPdfSettings } from "@/widget/shared/types/settings";

type Props = {
  pdf: IPdfSettings;
  content: Object;
};

const props = defineProps<Props>();
const { pdf, content } = toRefs(props);

const { getBorderStyles, getDocumentSidebarStyles, getComponentName } =
  pdfMixin();

interface Section {
  isBody: boolean;
  enable: boolean;
  sortId: number;
  key: string;
}

const getSidebarItems = computed(() => {
  const sections = Object.values(pdf.value.sections) as Array<{
    isBody: boolean;
    enable: boolean;
    sortId: number;
    key: string;
  }>;
  return sections
    .filter((s) => !s.isBody && s.enable)
    .sort((a, b) => a.sortId - b.sortId);
});

const getBodyItems = computed(() => {
  const sections = Object.values(pdf.value.sections) as Section[];
  return sections
    .filter((s) => s.isBody && s.enable)
    .sort((a, b) => a.sortId - b.sortId);
});
</script>
