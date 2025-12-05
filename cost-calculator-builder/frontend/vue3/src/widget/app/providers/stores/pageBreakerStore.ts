import { defineStore } from "pinia";
import { IConditionsStore } from "@/widget/shared/types/app/pageConditions.type";
import { useFieldsStore } from "./fieldsStore.ts";
import { Field } from "@/widget/shared/types/fields";
import { IConditionRule } from "@/widget/shared/types/app/pageConditions.type";
import { IOptions } from "@/widget/shared/types/fields";
import { IPageBreakerField } from "@/widget/shared/types/fields/fields.type";

interface IPageBreakerStore {
  conditions: IConditionsStore;
  disableNextButton: boolean;
}

const multipleOptionsField: string[] = [
  "checkbox",
  "checkbox_with_img",
  "toggle",
];

const singleOptionsField: string[] = [
  "dropDown",
  "dropDown_with_img",
  "radio",
  "radio_with_img",
];

export const usePageBreakerStore = defineStore("page_breaker_store", {
  state: (): IPageBreakerStore => ({
    conditions: {},
    disableNextButton: false,
  }),

  getters: {
    getConditions: (state: IPageBreakerStore): IConditionsStore =>
      state.conditions,

    getDisableNextButton: (state: IPageBreakerStore): boolean =>
      state.disableNextButton,
  },

  actions: {
    updateConditions(conditions: IConditionsStore): void {
      this.conditions = conditions;
    },

    setDisableNextButton(disable: boolean): void {
      this.disableNextButton = disable;
    },

    initPageConditions(): void {
      const fieldsStore = useFieldsStore();

      const pages = fieldsStore.getPages.filter(
        (page): page is IPageBreakerField => {
          return "groupElements" in page && "conditions" in page;
        },
      );

      pages.forEach((page: IPageBreakerField): void => {
        this.updateConditions({
          ...this.getConditions,
          [page.alias]: page.conditions,
        });
      });
    },

    checkPageFieldsConditions(): boolean {
      const fieldStore = useFieldsStore();
      const results: boolean[] = [];
      const activePage = fieldStore.getActivePage;

      const pageConditionType = activePage.condition;
      if (Array.isArray(activePage.conditions)) {
        activePage.conditions.forEach((conditionRule: IConditionRule) => {
          const triggerField = fieldStore.getField(conditionRule.field);
          if (!triggerField) return false;
          const conditionResult = this.checkFieldCondition(
            triggerField,
            conditionRule,
          );
          results.push(conditionResult);
        });
      }

      let result = false;

      if (pageConditionType === "and") {
        result = results.every((result) => result);
      } else if (pageConditionType === "any") {
        result = results.some((result) => result);
      }

      if (activePage.action === "not_skip") {
        this.setDisableNextButton(result);
      }

      return result;
    },

    checkFieldCondition(
      triggerField: Field,
      conditionRule: IConditionRule,
    ): boolean {
      let conditionResult: boolean = false;
      const fieldValue: number | undefined = triggerField.value;
      switch (conditionRule.condition) {
        case "==":
          if (
            singleOptionsField.includes(triggerField.fieldName) &&
            "selectedOption" in triggerField &&
            !Array.isArray(triggerField.selectedOption)
          ) {
            const selectedIdx: string | undefined =
              triggerField?.selectedOption?.optionValue?.split("_")[1] ||
              undefined;

            if (selectedIdx === undefined) {
              conditionResult = false;
            } else {
              conditionResult =
                conditionRule.key === "any"
                  ? !!selectedIdx
                  : +selectedIdx === +conditionRule.key;
            }
          } else {
            conditionResult = fieldValue === +conditionRule.value;
          }
          break;
        case "!=":
          conditionResult = fieldValue !== +conditionRule.value;
          break;
        case ">=":
          conditionResult = Number(fieldValue || 0) > +conditionRule.value;
          break;
        case "<=":
          conditionResult = Number(fieldValue || 0) < +conditionRule.value;
          break;
        case "in":
          if (
            multipleOptionsField.includes(triggerField.fieldName) &&
            "selectedOption" in triggerField
          ) {
            const selectedOption: IOptions[] = Array.isArray(
              triggerField.selectedOption,
            )
              ? triggerField.selectedOption
              : [];

            const values: number[] =
              selectedOption?.map(
                (f: IOptions) => +f.optionValue?.split("_")[1],
              ) || [];

            conditionResult = Array.from(conditionRule.checkedValues).every(
              (idx: number) => values.includes(idx),
            );
          }

          break;
        case "not in":
          if (
            multipleOptionsField.includes(triggerField.fieldName) &&
            "selectedOption" in triggerField
          ) {
            const selectedOption = Array.isArray(triggerField.selectedOption)
              ? triggerField.selectedOption
              : [];

            const values: number[] =
              selectedOption.map(
                (f: IOptions) => +f.optionValue?.split("_")[1],
              ) || [];

            conditionResult = Array.from(conditionRule.checkedValues).every(
              (idx: number) => !values.includes(idx),
            );
          }

          break;
        case "contains":
          if (
            multipleOptionsField.includes(triggerField.fieldName) &&
            "selectedOption" in triggerField
          ) {
            const selectedOptions: IOptions[] = Array.isArray(
              triggerField.selectedOption,
            )
              ? triggerField.selectedOption
              : [];

            const values: number[] =
              selectedOptions.map(
                (f: IOptions) => +f.optionValue?.split("_")[1],
              ) || [];

            conditionResult =
              conditionRule.key === "any"
                ? values.length > 0
                : values.includes(Number(conditionRule.key));
          }

          break;
        default:
          conditionResult = false;
      }

      return conditionResult;
    },
  },
});
