<template>
  <div>{{ translations.calculatorList }}</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useAdminTranslationsStore } from "@/analytics/store/analytics/translationsStore";

const translationsStore = useAdminTranslationsStore();

const translations = computed(() => translationsStore.getTranslations);
</script>
