<template>
  <div class="ccb-toggle-item">
    <div class="ccb-toggle-wrapper">
      <input
        type="checkbox"
        :name="name"
        :id="name + '_' + option.key"
        :value="option.value"
        :checked="option.value"
        @change="() => changeValue(option.key, !option.value)"
      />
      <label :for="name + '_' + option.key"></label>
    </div>

    <div class="ccb-toggle-item__label-wrap">
      <span class="ccb-toggle-item__label">{{ option.label }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";

const emit = defineEmits<{
  (e: "change", name: string, key: string, value: boolean): void;
}>();

const props = defineProps<{
  name: string;
  option: { key: string; value: boolean; label: string };
}>();

const { option, name } = toRefs(props);

const changeValue = (key: string, value: boolean) => {
  option.value.value = value;
  emit("change", name.value, key, value);
};
</script>
<style lang="scss" scoped>
.ccb-toggle-item {
  display: flex;
  padding: 0;
  vertical-align: middle;
  align-items: center;
  column-gap: 10px;

  &__postfix {
    color: #1a2734;
    cursor: pointer;
  }

  &__label-wrap {
    display: flex;
    align-items: center;

    overflow: hidden;
    color: #001931;
    text-overflow: ellipsis;
    font-size: 14px;
    font-style: normal;
    font-weight: 700;
  }

  .ccb-toggle-wrapper {
    margin: 0;
    position: relative;
    display: flex;
    align-items: center;

    input {
      display: none;
    }

    label {
      cursor: pointer;
      width: 45px;
      height: 24px;
      background: #00193133;
      position: relative;
      border-radius: 46px;
      display: inline-block;
      margin: 0;
      padding: 0;

      &:after {
        content: "";
        background: #f7f7f7;
        top: 3px;
        width: 18px;
        height: 18px;
        position: absolute;
        border-radius: 100%;
        left: 3px;
        z-index: 2;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        transition: left 0.3s ease !important;
      }
    }

    input:checked + label:after {
      left: 24px;
    }

    input:checked + label {
      background: #00b163;
    }
  }
}
</style>
