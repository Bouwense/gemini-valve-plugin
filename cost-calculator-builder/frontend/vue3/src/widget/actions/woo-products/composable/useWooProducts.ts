import { WooMetaAction } from "@/widget/shared/types/fields";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { Field } from "@/widget/shared/types/fields";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useConditions } from "@/widget/actions/conditions/composable/useConditions.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

interface IWooProductsResult {
  applyWooMetaActions: (actions: WooMetaAction[]) => void;
}

export function useWooProducts(): IWooProductsResult {
  const settingsStore = useSettingsStore();
  function applyWooMetaActions(actions: WooMetaAction[]): void {
    const currentProductId =
      settingsStore.getWooProductsSettings?.currentProductId;

    if (!currentProductId) return;
    actions.forEach(({ action, calcField, wooMeta }) => {
      if ("price" === wooMeta) {
        const value = settingsStore?.getWooProductsSettings?.productPrice || 0;
        updateField(action, calcField, +value);
      }

      if ("quantity" === wooMeta) {
        const element: HTMLInputElement | null =
          document.querySelector("input.qty");
        if (element) {
          const quantity = element?.value || 0;
          updateField(action, calcField, +quantity);

          element.addEventListener("change", (event: Event) => {
            const target = event.target as HTMLInputElement;
            const value = target?.value || 0;
            updateField(action, calcField, +value);
          });
        }
      }
    });
  }

  function updateField(action: string, calcField: string, value: number): void {
    if (!action) return;

    const fieldStore = useFieldsStore();
    const singleFieldMixins = useSingleField();
    const { applyConditionForField } = useConditions();

    const field: Field | undefined = fieldStore.getField(calcField);
    if (field) {
      if (action === "set_value") {
        field.value = value;
      } else if (action === "set_value_disable") {
        field.value = value;
        field.disabled = true;
      }

      field.displayValue = singleFieldMixins.getCommonFieldDisplayView(field);

      fieldStore.updateField(field.alias, field);
      if (!field.hidden || field.calculateHidden) {
        applyConditionForField(field.alias);
      }
    }
  }

  return { applyWooMetaActions };
}
