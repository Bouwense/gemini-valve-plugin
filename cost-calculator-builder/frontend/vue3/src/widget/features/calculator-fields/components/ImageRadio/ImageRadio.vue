<template>
  <div
    class="ccb-field ccb-radio-img-field"
    :class="{
      required: isRequired,
      'ccb-field-disabled': field.disabled,
      [boxStyle]: boxStyle,
      [additionalClasses]: true,
    }"
  >
    <div class="ccb-field__label">
      <RequiredHint v-if="isRequired" :text="requiredWarningText" />
      <div class="ccb-field__title">
        {{ field.label
        }}<span v-if="field.required" class="ccb-field-required-mark">*</span
        ><ProBadge />
      </div>
    </div>

    <div
      v-if="appearanceStore.getAppearanceDescriptionPosition === 'before'"
      class="ccb-field__descriptions ccb-before"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>

    <div class="ccb-field__input-wrapper">
      <component
        :is="currentComponents"
        :key="getKey"
        :alias="field.alias"
        :options="field.options"
        :current="fieldValue"
        @update="selectValue"
        :repeter="field.repeaterIdx"
        :showValueInOption="field.showValueInOption"
        :field="field"
      ></component>
    </div>

    <div
      v-if="appearanceStore.getAppearanceDescriptionPosition === 'after'"
      class="ccb-field__descriptions ccb-after"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, defineAsyncComponent } from "vue";
import { ISingleOptionsField } from "@/widget/shared/types/fields";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { useSingleOptionFieldShared } from "@/widget/actions/fields/composable/useSingleOptionFieldShared.ts";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: ISingleOptionsField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const appearanceStore = useAppearanceStore();

const { selectValue, fieldValue, isRequired, getKey, requiredWarningText } =
  useSingleOptionFieldShared(props);

const currentComponents = computed(() => {
  const style = field.value?.styles?.style || "default";
  if (style === "default") {
    return defineAsyncComponent(() => import("./styles/DefaultStyle.vue"));
  } else if (style === "with-icon") {
    return defineAsyncComponent(() => import("./styles/WithIcon.vue"));
  }

  return "";
});

const boxStyle = computed(() => {
  return field.value?.styles?.boxStyle === "vertical"
    ? "ccb-vertical-radio"
    : "";
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
.ccb-radio-image__label {
  font-size: var(--ccb-fields-button-size) !important;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-fields-button-size) !important;
  }
}
</style>
