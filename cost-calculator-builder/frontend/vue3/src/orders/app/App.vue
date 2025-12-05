<template>
  <component :is="getCurrentComponent" />
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, inject } from "vue";

const demo = inject("demo") || false;

const getCurrentComponent = computed(() => {
  if (demo) {
    return defineAsyncComponent(() => import("@/orders/pages/OrdersDemo.vue"));
  }

  return defineAsyncComponent(() => import("@/orders/pages/Orders.vue"));
});
</script>

<style lang="scss">
#calculator_orders {
  margin-right: 20px;
  height: calc(100vh - 60px);
  margin-bottom: 12px;
}

#wpfooter {
  display: none !important;
}

#wpbody-content,
#wpcontent {
  padding-bottom: 0 !important;
}

.ccb-custom-scrollbar {
  /* Scrollbar Styling */
  &::-webkit-scrollbar {
    width: 4px;
    height: 4px;
  }

  &.ccb-scroll-hide {
    &::-webkit-scrollbar {
      display: none;
    }
  }

  &::-webkit-scrollbar-track {
    transition: 300ms ease;
    background-color: transparent;
    -webkit-border-radius: 4px;
    border-radius: 4px;
  }

  &::-webkit-scrollbar-thumb {
    transition: 300ms ease;
    -webkit-border-radius: 4px;
    border-radius: 4px;
    background: transparent;
  }

  &:hover {
    &::-webkit-scrollbar {
      background-color: #ebebeb;
    }

    &::-webkit-scrollbar-thumb {
      -webkit-border-radius: 4px;
      border-radius: 4px;
      background: #808c97;
    }
  }

  &.large {
    &::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }

    &::-webkit-scrollbar-track {
      -webkit-border-radius: 6px;
      border-radius: 6px;
    }

    &::-webkit-scrollbar-thumb {
      -webkit-border-radius: 6px;
      border-radius: 6px;
    }

    &:hover {
      &::-webkit-scrollbar-thumb {
        -webkit-border-radius: 6px;
        border-radius: 6px;
      }
    }
  }
}
</style>
