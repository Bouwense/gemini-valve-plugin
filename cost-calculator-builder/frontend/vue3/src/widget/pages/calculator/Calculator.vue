<template>
  <BoxStyles v-if="appStore.getIsLive && !pageBreakEnabled" />
  <LegacyCalculator v-if="!pageBreakEnabled || !appStore.isProActive" />
  <PageBreaker v-else-if="pageBreakEnabled && appStore.isProActive" />
</template>

<script lang="ts" setup>
import { computed } from "vue";
import LegacyCalculator from "@/widget/widgets/legacy-calculator";
import PageBreaker from "@/widget/widgets/page-breaker";
import BoxStyles from "@/widget/shared/ui/components/Box-styles/BoxStyles.vue";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";

const appStore = useAppStore();
const fieldsStore = useFieldsStore();
const pageBreakEnabled = computed(() => fieldsStore.getPageBreakEnabled);
</script>
