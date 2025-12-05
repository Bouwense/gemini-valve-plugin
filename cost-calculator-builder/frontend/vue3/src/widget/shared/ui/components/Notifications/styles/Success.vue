<template>
  <div class="calc-notice-wrapper">
    <div class="calc-notice-icon-wrapper">
      <span class="calc-notice-icon-content">
        <i class="ccb-icon-Octicons"></i>
      </span>
    </div>
    <div class="calc-notice-content">
      <span class="calc-notice-title">{{ message }}</span>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { toRefs } from "vue";

type Props = {
  message: string;
};

const props = defineProps<Props>();
const { message } = toRefs(props);
</script>

<style scoped lang="scss">
.calc-notice-wrapper {
  display: flex;
  flex-direction: row;
  align-items: center;
  column-gap: 10px;

  .calc-notice-icon-wrapper {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background-color: var(--ccb-fields-bg-color-alpha);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;

    .calc-notice-icon-content {
      width: 22px;
      height: 22px;
      background-color: var(--ccb-accent-color);
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      position: relative;
      animation: pulse 1.5s infinite;

      i {
        font-size: 7px;
        color: #ffffff;
      }
    }
  }

  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 var(--ccb-accent-color);
    }
    70% {
      box-shadow: 0 0 0 10px transparent;
    }
    100% {
      box-shadow: 0 0 0 0 transparent;
    }
  }

  .calc-notice-icon {
    width: 22px;
    height: 19px;
    display: flex;
    align-items: center;

    img {
      max-width: 22px;
    }
  }

  .calc-notice-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    line-height: 1.3;

    .calc-notice-title {
      font-size: 14px;
      font-weight: 700;
      color: var(--ccb-accent-color);
    }

    .calc-notice-description {
      font-size: 13px;
      font-weight: 500;
      color: #001931;
    }
  }
}
</style>
