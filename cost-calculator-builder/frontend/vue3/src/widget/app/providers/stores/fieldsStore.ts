import { inject } from "vue";
import { defineStore } from "pinia";
import {
  Field,
  IDefaultTotal,
  IGroupField,
  IFormulaField,
  IRepeaterField,
  ISourceField,
} from "@/widget/shared/types/fields";
import { useFields } from "@/widget/actions/fields/composable/useFields.ts";
import { useSettingsStore } from "./settingsStore";
import { useFieldsRequired } from "@/widget/actions/fields/composable/useFieldsRequired.ts";
import { useAppStore } from "./appStore";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";
import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore.ts";
import { usePaymentAfterSubmitStore } from "@/widget/app/providers/stores/paymentAfterSubmit";
import { useDiscounts } from "@/widget/actions/discounts/composable/useDiscounts.ts";
import { IPageBreakerField } from "@/widget/shared/types/fields/fields.type";
import { getNonce } from "@/widget/shared/utils/nonce.utils.ts";
import { handleCalcAnalyticsRequest } from "@/widget/shared/api/handlerCalcAnalytics.ts";
import { useTranslationsStore } from "./translationsStore";

interface IFieldsStore {
  fields: Map<string, Field>;
  pages: Map<string, Field>;
  defaultTotals: IDefaultTotal | null;
  activePageIndex: number;
  pageBreakEnabled: boolean;
  requiredFields: Field[];
  timeout: ReturnType<typeof setTimeout> | null;
  interacted: boolean;
}

const randomId = Math.random().toString(36).substring(2, 15);
const requiredFieldsInstance = useFieldsRequired();

export const useFieldsStore = () => {
  const appStore = useAppStore();
  let calcId = appStore.getCalcId || null;
  if (!calcId) {
    calcId = inject("calc_id") || null;
  }

  const storeId = `fieldsStore_${randomId}_${calcId}`;

  const store = defineStore(storeId, {
    state: (): IFieldsStore => ({
      fields: new Map(),
      pages: new Map(),
      defaultTotals: null,
      activePageIndex: 0,
      pageBreakEnabled: false,
      requiredFields: [],
      timeout: null,
      interacted: false,
    }),

    getters: {
      getPageIndex(): (alias: string) => number {
        return (alias: string) => {
          const pages = Array.from(this.pages.values());
          let index = pages.findIndex((page: Field) => page.alias === alias);
          return index;
        };
      },

      getActivePage(): IPageBreakerField {
        const activePageIndex = this.activePageIndex;
        const pages = Array.from(this.pages.values());
        return pages[activePageIndex] as IPageBreakerField;
      },

      getPages(): Field[] {
        return Array.from(this.pages.values());
      },

      getPageBreakEnabled(): boolean {
        return this.pageBreakEnabled;
      },

      getActivePageIndex(): number {
        return this.activePageIndex;
      },

      getRequiredFields(): Field[] {
        return this.requiredFields;
      },

      getFields(): Field[] {
        return Array.from(this.fields.values());
      },

      getPageBreakFields(): Field[] {
        const pages = Array.from(this.pages.values());
        if (!pages) {
          return [];
        }
        return pages.filter((f: any) => f.type === "page-break");
      },

      getSummaryList(): Field[] {
        const settings = useSettingsStore();
        const fields = Array.from(this.fields.values());

        if (Array.isArray(fields)) {
          const excludeFields = ["total", "line", "html"];
          let result: Field[] = [];
          fields.forEach((f: Field) => {
            if (f.fieldName.includes("repeater")) {
              f.addToSummary = true;
            }

            if (f.fieldName === "group" && "groupElements" in f && !f.hidden) {
              const groupField = f as IGroupField;
              groupField.groupElements.forEach(
                (groupFieldMap: Map<string, Field>) => {
                  Array.from(groupFieldMap.values()).forEach((field: Field) => {
                    if (
                      !excludeFields.includes(field.fieldName) &&
                      field.addToSummary
                    ) {
                      result.push(field);
                    }
                  });
                },
              );
              return;
            }

            if (excludeFields.includes(f.fieldName) || !f.addToSummary) {
              return;
            }
            result.push(f);
          });

          if (!settings.general?.hideEmpty) {
            result = result.filter((f) => {
              if ("selectedOption" in f && Array.isArray(f.selectedOption)) {
                return f.selectedOption.length > 0;
              } else if (
                "selectedOption" in f &&
                !Array.isArray(f.selectedOption) &&
                f.selectedOption
              ) {
                return f.selectedOption.optionValue;
              } else if (["validated_form", "text"].includes(f.fieldName)) {
                return f.displayValue;
              } else if (
                f.fieldName === "geolocation" &&
                "geoType" in f &&
                f.geoType === "multiplyLocation"
              ) {
                return f.displayValue;
              } else if (
                (f.fieldName === "datePicker" && f.displayValue.length > 0) ||
                (f.fieldName === "timePicker" && f.displayValue.length > 0)
              ) {
                return f.displayValue;
              } else {
                return f.value;
              }
            });
          }

          return result;
        }

        return [];
      },

      getTotalsList(): Field[] {
        const settings = useSettingsStore();

        const fields = Array.from(this.fields.values());

        if (Array.isArray(fields)) {
          let totals = fields?.filter((f: { fieldName: string }) => {
            return (
              f.fieldName === "total" ||
              (f.fieldName === "repeater" &&
                ((f as IRepeaterField).sumAllAvailable ||
                  (f as IRepeaterField).enableFormula))
            );
          });

          if (totals.length === 0) {
            return (this.defaultTotals ? [this.defaultTotals] : []) as Field[];
          }

          if (!settings.general?.hideEmpty) {
            totals = totals.filter((f) => f.value);
          }

          return totals;
        }

        if (this.defaultTotals) return [this.defaultTotals] as Field[];

        return [];
      },

      getDefaultTotals(): IDefaultTotal | null {
        return this.defaultTotals;
      },

      getInteracted(): boolean {
        return this.interacted;
      },
    },

    actions: {
      setRequiredFields(fields: Field[]): void {
        let result: Field[] = [];
        if (this.getPageBreakEnabled) {
          const pageBreakerFieldAliases: string[] =
            this.getActivePage?.groupElements?.map((element) => {
              if ("alias" in element) {
                return element.alias as string;
              }
              return "";
            }) || [];

          fields = fields.filter((field) =>
            pageBreakerFieldAliases.includes(field.alias),
          );

          for (const field of fields) {
            if (pageBreakerFieldAliases.includes(field.alias)) {
              result.push(field);
            }
          }
        } else {
          result = fields;
        }

        this.requiredFields = result;
      },

      updateActivePageIndex(index: number): void {
        this.activePageIndex = index;
      },

      initPageBreaker(dataFields: ISourceField[] = []) {
        for (const field of dataFields) {
          if (field.type === "page-break") {
            this.addPage(field as unknown as Field);
          }
        }
      },

      initFields(dataFields: ISourceField[] = []) {
        const fieldsInstance = useFields();
        const appStore = useAppStore();
        const proFields: string[] = [
          "radio_with_img",
          "dropDown_with_img",
          "checkbox_with_img",
          "file_upload",
          "multi_range",
          "geolocation",
          "datePicker",
          "timePicker",
          "repeater",
          "group",
          "validated_form",
        ];

        for (const field of dataFields) {
          const fieldName = field.alias.replace(/\_field_id.*/, "");
          if (!appStore.isProActive && proFields.includes(fieldName)) {
            continue;
          }

          if (field.type === "page-break" && field.groupElements) {
            field.groupElements.forEach((item) => {
              const itemFieldName = item.alias.replace(/\_field_id.*/, "");

              if (!appStore.isProActive && proFields.includes(itemFieldName)) {
                return;
              }

              if (item.type === "Group" && item.groupElements) {
                const createdField = fieldsInstance.addField(item);
                if (createdField) {
                  createdField.hidden = item.hidden;
                  this.fields.set(item.alias, createdField);
                }

                item.groupElements.forEach((inner_item) => {
                  const createField = fieldsInstance.addField(inner_item);
                  createdField.hidden = inner_item.hidden;
                  this.fields.set(inner_item.alias, createField);
                });
              } else {
                const createField = fieldsInstance.addField(item);
                this.fields.set(item.alias, createField);
              }
            });
            this.setPageBreakEnabled(true);
          } else if (field.type === "Group" && field.groupElements) {
            const createdField = fieldsInstance.addField(field);
            if (createdField) {
              createdField.hidden = field.hidden;
              this.fields.set(field.alias, createdField);
            }
            field.groupElements.forEach((item) => {
              if (item.type === "Total") {
                const createdField = fieldsInstance.addField(item);
                createdField.hidden = field.hidden;
                this.fields.set(item.alias, createdField);
              }
            });
          } else {
            const createdField = fieldsInstance.addField(field);
            if (createdField) {
              this.fields.set(field.alias, createdField);
            }
          }
        }

        this.recalculateTotals();
        fieldsInstance.triggerConditions();
      },

      updateField(
        alias: string,
        field: Field,
        fromCondition: boolean = false,
      ): void {
        const paymentAfterSubmitStore = usePaymentAfterSubmitStore();
        const settingsStore = useSettingsStore();
        const summaryDisplay = settingsStore.getFormSettings?.summaryDisplay;

        setTimeout(() => {
          if (!this.getInteracted && !fromCondition) {
            handleCalcAnalyticsRequest({
              action: "ccb_calc_interactions",
              nonce: getNonce("ccb_calc_interactions"),
              data: {
                calc_id: appStore.getCalcId || 0,
              },
            });

            this.setInteracted(true);
          }
        }, 1000);

        if (paymentAfterSubmitStore.getSubmit) {
          paymentAfterSubmitStore.setSubmit(false);
        }

        const orderFormStore = useOrderFormStore();

        if (!summaryDisplay?.enable) {
          orderFormStore.updateNextButton(false);
          orderFormStore.updateNextButtonStatus(true);
        }

        const fieldsRequired = useFieldsRequired();
        const fieldsArray = this.getFields;
        fieldsRequired.maybeStartValidation(fieldsArray);

        if (this.fields.has(alias)) {
          const submissionStore = useSubmissionStore();
          submissionStore.resetSubmissions();

          if (field.fieldName !== "total") {
            this.fields.set(alias, field);
          } else {
            const currencyFormatter = useCurrency();
            if (alias && field) {
              field.displayValue = currencyFormatter.formatCurrency(
                field.value || 0,
                currencyFormatter.getCurrencyOptions(field),
              );

              this.fields.set(alias, field);
            }
          }

          this.debouncedRecalculateTotals();
        } else if ("repeaterAlias" in field && field.repeaterAlias) {
          this.debouncedRecalculateTotals();
        }
      },

      debouncedRecalculateTotals() {
        if (this.timeout) clearTimeout(this.timeout);

        this.timeout = setTimeout(() => {
          this.recalculateTotals();
          this.updateFormulaHook();
        }, 300);
      },

      recalculateTotals() {
        const currencyFormatter = useCurrency();
        const translationsStore = useTranslationsStore();

        if (!this.getDefaultTotals) {
          this.setDefaultTotals({
            label: translationsStore.getTranslations.defaultTotal,
            fieldName: "total",
            value: 0,
            displayValue: currencyFormatter.formatCurrency(
              0,
              currencyFormatter.getCurrencyOptions(),
            ),
          });
        }

        const fieldsArray = this.getFields;
        const repeaterFields: IRepeaterField[] = fieldsArray.filter(
          (f: Field) => f.fieldName === "repeater",
        ) as IRepeaterField[];

        let repeaterValue = 0;
        for (let repeater of repeaterFields) {
          if (repeater.sumAllAvailable) {
            for (const repeaterElement of repeater.groupElements) {
              for (const field of repeaterElement.values()) {
                repeaterValue += field.value || 0;
              }
            }

            repeater.value = repeaterValue;
            repeater.originalValue = repeaterValue;
            repeater.displayValue = currencyFormatter.formatCurrency(
              repeaterValue,
              currencyFormatter.getCurrencyOptions(repeater),
            );
          } else {
            let i: number = 0;
            const repeaterFormulas: Record<string, string> = {};

            if (!repeater.originalFormula) {
              repeater.originalFormula = repeater.formula;
            }

            for (const repeaterElement of repeater.groupElements) {
              repeaterFormulas[i] = repeater.originalFormula;
              const fieldsAliasList =
                repeaterFormulas[i].match(/\w+_field_id_\d+/g) || [];

              fieldsAliasList.forEach((alias: string) => {
                for (const field of repeaterElement.values()) {
                  if (field.alias === alias) {
                    let value = field.value || 0;
                    repeaterFormulas[i] = repeaterFormulas[i].replace(
                      new RegExp("\\b" + alias + "\\b", "g"),
                      value.toString(),
                    );
                  }
                }
              });

              i++;
            }

            repeater.formula = Object.keys(repeaterFormulas)
              .map((key: string): string => `(${repeaterFormulas[key].trim()})`)
              .join(" + ");

            const res = this.validateTotal(eval(repeater.formula));
            repeater.originalValue = res;
            repeater.value = res;
            repeater = this.applyDiscounts(repeater) as IRepeaterField;
          }

          repeater.displayValue = currencyFormatter.formatCurrency(
            repeater.value || 0,
            currencyFormatter.getCurrencyOptions(repeater),
          );
          repeater.originalDisplayView = currencyFormatter.formatCurrency(
            repeater.originalValue || 0,
            currencyFormatter.getCurrencyOptions(repeater),
          );
        }

        const totalFields: IFormulaField[] = fieldsArray
          .filter((f: Field) => f.fieldName === "total")
          .sort((a: Field, b: Field) => a.id - b.id) as IFormulaField[];

        if (repeaterFields.length || totalFields.length) {
          const notTotalsIncludes: Record<string, IFormulaField> = {};
          const totalsIncludes: Record<string, IFormulaField> = {};

          totalFields.forEach((total) => {
            if (!total.originalFormula && total.formula) {
              total.originalFormula = total.formula;
            } else {
              total.formula = total.originalFormula;
            }

            const matches = total.formula.match(/total_field_id_\d+/g) || [];
            if (matches.length === 0) {
              notTotalsIncludes[total.alias] = total;
            } else {
              totalsIncludes[total.alias] = total;
            }
          });

          Object.values(notTotalsIncludes).forEach((total) => {
            this.evaluateFormula(total);
          });
          Object.keys(totalsIncludes).forEach((key) => {
            const total = totalsIncludes[key];
            this.evaluateFormula(total);
            if (!notTotalsIncludes[key]) {
              notTotalsIncludes[key] = total;
              delete totalsIncludes[key];
            }
          });
        } else {
          let result: Field[] = [];
          fieldsArray.forEach((el) => {
            if (!el.hidden || el.calculateHidden) {
              if ("groupElements" in el && Array.isArray(el.groupElements)) {
                el.groupElements.forEach((inner: Map<string, Field>) => {
                  const values = Array.from(inner.values()) as Field[];
                  values.forEach((value) => {
                    if (!result.some((r) => r.alias === value.alias)) {
                      result.push(value);
                    }
                  });
                });
              } else {
                if (!result.some((r) => r.alias === el.alias)) {
                  result.push(el);
                }
              }
            }
          });

          const total = result.reduce((acc, field) => {
            acc += field.value || 0;
            return acc;
          }, 0);

          if (this.getDefaultTotals) {
            this.getDefaultTotals.label =
              translationsStore.getTranslations.defaultTotal;
            this.getDefaultTotals.value = total;
            this.getDefaultTotals.displayValue =
              currencyFormatter.formatCurrency(
                total,
                currencyFormatter.getCurrencyOptions(),
              );
          }
        }
      },

      evaluateFormula(total: IFormulaField): void {
        const currencyFormatter = useCurrency();
        const fieldsMap: Map<string, Field> = this.fields;

        const mergedFieldsMap: Map<string, Field> = new Map();

        for (const [key, field] of fieldsMap) {
          if ("groupElements" in field) {
            for (const repeaterElement of field.groupElements) {
              mergedFieldsMap.set(key, field);
              for (const field of repeaterElement.values()) {
                mergedFieldsMap.set(field.alias, field);
              }
            }
          } else {
            mergedFieldsMap.set(key, field);
          }
        }

        const fieldsAliasList = total.formula.match(/\w+_field_id_\d+/g) || [];

        fieldsAliasList.forEach((alias: string) => {
          const field = mergedFieldsMap.get(alias);

          if (field) {
            let value =
              field.hidden && !field.calculateHidden ? 0 : field.value || 0;
            total.formula = total.formula.replace(
              new RegExp("\\b" + alias + "\\b", "g"),
              value.toString(),
            );
          }
        });

        let newValue = this.validateTotal(eval(total.formula));
        const oldValue = total.value;

        total.originalValue = newValue;
        total.value = newValue;
        total = this.applyDiscounts(total) as IFormulaField;
        total.displayValue = currencyFormatter.formatCurrency(
          total.value || 0,
          currencyFormatter.getCurrencyOptions(total),
        );
        total.originalDisplayView = currencyFormatter.formatCurrency(
          total.originalValue || 0,
          currencyFormatter.getCurrencyOptions(total),
        );

        fieldsMap.set(total.alias, total);

        if (total.fieldName !== "repeater" && newValue !== oldValue) {
          // const fieldsInstance = useFields();
          // fieldsInstance.triggerFieldCondition(total.alias);
        }
      },

      applyDiscounts(total: IFormulaField): IFormulaField {
        const discountStore = useDiscounts();
        return discountStore.applyDiscounts(total) as IFormulaField;
      },

      checkRequiredFields(): boolean {
        return !requiredFieldsInstance.hasRequiredFields(this.getFields);
      },

      checkPageRequiredFields(fields: Field[]): boolean {
        return !requiredFieldsInstance.hasRequiredFields(fields);
      },

      checkFieldRequired(field: Field): boolean {
        return requiredFieldsInstance.checkFieldRequired(field);
      },

      setDefaultTotals(totals: IDefaultTotal) {
        this.defaultTotals = totals;
      },

      updateFormulaHook(): void {
        setTimeout(() => {
          const appStore = useAppStore();

          const formulaList = this.getFields.filter(
            (f: Field) => f.fieldName === "total",
          );

          const repeaterList = this.getFields.filter(
            (f: Field) => f.fieldName === "repeater",
          );

          const formulas = [...formulaList, ...repeaterList];
          const event = new CustomEvent("ccb_formula_update", {
            detail: { formulas, calcId: appStore.getCalcId },
          });

          document.dispatchEvent(event);
        });
      },

      addPage(field: Field): void {
        this.pages.set(field.alias, field);
      },

      resetFields(): void {
        this.fields.forEach((field: Field) => {
          if ("value" in field && field?.value) {
            field.value = Number(field.defaultValue || 0);
          }
        });
      },

      getField(alias: string): Field | undefined {
        const result: Field[] = [];
        let fields = this.getFields;

        fields.forEach((el) => {
          if ("groupElements" in el && Array.isArray(el.groupElements)) {
            result.push(el);
            el.groupElements.forEach((inner: Map<string, Field>) => {
              result.push(...Array.from(inner.values()));
            });
          } else {
            result.push(el);
          }
        });

        return result.find((field) => field.alias === alias);
      },

      setPageBreakEnabled(bool: boolean): void {
        this.pageBreakEnabled = bool;
      },

      parseContactFormDescriptions(): string {
        const fieldsArray = this.getFields;
        const allFields: Field[] = [];

        const exceptions: string[] = ["total", "html", "line"];

        for (const field of fieldsArray) {
          if (exceptions.includes(field.fieldName)) {
            continue;
          }

          if (
            (field.fieldName === "datePicker" ||
              field.fieldName === "timePicker") &&
            field.displayValue !== ""
          ) {
            allFields.push(field);
            continue;
          }

          if (
            field.fieldName === "validated_form" &&
            field.displayValue !== ""
          ) {
            allFields.push(field);
          }

          if (
            (["text"].includes(field.fieldName) && field.displayValue === "") ||
            !field.value
          ) {
            continue;
          }

          if (field.fieldName === "repeater" && "groupElements" in field) {
            allFields.push(field);
            for (const repeaterElement of field.groupElements) {
              for (const field of repeaterElement.values()) {
                allFields.push(field);
              }
            }
          } else {
            allFields.push(field);
          }
        }

        let subtotal = "";
        for (const field of allFields) {
          if (!field.hidden) {
            subtotal += `\n${field.label} ${field.displayValue}`;
          }
        }

        return `${subtotal}\n`;
      },

      parseContactFormTotals(formulas: string[]): string {
        const fieldsArray = this.getFields;

        let subtotal = "";

        for (const field of fieldsArray) {
          if (formulas.includes(field.alias) && !field.hidden) {
            subtotal += `\n${field.label} ${field.displayValue}`;
          }
        }

        if (!subtotal) {
          const formula = fieldsArray.find(
            (f) => f.fieldName === "total" || f.fieldName === "repeater",
          );

          if (formula) {
            subtotal = `\n${formula.label} ${formula.displayValue}`;
          }
        }

        return subtotal;
      },

      setInteracted(interacted: boolean): void {
        this.interacted = interacted;
      },

      validateTotal(total: number): number {
        if (isNaN(total)) {
          return 0;
        }

        if (total === -Infinity) {
          return 0;
        }

        return total;
      },
    },
  });

  return store();
};
