<template>
  <div
    class="ccb-field ccb-toggle-field"
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
import { IMultiOptionsField } from "@/widget/shared/types/fields";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import { useMultiOptionsFieldShared } from "@/widget/actions/fields/composable/useMultiOptionsFieldShared.ts";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IMultiOptionsField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const appearanceStore = useAppearanceStore();

const { applyChanges, isRequired, fieldValue, requiredWarningText } =
  useMultiOptionsFieldShared(props);

const currentComponents = computed(() => {
  const style = field.value?.styles?.style || "default";
  if (style === "default") {
    return defineAsyncComponent(
      () =>
        import(
          "@/widget/features/calculator-fields/components/Toggle/styles/Default.vue"
        ),
    );
  } else if (style === "box-with-toggle-and-description") {
    return defineAsyncComponent(
      () => import("./styles/BoxToggleWithDescription.vue"),
    );
  }

  return "";
});

const boxStyle = computed(() => {
  return field.value?.styles?.boxStyle === "vertical"
    ? "ccb-vertical-toggle"
    : "";
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
@use "@/styles/widget/_mixins.scss" as mixins;

.ccb-toggle-item__label {
  font-size: var(--ccb-fields-button-size) !important;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-fields-button-size) !important;
  }
}
</style>
