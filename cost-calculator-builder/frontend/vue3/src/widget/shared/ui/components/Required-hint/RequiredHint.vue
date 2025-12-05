<template>
  <div class="ccb-required-hint">
    <span>{{ text }}</span>
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
type Props = {
  text: string;
};

const props = defineProps<Props>();
const { text } = toRefs(props);
</script>

<style lang="scss">
.ccb-required-hint {
  position: relative;
  background: #d94141;
  color: #fff;
  padding: 5px 10px;
  border-radius: 3px;
  font-size: 14px;
  font-weight: 600;
  z-index: 99;
  white-space: nowrap;
  width: fit-content;

  &:after {
    content: "";
    position: absolute;
    bottom: -3px;
    left: 10px;
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 5px solid #d94141;
  }

  span {
    display: block;
    line-height: 1.2;
  }
}
</style>
