import { defineStore } from "pinia";
import {
  ICondition,
  IConditionList,
  IConditionsStore,
} from "@/widget/shared/types/app";
import { useConditions } from "@/widget/actions/conditions/composable/useConditions.ts";
import { WooMetaAction } from "@/widget/shared/types/fields";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { useWooProducts } from "@/widget/actions/woo-products/composable/useWooProducts.ts";

export interface IConditionsStoreResult {
  conditions: IConditionsStore;
  conditionsHistory: ICondition[];
}

export const useConditionsStore = defineStore("conditions_store", {
  state: (): IConditionsStoreResult => ({
    conditions: {} as IConditionsStore,
    conditionsHistory: [],
  }),

  actions: {
    setConditions(conditions: IConditionsStore): void {
      this.conditions = conditions;
    },

    initConditions(conditions: IConditionList[] = []): void {
      const conditionInstance = useConditions();
      conditionInstance.addConditions(conditions);
    },

    initWooProductsConditions(): void {
      const settingsStore = useSettingsStore();
      const wooProducts = useWooProducts();
      const wooActions: WooMetaAction[] =
        settingsStore.getWooProductsSettings?.metaLinks || [];

      setTimeout(() => wooProducts.applyWooMetaActions(wooActions));
    },

    applyConditionForField(alias: string): void {
      const conditionInstance = useConditions();
      conditionInstance.applyConditionForField(alias);
    },

    triggerCondition(): void {
      const conditionInstance = useConditions();
      conditionInstance.triggerCondition();
    },

    updateConditionHistory(conditions: ICondition[]): void {
      this.conditionsHistory = conditions;
    },
  },

  getters: {
    getConditionHistory(): ICondition[] {
      return this.conditionsHistory;
    },
  },
});
