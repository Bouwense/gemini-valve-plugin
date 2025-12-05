<template>
  <div class="order-details-attachments">
    <div class="order-details-attachments-header">
      <span>Attachments</span>
      <button @click="emit('downloadAll')">Download All</button>
    </div>
    <div class="order-details-attachments-list">
      <div
        class="order-details-attachments-list-item"
        v-for="attachment in attachments"
      >
        <span>{{ toShort(attachment.name) }}</span>
        <a
          :href="attachment.url"
          class="ccb-download-btn"
          :download="attachment.name"
        >
          <i class="ccb-icon-Path-34581"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";

type Props = {
  attachments: {
    name: string;
    url: string;
  }[];
};

const props = defineProps<Props>();
const { attachments } = toRefs(props);

const emit = defineEmits<{
  (e: "downloadAll"): void;
}>();

const toShort = computed(() => {
  return (text: string) => {
    return text.length > 38 ? text.substring(0, 35) + "..." : text;
  };
});
</script>

<style scoped lang="scss">
.order-details-attachments {
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

    button {
      border: none;
      cursor: pointer;
      padding: 6px 8px;
      border-radius: 8px;
      background: rgba(22, 36, 50, 0.05);
      color: rgba(22, 36, 50, 0.85);
      font-size: 12px;
      font-weight: 500;
      line-height: 1;
      transition: 0.2s linear;

      &:hover {
        background: rgba(22, 36, 50, 0.1);
      }
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

      .ccb-download-btn {
        outline: none;
        box-shadow: none;
        cursor: pointer;
        text-decoration: none;
        color: #1ab163;
        line-height: 1;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: 0.2s linear;

        i {
          color: #1ab163;
        }
      }

      &:hover {
        .ccb-download-btn {
          opacity: 1;
          visibility: visible;
          pointer-events: auto;
        }
      }
    }
  }
}
</style>
