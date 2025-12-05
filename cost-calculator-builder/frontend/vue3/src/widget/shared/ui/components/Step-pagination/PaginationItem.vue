<template>
  <div class="ccb-pagination" :class="pageBreakerSettings?.paginationType">
    <component
      :is="currentComponents"
      :pages="getPages"
      :step="activeStepIndex"
      :hideTitle="hideTitle"
      :calcTitle="calcTitle"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from "vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";

const settingsStore = useSettingsStore();
const pageBreakerSettings = settingsStore.getPageBreakerSettings;
const appStore = useAppStore();
const fieldsStore = useFieldsStore();

const activeStepIndex = computed(() => {
  return fieldsStore.getActivePageIndex + 1;
});

const currentComponents = computed(() => {
  if (pageBreakerSettings?.paginationType === "circle_with_line") {
    return defineAsyncComponent(() => import("./CircleWithLine.vue"));
  } else if (
    pageBreakerSettings?.paginationType === "rectangle_steps_with_line"
  ) {
    return defineAsyncComponent(() => import("./RectangleWithLine.vue"));
  } else if (pageBreakerSettings?.paginationType === "circle_tabs") {
    return defineAsyncComponent(() => import("./CircleTabs.vue"));
  } else if (pageBreakerSettings?.paginationType === "rectangle_tabs") {
    return defineAsyncComponent(() => import("./RectangleTabs.vue"));
  } else if (pageBreakerSettings?.paginationType === "progress_with_circle") {
    return defineAsyncComponent(() => import("./ProgressWithCircle.vue"));
  } else if (pageBreakerSettings?.paginationType === "progress_with_bar") {
    return defineAsyncComponent(() => import("./ProgressWithBar.vue"));
  }
});

const hideTitle = computed(() => {
  return !!pageBreakerSettings?.hidePaginationTitle;
});

const calcTitle = computed(() => {
  return appStore.getCalcTitle;
});

const getPages = computed(() => {
  const pages = fieldsStore.getPages;
  let allPages = pages.map((page, index) => {
    return {
      id: index + 1,
      title: page.label,
      label: page.label,
    };
  });

  if (pageBreakerSettings?.summaryAfterLastPage) {
    allPages.push({
      id: allPages.length + 1,
      title: "Summary",
      label: "Summary",
    });
  }

  return allPages;
});
</script>

<style lang="scss">
.ccb-pagination {
  position: relative;
  overflow-x: auto;
  max-width: 470px;
  .ccb-calc-title {
    position: absolute;
    color: #001931;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
    grid-column: 1 / -1;
  }

  .ccb-rectangle-with-line,
  .ccb-circle-with-line {
    &.hide-title {
      .tab__title {
        display: none;
      }
    }

    .tab {
      margin: 0 10px;
      display: flex;
      align-items: center;

      &.done {
        .tab__item {
          background-color: var(--ccb-accent-color);
          color: var(--ccb-container-color);
        }

        .complete {
          display: flex;
        }

        .count {
          display: none;
        }
      }

      &.active {
        .tab__title {
          font-weight: 600;
        }

        .complete {
          display: none;
        }
      }
    }

    .tab__item {
      margin-right: 6px;
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-shrink: 0;
      max-width: 140px;
    }
  }

  padding: 20px;
  border-bottom: 1px solid var(--ccb-fields-bg-color);

  .tab__title {
    color: var(--ccb-text-color);
    font-weight: 500;
    font-size: 13px;
  }
}

.summary-last-page {
  .ccb-pagination {
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .ccb-pagination {
    position: relative;

    &.circle_with_line,
    &.rectangle_steps_with_line {
      height: 70px;
    }

    &.circle_tabs,
    &.rectangle_tabs {
      height: 40px;
    }

    .ccb-circle-with-line,
    .ccb-circle-tab,
    .ccb-rectangle-with-line,
    .ccb-rectangle-tab {
      position: absolute;
    }
  }

  .ccb-page-navigation {
    flex-direction: column;
    gap: 15px;

    &__actions {
      display: grid !important;
      width: 100%;
      grid-template-columns: 1fr;
      gap: 10px;

      .ccb-button {
        width: 100% !important;
      }
    }
  }
}
</style>
