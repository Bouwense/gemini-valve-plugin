<template>
  <div class="ccb-header-title">
    {{ title }}
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";

type Props = {
  title: string | undefined;
};

const props = defineProps<Props>();
const { title } = toRefs(props);
</script>

<style lang="scss">
.ccb-header-title {
  font-size: var(--ccb-header-size);
  font-weight: var(--ccb-header-weight);
  color: var(--ccb-text-color);
  text-align: left;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-header-size);
    font-weight: var(--ccb-mobile-header-weight);
  }
}
</style>
