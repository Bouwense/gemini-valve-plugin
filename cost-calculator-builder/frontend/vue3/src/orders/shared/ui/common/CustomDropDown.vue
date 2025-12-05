<template>
  <div class="ccb-custom-dropdown">
    <span v-if="title" class="ccb-custom-dropdown--title">{{ title }}</span>
    <div class="ccb-custom-dropdown--content">
      <select
        v-model="defaultValue"
        @change="handleSelect"
        class="ccb-custom-dropdown--select"
      >
        <option v-for="item in items" :key="item.value" :value="item.value">
          {{ item.label }}
        </option>
      </select>
      <i class="ccb-icon-Path-3485"></i>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, toRefs } from "vue";

interface IItem {
  value: string;
  label: string;
}

const props = defineProps<{
  id?: string | number;
  title?: string;
  items: IItem[];
  selected: string;
}>();

const emit = defineEmits<{
  (e: "select", value: string, id: string | number): void;
}>();

const { id, title, items, selected } = toRefs(props);

const defaultValue = ref<string>(selected.value);

const handleSelect = (event: Event) => {
  const value = (event.target as HTMLSelectElement).value;
  defaultValue.value = value;
  emit("select", value, id.value || "");
};
</script>

<style lang="scss" scoped>
.ccb-custom-dropdown {
  &--title {
    color: #001931;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 4px;
    display: inline-flex;
  }

  &--content {
    width: 100%;
    height: 32px;
    padding: 0 8px;
    border-radius: 6px;
    background: #ffffff;
    position: relative;
    border: 1px solid #dddddd;

    i {
      position: absolute;
      right: 15px;
      top: 50%;
      color: #001931;
      opacity: 0.5;
      transition: all 200ms ease-in-out;
      transform: translateY(-40%);
      font-size: 9px;

      &:hover {
        opacity: 0.7;
      }
    }

    select {
      font-size: 14px;
      color: #001931;
      width: 100%;
      height: 100%;
      border: none;
      outline: none;
      background: transparent;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      -ms-appearance: none;

      &:focus,
      &:hover {
        outline: none;
        box-shadow: none;
        border: none;
        background: transparent;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        -ms-appearance: none;
      }
    }
  }
}
</style>
