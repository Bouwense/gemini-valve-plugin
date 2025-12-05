<template>
  <component :is="currentComponents">
    <slot></slot>
  </component>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';

type Props = {
  listType: 'total' | 'summary';
};

const props = defineProps<Props>();

const currentComponents = computed(() => {
  if (props.listType === 'total') {
    return defineAsyncComponent(() => import('./lists/TotalsList.vue'));
  } else if (props.listType === 'summary') {
    return defineAsyncComponent(() => import('./lists/SummaryList.vue'));
  }

  return '';
});
</script>
