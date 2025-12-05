<template>
  <div class="ccb-payment-skeleton">
    <div class="ccb-skeleton-title" />

    <div class="ccb-skeleton-option">
      <div class="ccb-skeleton-radio" />
      <div class="ccb-skeleton-label" />
      <div class="ccb-skeleton-icon" />
    </div>

    <div class="ccb-skeleton-button full" />
  </div>
</template>

<script lang="ts" setup></script>

<style scoped>
.ccb-payment-skeleton {
  background-color: var(--ccb-background-color); /* Синий фон */
  border-radius: 8px;
  width: 100%;
  max-width: 400px;
}

.ccb-skeleton-title {
  width: 120px;
  height: 16px;
  background-color: color-mix(in srgb, var(--ccb-text-color) 15%, #fff);
  border-radius: 4px;
  margin-bottom: 1rem;
  animation: pulse 1.5s infinite ease-in-out;
}

.ccb-skeleton-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  animation: pulse 1.5s infinite ease-in-out;
}

.ccb-skeleton-radio {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background-color: color-mix(in srgb, var(--ccb-text-color) 15%, #fff);
  animation: pulse 1.5s infinite ease-in-out;
}

.ccb-skeleton-label {
  width: 120px;
  height: 14px;
  background-color: color-mix(in srgb, var(--ccb-text-color) 15%, #fff);
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.ccb-skeleton-icon {
  width: 24px;
  height: 16px;
  background-color: color-mix(in srgb, var(--ccb-text-color) 15%, #fff);
  border-radius: 4px;
  margin-left: auto;
  animation: pulse 1.5s infinite ease-in-out;
}

.ccb-skeleton-button {
  height: 40px;
  background-color: color-mix(in srgb, var(--ccb-accent-color) 35%, #fff);
  border-radius: 6px;
}

.ccb-skeleton-button.full {
  width: 100%;
}

.ccb-skeleton-button-row {
  display: flex;
  gap: 1rem;
}

@keyframes pulse {
  0% {
    background-color: #f0f0f0;
  }
  50% {
    background-color: #e0e0e0;
  }
  100% {
    background-color: #f0f0f0;
  }
}
</style>
