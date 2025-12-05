<template>
  <div class="ccb-settings-toggle">
    <div
      class="ccb-toggle-item"
      v-for="option in customOptions"
      :key="option.key"
    >
      <div class="ccb-toggle-wrapper">
        <input
          :id="option.key"
          type="checkbox"
          :name="option.key"
          :value="option.value"
          :checked="option.value"
          @change="() => changeValue(option.key, !option.value)"
        />
        <label :for="option.key"></label>
      </div>

      <div class="ccb-toggle-item__label-wrap">
        <span class="ccb-toggle-item__label">{{ option.title }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, ref, onMounted } from "vue";

const props = defineProps<{
  options: { title: string; value: boolean; key: string }[];
  name: string;
}>();

const customOptions = ref<{ title: string; value: boolean; key: string }[]>([]);

const emit = defineEmits<{
  (e: "update", key: string, value: boolean): void;
}>();

const { options } = toRefs(props);

const changeValue = (key: string, value: boolean) => {
  customOptions.value.forEach((option) => {
    if (option.key === key) {
      option.value = value;
    }
  });

  emit("update", key, value);
};

onMounted(() => {
  customOptions.value = JSON.parse(JSON.stringify(options.value)) as {
    title: string;
    value: boolean;
    key: string;
  }[];
});
</script>

<style lang="scss" scoped>
.ccb-settings-toggle {
  display: flex;
  row-gap: 10px;
  flex-direction: column;
}

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
