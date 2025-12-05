<template>
  <span class="ccb-demo-notice" v-if="appStore.getIsLive">
    Sorry, this site is only for demo purposes.
  </span>
</template>
<script setup lang="ts">
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";

const appStore = useAppStore();
</script>
<style lang="scss">
.ccb-demo-notice {
  background-color: #d94141;
  border-radius: 4px;
  color: #fff;
  cursor: pointer;
  font-size: 14px;
  padding: 4px 20px;
  text-align: center;
  position: relative;
  grid-column: span 12;
  margin-bottom: 10px;
}
.ccb-contact-form7 ~ .ccb-demo-notice {
  margin-top: 20px;
  display: block;
}
</style>
