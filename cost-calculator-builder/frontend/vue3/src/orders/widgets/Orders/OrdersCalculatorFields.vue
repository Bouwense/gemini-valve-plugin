<template>
  <div class="order-details-calculator-fields">
    <div
      class="order-details-calculator-fields__item"
      v-for="field in getFields"
    >
      <div class="order-details-calculator-fields__item-label">
        <span class="option-label">{{ field.label }}</span>
        <span
          class="extra-option-list"
          v-if="field.extraOptions.length > 0 && !isArrayValues(field)"
        >
          <template v-if="field.alias.includes('geolocation')">
            <template v-if="field.extraOptions.length > 2">
              From:
              <a :href="field.extraOptions[0].addressLink" target="_blank">{{
                field.extraOptions[0].label
              }}</a>
              To:
              <a :href="field.extraOptions[1].addressLink" target="_blank">{{
                field.extraOptions[1].label
              }}</a>
              {{ field.extraOptions[2].distanceView }}
            </template>
            <template v-else>
              <a :href="field.extraOptions[0].addressLink" target="_blank">{{
                field.extraOptions[0].label
              }}</a>
              {{ field.extraOptions[0].distanceView }}
            </template>
          </template>
          <template v-else>
            <span v-for="option in field.extraOptions" :key="option.label">
              {{ option.label }}
            </span>
          </template>
        </span>
      </div>
      <div
        class="order-details-calculator-fields__item-value"
        :class="{ 'option-list': isArrayValues(field) }"
      >
        <template v-if="!isArrayValues(field)">
          <span>{{ field.value }}</span>
        </template>
        <template v-else>
          <span v-for="option in field.extraOptions" :key="option.label">
            {{ option.label }}
          </span>
        </template>
      </div>
    </div>

    <div
      class="order-details-calculator-fields__item totals"
      v-if="otherTotals.length > 0"
    >
      <div class="total-item" v-for="total in otherTotals">
        <span>{{ total.label }}</span>
        <span>{{ total.converted }}</span>
      </div>
    </div>

    <div
      class="order-details-calculator-fields__item totals"
      v-if="totals.length > 0"
    >
      <div class="total-item" v-for="total in totals">
        <span>{{ total.label }}</span>
        <span>{{ total.converted }}</span>
      </div>
    </div>

    <div class="order-details-calculator-fields__item totals">
      <div class="total-item">
        <span>Total</span>
        <span>{{ formatCurrency(total_amount) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, toRefs } from "vue";
import {
  ICalculatorFields,
  ICurrency,
  ITotals,
} from "@/orders/shared/types/orders.type";
import { currencyConvertor } from "@/orders/shared/utils/useCurrencyConvertor";

type Props = {
  fields: ICalculatorFields[];
  totals: ITotals[];
  otherTotals: ITotals[];
  total_amount: number | string;
  currency: ICurrency;
};

type IOrderDetails = {
  alias: string;
  sort_id: number;
  label: string;
  value: string;
  summary_view: string;
  extraOptions: {
    label: string;
    value: string;
    distanceView?: string;
    addressLink?: string;
  }[];
};

const props = defineProps<Props>();
const { fields, totals, otherTotals, total_amount, currency } = toRefs(props);

const getFields = computed(() => {
  const result: Array<IOrderDetails> = [];

  for (const f of fields.value) {
    const d: IOrderDetails = {
      sort_id: f.sort_id,
      label: f.label,
      value: f.value || f.converted || "0",
      extraOptions: [],
      alias: f.alias,
      summary_view: f.summary_view,
    };

    if (+f.is_basic && f.option_unit) {
      d.extraOptions.push({
        label: "",
        value: f.option_unit,
      });
    } else {
      if (!f.alias.includes("file_upload") && !+f.is_basic) {
        if (!Array.isArray(f.value) && f.value) {
          d.value = f.value;
        } else if (Array.isArray(f.value) && f.converted) {
          d.value = f.converted;
        } else if (Array.isArray(f.value) && !f.converted) {
          d.value = "0";
        } else if (!Array.isArray(f.value) && !f.value && f.converted) {
          d.value = f.converted;
        } else {
          d.value = "0";
        }

        const options = f.options || [];
        for (const o of options) {
          if (f.alias.includes("geolocation")) {
            d.extraOptions.push({
              label: o.label,
              value: o.converted,
              distanceView: o.distanceView,
              addressLink: o.addressLink,
            });
          } else {
            d.extraOptions.push({
              label: o.label,
              value: o.converted,
            });
          }
        }
      } else {
        const options = Array.isArray(f.value) ? f.value : [];
        for (const o of options) {
          const optionValue = f.options?.find(
            (inner_o) => inner_o.optionValue === o,
          );
          if (optionValue) {
            d.extraOptions.push({
              label: optionValue.label,
              value: optionValue.converted,
            });
          }
        }
      }
    }

    if (f.alias.includes("file_upload") && f.option_unit) {
      d.extraOptions = [{ label: "", value: f.option_unit }];
    }

    if (+f.is_basic && f.summary_view === "show_value" && f.option_unit) {
      d.extraOptions.push({
        label: "",
        value: f.option_unit,
      });
    }

    result.push(d);
  }

  return result.sort((a, b) => a.sort_id - b.sort_id);
});

const formatCurrency = computed(() => {
  return (amount: number | string) => {
    return currencyConvertor(Number(amount), {
      currency: currency.value.currency_sign || "",
      currencyPosition: currency.value.currency_position,
      numAfterInteger: currency.value.number_of_decimals,
      thousandsSeparator: currency.value.thousand_separator,
      decimalSeparator: currency.value.decimal_separator,
    });
  };
});

const isArrayValues = computed(() => {
  return (field: IOrderDetails) => {
    const alias = field.alias.replace(/\_field_id.*/, "");
    const optionFields = ["checkbox", "checkbox_with_img", "toggle"];
    return optionFields.includes(alias) && field.summary_view !== "show_value";
  };
});
</script>

<style scoped lang="scss">
.order-details-calculator-fields {
  display: flex;
  padding: 8px 16px;
  flex-direction: column;
  align-items: flex-start;
  align-self: stretch;
  width: 100%;

  border-radius: 12px;
  background: rgba(22, 36, 50, 0.03);

  &__item {
    display: flex;
    padding: 16px 0;
    justify-content: space-between;
    width: 100%;
    column-gap: 10px;

    &-label {
      display: flex;
      flex-direction: column;
      row-gap: 10px;
      max-width: 65%;

      .option-label {
        color: #162432;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
      }

      .extra-option-list {
        display: flex;
        flex-direction: column;
        row-gap: 5px;
        color: rgba(22, 36, 50, 0.6);
        font-size: 13px;
        font-weight: 500;
        line-height: 1;
      }
    }

    &-value {
      color: #162432;
      font-size: 14px;
      font-weight: 700;
      line-height: 1;

      &.option-list {
        display: flex;
        flex-direction: column;
        row-gap: 5px;
      }
    }

    &:not(:last-child) {
      border-bottom: 1px dashed var(--Lines-Stroke-10, rgba(22, 36, 50, 0.1));
    }

    &.totals {
      display: flex;
      flex-direction: column;
      row-gap: 12px;

      .total-item {
        display: flex;
        justify-content: space-between;
        width: 100%;

        span {
          color: #162432;
          font-size: 16px;
          font-weight: 700;
          line-height: 1;
        }
      }
    }
  }
}
</style>
