import { computed, ref, toRefs } from "vue";
import { IMultiOptionsField, IOptions } from "@/widget/shared/types/fields";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";

export function useMultiOptionsFieldShared(props: {
  field: IMultiOptionsField;
}) {
  const { field } = toRefs(props);
  const conditionsStore = useConditionsStore();
  const pageBreakerStore = usePageBreakerStore();
  const fieldStore = useFieldsStore();
  const singleFieldInstance = useSingleField();

  const style = ref<string>("default");
  const selectedOptions = ref<IOptions[]>([]);

  const isRequired = computed(() => fieldStore.checkFieldRequired(field.value));
  const fieldSelectedOption = computed(() => field.value.selectedOption || []);

  const fieldValue = computed(() =>
    fieldSelectedOption.value?.map((f: IOptions) => f.optionValue),
  );

  const getKey = computed(() => {
    return (
      fieldValue.value
        ?.map((optionValue: string) => {
          return optionValue.split("_")[1] || 9999;
        })
        .join("_") || 9999
    );
  });

  const updateValue = (options: IOptions[]) => {
    field.value.selectedOption = options;

    let value = 0;
    if (Array.isArray(options)) {
      for (const option of options) {
        let splitValues: string[] = option.optionValue?.toString()?.split("_");
        const originalValue: number = +splitValues[0];

        if (
          !isNaN(originalValue) &&
          field.value.summaryView !== "show_label_not_calculable"
        ) {
          value += originalValue;
        }
      }
    }

    field.value.value = value;
    field.value.displayValue =
      singleFieldInstance.getMultipleOptionsFieldDisplayView(field.value);

    fieldStore.updateField(field.value.alias, field.value);
    conditionsStore.applyConditionForField(field.value.alias);

    if (
      fieldStore.getPageBreakEnabled &&
      fieldStore.getActivePage.action === "not_skip"
    ) {
      pageBreakerStore.checkPageFieldsConditions();
    }
  };

  const applyChanges = (value: string): void => {
    selectedOptions.value = [];
    const values = value.split(",");

    for (const v of values) {
      const option = field.value.options.find(
        (f: IOptions) => f.optionValue === v,
      );

      if (option) {
        selectedOptions.value.push(option);
      }
    }

    updateValue(selectedOptions.value);
  };

  const requiredWarningText = computed(() => {
    const settingStore = useSettingsStore();
    return settingStore.getWarningTexts?.requiredMsg || "";
  });

  return {
    style,
    selectedOptions,
    updateValue,
    applyChanges,
    isRequired,
    fieldValue,
    getKey,
    requiredWarningText,
  };
}
