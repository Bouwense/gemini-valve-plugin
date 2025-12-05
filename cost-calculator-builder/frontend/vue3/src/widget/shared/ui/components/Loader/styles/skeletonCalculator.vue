<template>
  <div class="ccb-skeleton-calculator">
    <Layout class="ccb-layout-container">
      <Wrapper wrapper="fields" class="ccb-fields-block">
        <div class="ccb-skeleton-fields">
          <div class="skeleton-input-wrapper">
            <div class="skeleton-label"></div>
            <div class="skeleton-input"></div>
          </div>
          <div class="skeleton-checkbox-wrapper">
            <div class="skeleton-checkbox"></div>
            <div class="skeleton-label"></div>
          </div>
          <div class="skeleton-checkbox-wrapper">
            <div class="skeleton-checkbox"></div>
            <div class="skeleton-label" style="width: 20%"></div>
          </div>
          <div class="skeleton-range-slider-wrapper">
            <div class="skeleton-range-bar"></div>
            <div class="skeleton-range-thumb"></div>
          </div>
          <div class="skeleton-input-wrapper">
            <div class="skeleton-label" style="width: 20%"></div>
            <div class="skeleton-input"></div>
          </div>
          <div class="skeleton-textarea-wrapper">
            <div class="skeleton-textarea"></div>
          </div>
        </div>
      </Wrapper>
      <Wrapper wrapper="subtotal" class="ccb-subtotals-block">
        <div class="skeleton-header"></div>
        <div class="skeleton-subtotal">
          <div class="skeleton-list-wrapper">
            <div v-for="index in 11" :key="index" class="skeleton-list-item">
              <div
                class="skeleton-label"
                :style="{ width: `${Math.random() * 30 + 10}%` }"
              ></div>
              <div class="skeleton-dots"></div>
              <div
                class="skeleton-end"
                :style="{ width: `${Math.random() * 20 + 10}%` }"
              ></div>
            </div>
          </div>
          <div class="skeleton-actions">
            <div class="skeleton-action"></div>
            <div class="skeleton-action"></div>
          </div>
        </div>
      </Wrapper>
    </Layout>
  </div>
</template>

<script setup lang="ts">
import Layout from "@/widget/shared/ui/layouts/Layout.vue";
import Wrapper from "@/widget/shared/ui/wrappers/Wrapper.vue";
</script>

<style lang="scss">
.ccb-skeleton-calculator {
  width: 100%;
}

.skeleton-header {
  width: 100%;
  height: 20px;
  background-color: #f0f0f0;
  border-radius: 2px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
  margin-bottom: 10px;
}

.skeleton-action {
  width: 100%;
  height: 40px;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-list-wrapper {
  width: 100%;
  max-width: 600px;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.skeleton-list-item {
  width: 100%;
  display: flex;
  align-items: flex-end;
  gap: 10px;
}

.skeleton-label {
  width: 30%;
  height: 20px;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-dots {
  flex-grow: 1;
  height: 2px;
  background-color: #f0f0f0;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-end {
  width: 20px;
  height: 20px;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.ccb-skeleton-fields {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.skeleton-textarea-wrapper {
  width: 100%;
  height: 150px;
  display: flex;
  justify-content: center;
}

.skeleton-textarea {
  width: 100%;
  height: 100%;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-input-wrapper {
  width: 100%;
  height: 80px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: flex-start;
}

.skeleton-label {
  width: 40%;
  height: 20px;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-input {
  width: 100%;
  height: 40px;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-checkbox-wrapper {
  width: 100%;
  height: 40px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.skeleton-checkbox {
  width: 20px;
  height: 20px;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-label {
  width: 60%;
  height: 20px;
  background-color: #f0f0f0;
  border-radius: 4px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-range-slider-wrapper {
  width: 100%;
  height: 10px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: center;
  position: relative;
  margin-top: 10px;
  margin-bottom: 20px;
}

.skeleton-range-bar {
  width: 100%;
  height: 6px;
  background-color: #f0f0f0;
  border-radius: 3px;
  animation: pulse 1.5s infinite ease-in-out;
}

.skeleton-range-thumb {
  width: 16px;
  height: 16px;
  background-color: #f0f0f0;
  border-radius: 50%;
  position: absolute;
  top: -5px;
  animation: pulse 1.5s infinite ease-in-out;
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
