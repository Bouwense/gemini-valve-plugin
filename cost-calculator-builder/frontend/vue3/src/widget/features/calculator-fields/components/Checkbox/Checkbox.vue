<template>
  <div
    class="ccb-field ccb-checkbox-field"
    :class="{
      required: isRequired,
      'ccb-field-disabled': field.disabled,
      [boxStyle]: boxStyle,
      [additionalClasses]: true,
    }"
  >
    <div class="ccb-field__label">
      <RequiredHint
        v-if="
          isRequired &&
          field.selectedOption &&
          field.selectedOption.length < field.minAllowedOptions
        "
        :text="
          field.minAllowedOptions === 1
            ? field.minAllowedOptions +
              ' ' +
              translationsStore.getTranslations.minRequiredSingle
            : field.minAllowedOptions +
              ' ' +
              translationsStore.getTranslations.minRequiredPlural
        "
      />
      <RequiredHint v-else-if="isRequired" :text="requiredWarningText" />
      <div class="ccb-field__title">
        {{ field.label.trim()
        }}<span v-if="field.required" class="ccb-field-required-mark">*</span>
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
        :values="fieldValue"
        @update="applyChanges"
        :field="field"
      />
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
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { IMultiOptionsField } from "@/widget/shared/types/fields";
import { useMultiOptionsFieldShared } from "@/widget/actions/fields/composable/useMultiOptionsFieldShared.ts";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

type Props = {
  field: IMultiOptionsField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);
const translationsStore = useTranslationsStore();
const appearanceStore = useAppearanceStore();
const { applyChanges, isRequired, fieldValue, requiredWarningText } =
  useMultiOptionsFieldShared(props);

const currentComponents = computed(() => {
  const style = field.value?.styles?.style || "default";

  if (style === "default") {
    return defineAsyncComponent(() => import("./styles/DefaultStyle.vue"));
  } else if (style === "box") {
    return defineAsyncComponent(() => import("./styles/Box.vue"));
  } else if (style === "box-with-checkbox") {
    return defineAsyncComponent(() => import("./styles/BoxWithCheckbox.vue"));
  } else if (style === "box-with-checkbox-and-description") {
    return defineAsyncComponent(
      () => import("./styles/BoxWithCheckboxAndDescription.vue"),
    );
  } else if (style === "box-with-heading-checkbox-and-description") {
    return defineAsyncComponent(
      () => import("./styles/BoxWithHeadingCheckboxAndDescription.vue"),
    );
  }

  return "";
});

const boxStyle = computed(() => {
  return field.value?.styles?.boxStyle === "vertical"
    ? "ccb-vertical-checkbox"
    : "";
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
@use "@/styles/widget/_mixins.scss" as mixins;

.ccb-checkbox-label {
  font-size: var(--ccb-fields-button-size) !important;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-fields-button-size) !important;
  }
}
</style>
