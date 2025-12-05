<template>
  <div class="ccb-chart-skeleton">
    <div class="ccb-line-shimmer">
      <div class="ccb-shimmer-line"></div>
    </div>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
.ccb-chart-skeleton {
  width: 100%;
  height: 100%;
  background: white;
  border-radius: 12px;
  box-shadow: 0 6px 10px rgba(0, 0, 0, 0.05);
  position: absolute;
  overflow: hidden;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
}

.ccb-chart-skeleton::before {
  content: "";
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-image:
    linear-gradient(to right, rgba(0, 0, 0, 0.05) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(0, 0, 0, 0.05) 1px, transparent 1px);
  background-size: 10% 25%;
  z-index: 1;
}

.ccb-line-shimmer {
  position: absolute;
  inset: 0;
  z-index: 2;
}

.ccb-shimmer-line {
  width: 100%;
  height: 100%;
  background: linear-gradient(
    -45deg,
    #ff9a9e,
    #fad0c4,
    #fbc2eb,
    #a18cd1,
    #00b163,
    #8fd3f4,
    #ff9a9e
  );
  background-size: 400% 400%;
  animation: ccb-shimmer-move 3s ease-in-out infinite;
  opacity: 0.35;
  clip-path: polygon(
    0% 80%,
    10% 60%,
    20% 65%,
    30% 45%,
    40% 50%,
    50% 35%,
    60% 40%,
    70% 30%,
    80% 35%,
    90% 20%,
    100% 25%,
    100% 100%,
    0% 100%
  );
}

@keyframes ccb-shimmer-move {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
</style>
