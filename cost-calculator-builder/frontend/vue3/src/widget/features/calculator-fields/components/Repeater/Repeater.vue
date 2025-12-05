<template>
  <div class="ccb-repeater-wrapper">
    <div
      class="ccb-repeater-item"
      :class="{
        'ccb-collapsed': collapseStore[idx],
        [additionalClasses]: true,
      }"
      v-for="(repeaterFieldMap, idx) in field.groupElements"
    >
      <div
        class="ccb-repeater-item--header"
        @click="collapseStore[idx] = !collapseStore[idx]"
      >
        <span class="ccb-repeater-item--icon ccb-icon-Path-3514"></span>
        <span class="ccb-repeater-item--label">{{ field.label }}</span>
        <span class="ccb-repeater-item--counter">#{{ idx + 1 }}</span>
      </div>

      <Transition name="fade">
        <div class="ccb-repeater-item--body" v-if="!collapseStore[idx]">
          <div class="ccb-repeater-item--fields">
            <template
              v-for="field in getFieldsFromMap(repeaterFieldMap)"
              :key="field.alias"
            >
              <CalculatorField :name="field.fieldName" :field="field" />
            </template>
          </div>

          <div class="ccb-repeater-item--actions">
            <Button
              type="danger"
              :on-click="() => removeRepeater(idx)"
              :text="field.removeButtonLabel"
              icon="ccb-icon-Trash-filled"
              icon-position="before"
              :disabled="field.groupElements?.length === 1"
            />
          </div>
        </div>
      </Transition>
    </div>

    <div class="ccb-repeater-actions">
      <Button
        type="success-outlined"
        :on-click="addRepeater"
        :text="field.addButtonLabel"
        icon="ccb-icon-Add-Plus-Circle"
        icon-position="before"
        :disabled="disableStatusAddRepeaterBtn"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Field,
  IRepeaterField,
  ISourceField,
} from "@/widget/shared/types/fields";
import { computed, ref, toRefs } from "vue";
import CalculatorField from "@/widget/features/calculator-fields/CalculatorField.vue";
import Button from "@/widget/shared/ui/components/Button/Button.vue";
import { useFields } from "@/widget/actions/fields/composable/useFields.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";

type Props = {
  field: IRepeaterField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const fieldsStore = useFieldsStore();
const collapseStore = ref<Record<number, boolean>>({});

const getFieldsFromMap = computed(() => {
  return (data: Map<string, Field>): Field[] => {
    return Array.from(data.values());
  };
});

const removeRepeater = (idx: number) => {
  if (confirm("Are you sure to delete the repeated group of elements?")) {
    const groupElements = field.value.groupElements;
    if (Array.isArray(groupElements)) {
      field.value.groupElements.splice(idx, 1);
    }

    field.value.groupElements.forEach((elements, idx) =>
      Array.from(elements.entries()).forEach(([_, element]) => {
        element.repeaterIdx = idx;
      }),
    );

    fieldsStore.recalculateTotals();
  }
};

const addRepeater = () => {
  if (field.value.repeatCount === field.value.groupElements.length) return;

  const fieldsInstance = useFields();
  const groupElements: Map<string, Field> = new Map();

  const fields: ISourceField[] = field.value.originalElements || [];
  for (const f of fields) {
    const createdField: Field = fieldsInstance.addField(
      f,
      field.value.groupElements.length,
      field.value.alias,
    );

    if (createdField) {
      groupElements.set(f.alias, createdField);
    }
  }

  field.value.groupElements.push(groupElements);

  fieldsStore.recalculateTotals();
};

const disableStatusAddRepeaterBtn = computed(
  (): boolean => +field.value.repeatCount === field.value.groupElements.length,
);

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style scoped lang="scss">
.ccb-repeater-wrapper {
  display: flex;
  flex-direction: column;
  row-gap: 25px;

  .ccb-repeater-item {
    display: flex;
    flex-direction: column;
    border-left: 2px solid var(--ccb-container-dark-color);
    padding-left: 15px;
    row-gap: 8px;

    &--fields {
      display: flex;
      flex-direction: column;
      row-gap: 20px;
    }

    &--header {
      cursor: pointer;
      display: flex;
      column-gap: 6px;
      align-items: center;
      padding: 5px 0;
      color: var(--ccb-text-color);
    }

    &--icon {
      font-size: 12px;
      transition: 200ms linear;
      transform: rotate(0);
    }

    &--label {
      font-size: 16px;
      font-weight: 600;
    }

    &--counter {
      font-size: 16px;
      opacity: 0.6;
      font-weight: 600;
    }

    &--actions {
      margin-top: 10px;
      button {
        padding: 10px 20px;
      }
    }

    &.ccb-collapsed {
      .ccb-repeater-item--icon {
        transform: rotate(-180deg);
      }
    }
  }
}
</style>
