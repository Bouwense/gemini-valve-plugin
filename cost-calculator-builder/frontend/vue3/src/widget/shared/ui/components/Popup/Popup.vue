<template>
  <div v-show="isVisible" class="ccb-popup" :class="{ [size]: true }">
    <div class="ccb-popup__overlay">
      <div class="ccb-popup__wrapper">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { defineExpose, ref, computed } from "vue";

type Props = {
  size?: "medium" | "small" | "large";
};

const props = defineProps<Props>();
const size = computed(() => props.size || "medium");

const isVisible = ref(false);

const showPopup = () => {
  isVisible.value = true;
};

const hidePopup = () => {
  isVisible.value = false;
};

defineExpose({
  showPopup,
  hidePopup,
});
</script>

<style lang="scss">
.ccb-popup {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 100;

  &.large {
    .calc-thank-you-page-container {
      width: 730px;
    }
  }

  &.medium {
    .calc-thank-you-page-container {
      width: 495px;
      @media screen and (max-width: 550px) {
        width: unset;
      }
    }
  }

  &.small {
    .calc-thank-you-page-container {
      width: 410px;
    }
  }

  &__overlay {
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
  }

  &__wrapper {
    padding: 10px;
    background: transparent;

    @media (max-width: 768px) {
      width: 90%;
    }
  }
}
</style>
