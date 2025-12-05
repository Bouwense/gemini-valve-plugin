<template>
  <div class="ccb-empty-orders">
    <div class="ccb-empty-orders-icon">
      <i class="ccb-icon-Box-open-2"></i>
    </div>
    <div class="ccb-empty-orders-content">
      <span class="ccb-empty-orders-label">{{ label }}</span>
      <span class="ccb-empty-orders-description">{{ description }}</span>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { toRefs } from "vue";

const props = defineProps<{
  label: string;
  description: string;
}>();

const { label, description } = toRefs(props);
</script>

<style lang="scss" scoped>
.ccb-empty-orders {
  display: flex;
  height: calc(100% - 48px);
  flex-direction: column;
  align-items: center;
  justify-content: center;
  row-gap: 20px;
}

.ccb-empty-orders-icon {
  background-color: rgba(26, 177, 99, 0.1);
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;

  i {
    font-size: 40px;
    color: #00b163;
  }
}

.ccb-empty-orders-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  row-gap: 10px;

  .ccb-empty-orders-label {
    font-size: 20px;
    font-weight: 700;
    color: #001931;
  }

  .ccb-empty-orders-description {
    font-size: 16px;
    font-weight: 500;
    color: #6b7280;
  }
}
</style>
