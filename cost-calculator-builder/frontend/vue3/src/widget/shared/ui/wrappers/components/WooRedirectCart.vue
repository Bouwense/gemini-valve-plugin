<template>
  <div class="ccb-woo-product">
    <div class="ccb-woo-product__info">
      "{{ getWooProductName }}"
      {{ translationsStore.getTranslations.hasBeenAddedCart }}
    </div>
    <Button
      type="success"
      :text="translationsStore.getTranslations.viewCart"
      :href="redirectUrl"
      :is-link="true"
      @click="() => {}"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import Button from "@/widget/shared/ui/components/Button";

const translationsStore = useTranslationsStore();
const settingsStore = useSettingsStore();

const getWooCartUrl = computed(() => {
  return settingsStore.getWooCheckoutSettings?.wooCartUrl;
});

const getWooProductName = computed(() => {
  return settingsStore.getWooCheckoutSettings?.productName;
});

const redirectUrl = computed(() => {
  return getWooCartUrl.value ?? "/cart";
});
</script>

<style scoped lang="scss">
.ccb-woo-product {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 27px;
  border: 1px solid var(--ccb-accent-color);
  background: var(--ccb-fields-bg-color-alpha);

  &__info {
    padding-right: 20px;
    font-size: 14px;
    line-height: 20px;
    word-wrap: break-word;
  }
}
</style>
