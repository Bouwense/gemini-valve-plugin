<template>
  <div class="ccb-field__input-wrapper" ref="dropdownWrapper">
    <div class="ccb-order-dropdown">
      <div class="ccb-order-dropdown__input" @click="toggleBody">
        <span class="ccb-dropdown__label" v-if="!selectedValue.length">{{
          field.placeholder
            ? field.placeholder
            : translationsStore.getTranslations.selectValue
        }}</span>
        <span v-else class="ccb-order-dropdown__label">{{
          selectedValue
        }}</span>
        <span class="ccb-order-dropdown__icon">
          <i class="ccb-icon-Path-3485"></i>
        </span>
      </div>
      <div class="ccb-order-dropdown__list">
        <ul>
          <li @click.prevent="() => selectValue('')">
            {{ translationsStore.getTranslations.selectValue }}
          </li>
          <template v-if="field.attributes?.options">
            <li
              v-for="(option, idx) in field.attributes.options"
              :key="idx"
              @click.prevent="() => selectValue(`${option.optionText}_${idx}`)"
            >
              {{ option.optionText }}
            </li>
          </template>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, toRefs, ref } from "vue";
import { IFormField } from "@/widget/shared/types/fields";
import { useOrderFormDropdown } from "@/widget/actions/pro-features/order-form/composable/useOrderFormDropdown.ts";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { splitByLastUnderscore } from "@/widget/shared/utils/split-by-last-underscore.utils.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

const emit = defineEmits<{
  (event: "toggle", value: boolean): void;
}>();

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const dropdownWrapper = ref<HTMLElement | null>(null);
const { toggleBody } = useOrderFormDropdown(props, emit, dropdownWrapper);
const translationsStore = useTranslationsStore();

const selectedValue = computed(() => {
  return splitByLastUnderscore(field.value.value || "");
});

const selectValue = (value: string): void => {
  field.value.value = value;
  toggleBody();

  const { updateValue } = useOrderForm();
  updateValue(field.value.id, field.value.value);
};

onMounted(() => {
  if (Array.isArray(field.value?.value)) {
    field.value.value = field.value.value[0];
  }
});
</script>

<style scoped lang="scss"></style>
