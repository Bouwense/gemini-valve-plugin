<template>
  <div class="ccb-demo-page-info-block">
    <div class="ccb-demo-page-info-block-content">
      <span class="ccb-demo-page-info-block-content--title">{{ title }} </span>
      <span class="ccb-demo-page-info-block-content--description">
        {{ description }}
      </span>
    </div>
    <div class="ccb-demo-page-info-block-actions">
      <a
        :href="link"
        target="_blank"
        class="ccb-demo-page-info-block-actions-button"
      >
        <span>{{ translations.upgradeNowBtn }}</span>
      </a>
    </div>
  </div>

  <div class="ccb-demo-page-banner">
    <div class="ccb-demo-page-banner-content">
      <img :src="image" alt="analytics-banner" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, toRefs } from "vue";
import { useAdminTranslationsStore } from "@/analytics/store/analytics/translationsStore";

const translationsStore = useAdminTranslationsStore();

const translations = computed(() => translationsStore.getTranslations);

const props = defineProps<{
  title: string;
  description: string;
  link: string;
  image: string;
}>();

const { title, description, link, image } = toRefs(props);
</script>

<style lang="scss" scoped>
.ccb-demo-page-info-block {
  display: flex;
  width: 100%;
  padding: 20px 16px;
  border-radius: 12px;
  background: rgba(26, 177, 99, 0.15);
  justify-content: space-between;
  align-items: center;

  &-content {
    display: flex;
    flex-direction: column;
    row-gap: 10px;

    &--title {
      font-size: 18px;
      font-weight: 700;
      color: #131925;
    }

    &--description {
      font-size: 16px;
      font-weight: 400;
      color: #1f2533;
    }
  }

  &-actions {
    &-button {
      text-decoration: none;
      cursor: pointer;
      display: flex;
      padding: 0 20px;
      justify-content: center;
      align-items: center;
      border-radius: 8px;
      background: #1ab163;
      color: #fff;
      height: 40px;
      border: none !important;
      box-shadow: none !important;
      outline: none !important;

      transition: background 0.3s ease;

      &:hover {
        background: #138043;
      }
    }
  }
}

.ccb-demo-page-banner {
  &-content {
    max-width: 100%;
    overflow: hidden;

    img {
      max-width: 100%;
      height: auto;
      object-fit: cover;
      border-radius: 12px;
    }
  }
}
</style>
