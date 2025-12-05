<template>
  <div class="ccb-pdf-layout ccb-no-sidebar" :style="getBorderStyles(pdf)">
    <div class="ccb-pdf-layout__content">
      <div class="ccb-pdf-layout__items">
        <template v-for="item in getAllItems">
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

const { getBorderStyles, getComponentName } = pdfMixin();

const getAllItems = computed(() => {
  const sections = Object.values(pdf.value?.sections ?? {}) as Array<{
    enable: boolean;
    sortId: number;
    key: string;
  }>;
  return sections.filter((s) => s.enable).sort((a, b) => a.sortId - b.sortId);
});
</script>
