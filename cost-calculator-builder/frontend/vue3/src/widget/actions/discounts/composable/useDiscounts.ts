import {
  DiscountsStore,
  IDiscount,
  IDiscountCondition,
  OriginalDiscountsStore,
} from "@/widget/shared/types/discounts";
import { computed, ref } from "vue";
import { IFormulaField, IRepeaterField } from "@/widget/shared/types/fields";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency.ts";
import { useConditions } from "@/widget/actions/conditions/composable/useConditions.ts";
import { ComputedRef } from "vue-demi";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";

const discounts = ref<DiscountsStore>({});
const originalDiscounts = ref<OriginalDiscountsStore>({});
const promocodes = ref<string[]>([]);
const originalPromocodes = ref<string[]>([]);

interface DiscountsResult {
  addDiscounts: (rawDiscounts: IDiscount[]) => void;
  applyDiscounts: (
    field: IFormulaField | IRepeaterField,
  ) => IFormulaField | IRepeaterField;
  promocodes: ComputedRef<string[]>;
  originalPromocodes: ComputedRef<string[]>;
  applyPromocode: (promocode: string) => void;
  removePromocode: (idx: number) => void;
  hasPromocodes: () => boolean;
}

function addDiscounts(rawDiscounts: IDiscount[]): void {
  const structuredDiscounts: DiscountsStore = {};

  rawDiscounts.forEach((discount) => {
    if (
      discount.isPromo &&
      discount.promocode &&
      !originalPromocodes.value.includes(discount.promocode)
    ) {
      originalPromocodes.value.push(discount.promocode);
    }

    discount.conditions.forEach((condition: IDiscountCondition) => {
      const fieldAliases = condition.fieldAlias.split(",");

      fieldAliases.forEach((fieldAlias: string): void => {
        if (!structuredDiscounts[fieldAlias]) {
          structuredDiscounts[fieldAlias] = [];
        }

        structuredDiscounts[fieldAlias].push({
          ...condition,
          isPromo: discount.isPromo,
          promocode: discount.promocode,
          discountId: +discount.discountId,
          discountConditionId: +condition.discountConditionId,
        });
      });
    });

    originalDiscounts.value[+discount.discountId] = discount;
  });

  discounts.value = structuredDiscounts;
}

function applyDiscounts(
  field: IFormulaField | IRepeaterField,
): IFormulaField | IRepeaterField {
  if (!discounts.value[field?.alias]) return field;
  const conditions = JSON.parse(
    JSON.stringify(discounts.value[field.alias]),
  )?.reverse();

  const withPromocodes = conditions.filter(
    (c: IDiscountCondition) => c.isPromo,
  );

  const notPromocodes = conditions.filter(
    (c: IDiscountCondition) => !c.isPromo,
  );

  const result = [...withPromocodes, ...notPromocodes];

  let applied = false;
  for (const condition of result) {
    const discount: IDiscount = originalDiscounts.value[condition.discountId];
    if (
      discount &&
      isDiscountValid(discount) &&
      evaluateDiscountCondition(field, condition) &&
      checkPromocodeConditions(discount) &&
      !applied
    ) {
      const totalDiscount = calculateDiscount(field.value || 0, condition);
      field.value = (field.originalValue || 0) - totalDiscount;
      field.hasDiscount = true;

      field.discount = {
        discountType: condition.discountType,
        isPromo: condition.isPromo,
        promocode: condition.promocode,
        viewType: discount.viewType,
        discountName: discount.title,
        extraView: parseAppliedDiscount(
          totalDiscount,
          discount,
          condition,
          field,
        ),
      };

      applied = true;
    }
  }

  if (!applied) {
    field.discount = undefined;
    field.hasDiscount = false;
  }

  return field;
}

function isDiscountValid(discount: IDiscount): boolean {
  const today = new Date().toISOString().split("T")[0];

  if (discount.period === "single_day" && discount.singleDate !== today) {
    return false;
  }

  if (
    discount.period === "period" &&
    (today < discount.periodStartDate! || today > discount.periodEndDate!)
  ) {
    return false;
  }

  return discount.discountStatus === "active";
}

function checkPromocodeConditions(discount: IDiscount) {
  return discount.isPromo
    ? promocodes.value.length &&
        discount.isPromo &&
        !!promocodes.value.includes(discount?.promocode || "")
    : true;
}

function toFixedTruncate(num: number, decimals: number): number {
  const factor = Math.pow(10, decimals);
  return Math.floor(num * factor) / factor;
}

function evaluateDiscountCondition(
  field: IFormulaField | IRepeaterField,
  condition: IDiscountCondition,
): boolean {
  let value = field.value || 0;

  const decimals =
    field.fieldCurrency && field.fieldCurrencySettings
      ? field.fieldCurrencySettings.numAfterInteger
      : 0;

  value = toFixedTruncate(Number(value), decimals);

  switch (condition.conditionSymbol) {
    case "<":
      return value !== 0 && value < +condition.overPrice;
    case ">":
      return value !== 0 && value > +condition.overPrice;
    case "=":
      return value === +condition.overPrice;
    default:
      return false;
  }
}

function calculateDiscount(
  value: number,
  condition: IDiscountCondition,
): number {
  if (condition.discountType === "percent_of_amount") {
    return (value * +condition.discountAmount) / 100;
  }
  return +condition.discountAmount;
}

function parseAppliedDiscount(
  discountValue: number,
  discount: IDiscount,
  condition: IDiscountCondition,
  field: IFormulaField,
): string {
  const currencyFormatter = useCurrency();
  const value = currencyFormatter.formatCurrency(
    discountValue,
    currencyFormatter.getCurrencyOptions(field),
  );

  if (discount.viewType === "show_with_title") {
    return `-${value}`;
  }

  if (condition.discountType === "percent_of_amount") {
    return `${Number(condition.discountAmount)}% off`;
  }

  return `-${value} off`;
}

function applyPromocode(promocode: string): void {
  const fieldStore = useFieldsStore();
  const { triggerCondition } = useConditions();

  if (!promocodes.value.includes(promocode)) {
    promocodes.value.push(promocode);
    fieldStore.recalculateTotals();
    triggerCondition();
    fieldStore.recalculateTotals();
  }
}

function removePromocode(idx: number): void {
  const fieldStore = useFieldsStore();
  const { triggerCondition } = useConditions();
  promocodes.value = promocodes.value.filter((_, i: number) => i !== idx);

  fieldStore.recalculateTotals();
  triggerCondition();
  fieldStore.recalculateTotals();
}

function hasPromocodes(): boolean {
  return originalPromocodes.value.length > 0;
}

export function useDiscounts(): DiscountsResult {
  return {
    addDiscounts,
    applyDiscounts,
    applyPromocode,
    removePromocode,
    hasPromocodes,
    promocodes: computed(() => promocodes.value),
    originalPromocodes: computed(() => originalPromocodes.value),
  };
}
