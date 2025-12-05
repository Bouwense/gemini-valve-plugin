<template>
  <label style="column-gap: 5px">
    <input
      type="radio"
      :name="name"
      :value="value"
      :checked="checked"
      @change="() => handleChange(value)"
    />
    <span class="ccb-heading-5" v-if="showLabel">{{ label }}</span>
  </label>
</template>

<script setup lang="ts">
import { toRefs } from "vue";

const props = defineProps<{
  name: string;
  label: string;
  value: string | number;
  showLabel: boolean;
  checked: boolean;
}>();

const { name, label, value, showLabel, checked } = toRefs(props);

const emit = defineEmits<{
  (e: "update", value: string | number): void;
}>();

const handleChange = (value: string | number) => {
  emit("update", value);
};
</script>

<style lang="scss" scoped>
label {
  margin: 0 !important;
  padding: 0;
  font-size: 14px;
  font-style: normal;
  font-weight: 700;
  line-height: 20px;
  color: #001931;
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
}

.description {
  display: block;
  font-size: 14px;
  font-style: normal;
  font-weight: 500;
  line-height: 20px;
  color: #001931;
  opacity: 0.6;
  margin-left: 30px;
}

input[type="radio"] {
  outline: none;
  max-width: 20px;
  min-width: 20px;
  max-height: 20px;
  min-height: 20px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  position: relative;
  border: 2px solid #dddddd;
  background-color: #ffffff;
  -webkit-appearance: none;
  margin-right: 0px;
  margin-top: 0;
  display: flex;
  align-items: center;
  flex-wrap: nowrap;

  &:focus {
    outline: none !important;
    box-shadow: none !important;
  }

  &:before {
    content: "";
    width: 10px;
    height: 10px;
    margin-top: 3px;
    margin-left: 3px;
    display: block;
    border-radius: 50%;
  }

  &:checked {
    background: #00b163ff;
    border: 2px solid transparent !important;
  }

  &:checked:before {
    background: #fff;
  }
}
</style>
