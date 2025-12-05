<template>
  <div class="ccb-demo-box-styles">
    <div class="ccb-box-styles">
      <div
        class="ccb-box-style-inner"
        :class="{ 'ccb-style-active': currentStyle === 'two_columns' }"
        @click="changeStyle('two_columns')"
      >
        <i class="ccb-icon-Union-27"></i>
        <span>Two columns</span>
      </div>
      <div
        class="ccb-box-style-inner"
        :class="{ 'ccb-style-active': currentStyle === 'vertical' }"
        @click="changeStyle('vertical')"
      >
        <i class="ccb-icon-Union-26"></i>
        <span>Vertical</span>
      </div>
      <div
        class="ccb-box-style-inner"
        :class="{ 'ccb-style-active': currentStyle === 'horizontal' }"
        @click="changeStyle('horizontal')"
      >
        <i class="ccb-icon-Union-25"></i>
        <span>Horizontal</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";

const appearanceStore = useAppearanceStore();

const currentStyle = computed(() => appearanceStore.getAppearanceBoxStyle);

const changeStyle = (style: string) => {
  appearanceStore.initAppearanceStore({
    boxStyle: style,
    descriptionPosition: appearanceStore.descriptionPosition,
    loaderType: appearanceStore.loaderType,
    accentColor: appearanceStore.accentColor,
    svgColor: appearanceStore.svgColor,
  });
};
</script>

<style lang="scss">
.ccb-sticky-modal {
  .ccb-demo-box-styles {
    display: none !important;
  }
}
.ccb-demo-box-styles {
  width: 100%;
  top: 0;
  position: relative;
  .ccb-box-styles {
    background-color: #d2d8e5;
    border-radius: 10px;
    box-sizing: border-box;
    display: flex;
    margin: 30px auto;
    padding: 3px;
    width: fit-content;

    .ccb-box-style-inner {
      align-items: center;
      border-radius: 8px;
      column-gap: 10px;
      cursor: pointer;
      display: flex;
      height: 44px;
      justify-content: center;
      min-width: 178px;
      padding: 0 20px;
      & > span {
        color: #001931;
        font-size: 14px;
        font-weight: 500;
      }

      &.ccb-style-active {
        background-color: #fff;
        box-shadow: 0 3px 3px 0 #0000001a;
        i {
          color: #1ab163;
          opacity: 1;
        }
      }

      i {
        color: #001931;
        font-size: 19px;
        opacity: 0.7;
      }
    }
  }
}
</style>
