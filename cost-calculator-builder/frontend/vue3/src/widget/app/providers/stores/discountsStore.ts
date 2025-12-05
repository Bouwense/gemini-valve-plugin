import { defineStore } from "pinia";
import { IDiscount } from "@/widget/shared/types/discounts";
import { useDiscounts } from "@/widget/actions/discounts/composable/useDiscounts.ts";
import { convertKeysToCamelCase } from "@/widget/shared/utils/convert-case-to-camel-case.utils.ts";
import { IFormulaField } from "@/widget/shared/types/fields";

const discountInstance = useDiscounts();

export const useDiscountsStore = defineStore("discounts_store", {
  state: () => ({}),

  getters: {
    getUsedPromocodes: (): string[] => discountInstance.promocodes.value || [],
  },

  actions: {
    initDiscounts(discounts: IDiscount[] = []): void {
      discounts = convertKeysToCamelCase(discounts);
      discountInstance.addDiscounts(discounts);
    },

    applyDiscount(field: IFormulaField): IFormulaField {
      return discountInstance.applyDiscounts(field);
    },
  },
});
