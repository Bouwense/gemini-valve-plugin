<template>
  <div class="ccb-singlepage-layout">
    <ChartsSection />
    <WidgetsSection />
  </div>
</template>

<script setup lang="ts">
import ChartsSection from "@/admin/widgets/Analytics/components/ChartsSection.vue";
import WidgetsSection from "@/admin/widgets/Analytics/components/WidgetsSection.vue";
</script>

<style scoped lang="scss">
.ccb-singlepage-layout {
  display: flex;
  flex-direction: row;
  gap: 26px;
  width: 100%;
  align-items: flex-start;
}
</style>
