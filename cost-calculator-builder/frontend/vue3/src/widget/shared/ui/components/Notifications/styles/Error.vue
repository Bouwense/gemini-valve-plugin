<template>
  <div class="calc-notice-wrapper">
    <div class="calc-notice-icon">
      <span class="calc-notice-icon">
        <i class="ccb-icon-warning"></i>
      </span>
    </div>
    <div class="calc-notice-content">
      <span class="calc-notice-title">{{ message }}</span>
      <span
        class="calc-notice-description"
        v-if="description"
        v-html="description"
      ></span>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { toRefs } from "vue";

type Props = {
  message: string;
  description?: string;
};

const props = defineProps<Props>();
const { message, description } = toRefs(props);
</script>

<style scoped lang="scss">
.calc-notice-wrapper {
  display: flex;
  flex-direction: row;
  align-items: center;
  column-gap: 10px;

  .calc-notice-icon {
    display: flex;
    align-items: center;

    i {
      color: #d94141;
      font-size: 22px;
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
      color: #d94141;
    }

    .calc-notice-description {
      font-size: 13px;
      font-weight: 500;
      color: #001931;
    }
  }
}
</style>
