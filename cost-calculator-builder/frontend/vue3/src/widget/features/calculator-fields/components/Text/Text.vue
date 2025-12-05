<template>
  <div
    class="ccb-field ccb-text-field"
    :class="{
      required: isRequired,
      'ccb-field-disabled': field.disabled,
      [additionalClasses]: true,
    }"
  >
    <div class="ccb-field__label">
      <div v-if="isRequired" class="ccb-required-tooltip">
        <RequiredHint
          v-if="requitedType === 'limit'"
          :field="field"
          :text="`${translationsStore.getTranslations.allowedLimitIs} ${field.numberOfCharacters} ${translationsStore.getTranslations.characters}`"
        />
        <RequiredHint v-else :field="field" :text="requiredWarningText" />
      </div>
      <div class="ccb-field__title">
        <span>{{ field.label }}</span>
        <span v-if="field.required" class="ccb-field-required-mark">*</span>
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
      <textarea
        :value="field.displayValue"
        :placeholder="field.placeholder"
        @input="onInput"
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
import { toRefs, computed, ref } from "vue";
import { ITextField } from "@/widget/shared/types/fields";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

type Props = {
  field: ITextField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const fieldStore = useFieldsStore();
const appearanceStore = useAppearanceStore();
const translationsStore = useTranslationsStore();

const requitedType = ref<string>("required");

const requiredWarningText = computed(() => {
  const settingStore = useSettingsStore();
  return settingStore.getWarningTexts?.requiredMsg || "";
});

const updateValue = (value: string) => {
  requitedType.value = "required";
  let displayValue: string = value;
  field.value.value = 0;
  field.value.displayValue = displayValue;

  const length = field.value.displayValue?.length;

  if (field.value.numberOfCharacters === 0) {
    field.value.numberOfCharacters = 150;
  }
  if (!field.value.numberOfCharacters) {
    fieldStore.updateField(field.value.alias, field.value);
    return;
  }

  requitedType.value =
    length > field.value.numberOfCharacters ? "limit" : "required";

  fieldStore.updateField(field.value.alias, field.value);
};

const onInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  let value: string = target.value;
  updateValue(value);
};

const isRequired = computed(() => {
  if (requitedType.value === "limit") return true;

  return fieldStore.checkFieldRequired(field.value);
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
@use "@/styles/widget/_mixins.scss" as mixins;

.ccb-text-field {
  textarea {
    @include mixins.field;
  }

  textarea {
    resize: vertical;
    min-height: var(--ccb-field-button-height);

    @media only screen and (max-width: 480px) {
      padding: 12px var(--ccb-mobile-field-side-indent);
      min-height: var(--ccb-mobile-field-button-height);
    }
  }
}

.ccb-field {
  &.ccb-text-field {
    .ccb-field__input-wrapper {
      display: flex;
    }
  }
}
</style>
