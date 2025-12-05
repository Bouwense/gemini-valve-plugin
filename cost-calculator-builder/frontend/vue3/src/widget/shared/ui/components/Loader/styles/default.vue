<template>
  <div class="ccb-loader-default">
    <div class="spinner"></div>
  </div>
</template>

<script setup lang="ts"></script>

<style lang="scss">
.ccb-loader-default {
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner {
  border: 6px solid #fff;
  border-top: 6px solid var(--ccb-accent-color);
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
