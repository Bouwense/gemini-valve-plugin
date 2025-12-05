<template>
  <div class="order-details-emails">
    <div class="order-details-emails-header">
      <span>Emailed to</span>
    </div>
    <div class="order-details-emails-list">
      <div class="order-details-emails-list-item" v-for="email in emails">
        <span>{{ email }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, PropType } from "vue";

const props = defineProps({
  emails: {
    type: Object as PropType<any>,
    required: true,
  },
});

const { emails } = toRefs(props);
</script>

<style scoped lang="scss">
.order-details-emails {
  display: flex;
  flex-direction: column;
  row-gap: 12px;

  padding: 16px;
  border-radius: 12px;
  background: rgba(22, 36, 50, 0.03);

  &-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    span {
      color: #162432;
      font-size: 13px;
      font-weight: 700;
      line-height: 1;
    }
  }

  &-list {
    display: flex;
    flex-direction: column;
    row-gap: 12px;

    &-item {
      display: flex;
      column-gap: 8px;

      span {
        color: rgba(22, 36, 50, 0.85);
        font-size: 13px;
        font-weight: 500;
        line-height: 1;
      }

      button {
        border: none;
        cursor: pointer;
        background: transparent;
      }
    }
  }
}
</style>
