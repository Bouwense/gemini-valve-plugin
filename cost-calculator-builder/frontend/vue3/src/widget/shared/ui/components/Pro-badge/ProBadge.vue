<template>
  <span class="is-pro" v-if="appStore.getIsLive">
    <span class="pro-tooltip">
      pro
      <span class="pro-tooltiptext"
        >Feature Available <br />
        in Pro Version</span
      >
    </span>
  </span>
</template>
<script setup lang="ts">
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";

const appStore = useAppStore();
</script>
<style lang="scss">
.is-pro {
  cursor: pointer;
  color: #0098ca;
  border-radius: 10px;
  z-index: 3;
  padding: 3px 7px;
  margin-left: 5px;
  font-size: 11px;
  text-transform: uppercase;
  background: #c5f1ff;
  font-weight: 800;
  position: relative;
  top: -2px;
  display: inline-block;
  .pro-tooltip {
    display: block;
    line-height: 1;
    overflow: visible;
  }
  .pro-tooltiptext {
    visibility: hidden;
    width: 110px;
    z-index: 10;
    font-weight: 400;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    text-align: center;
    border-radius: 6px;
    box-sizing: content-box;
    padding: 6px 9px !important;
    position: absolute;
    font-size: 12px !important;
    bottom: 130%;
    left: 50%;
    transition: visible 0.1s linear;
    margin-left: -63px !important;
    text-transform: initial;

    &:after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: rgba(0, 0, 0, 0.7) transparent transparent transparent;
    }
  }

  &:hover {
    .pro-tooltiptext {
      visibility: visible !important;
    }
  }
}
</style>
