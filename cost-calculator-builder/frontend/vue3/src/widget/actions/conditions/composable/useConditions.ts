import {
  Actions,
  ICondition,
  IConditionList,
  IConditionRule,
  IConditionsResult,
  IConditionsStore,
} from "@/widget/shared/types/app/conditions.type";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import {
  Field,
  FieldWithMultipleDisplayView,
  IOptions,
} from "@/widget/shared/types/fields";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useDatePickerFieldHelper } from "@/widget/actions/fields/composable/useDatePickerFieldHelper.ts";
import { useCallbackStore } from "@/widget/app/providers/stores/callbackStore.ts";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import { useFields } from "../../fields/composable/useFields";

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

function addConditions(rawConditions: IConditionList[]): void {
  const structuredConditions: IConditionsStore = {};

  rawConditions.forEach(({ optionsTo, optionsFrom, condition }) => {
    if (!structuredConditions[optionsFrom]) {
      structuredConditions[optionsFrom] = {};
    }

    if (!structuredConditions[optionsFrom][optionsTo]) {
      structuredConditions[optionsFrom][optionsTo] = [];
    }

    condition.forEach(({ action, conditions, setVal, setOptions, sort }) => {
      structuredConditions[optionsFrom][optionsTo].push({
        optionTo: optionsTo,
        optionFrom: optionsFrom,
        conditions,
        setVal,
        setOptions,
        action,
        sort,
      });
    });
  });

  const conditionsStore = useConditionsStore();
  conditionsStore.setConditions(structuredConditions);
}

function triggerCondition() {
  const appStore = useAppStore();
  if (!appStore.isProActive) {
    return;
  }

  const fieldsInstance = useFields();
  for (const field of fieldsInstance.getFields()) {
    applyConditionForField(field.alias);
  }
}

function applyConditionForField(fieldAlias: string): void {
  const conditionsStore = useConditionsStore();

  if (!conditionsStore.conditions[fieldAlias]) return;

  const fieldStore = useFieldsStore();
  const singleFieldMixins = useSingleField();

  const affectedFields = Object.keys(conditionsStore.conditions[fieldAlias]);
  const triggerField: Field | undefined = fieldStore.getField(fieldAlias);

  if (!triggerField) return;

  for (const affectedAlias of affectedFields) {
    const fieldConditions =
      conditionsStore.conditions[fieldAlias][affectedAlias];
    let targetField: Field = fieldStore.getField(affectedAlias) as Field;
    if (!targetField) return;

    fieldConditions.forEach((condition: ICondition) => {
      const fieldValue: number | undefined = triggerField.value;

      const conditionResultData: Array<boolean | string> = [];
      condition.conditions.forEach(
        (conditionRule: IConditionRule, index: number) => {
          let conditionResult: boolean = false;

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
              if (
                singleOptionsField.includes(triggerField.fieldName) &&
                "selectedOption" in triggerField &&
                !Array.isArray(triggerField.selectedOption)
              ) {
                if (!triggerField.selectedOption) {
                  conditionResult = true;
                } else {
                  const selectedIdx: string | undefined =
                    triggerField?.selectedOption?.optionValue?.split("_")[1] ||
                    undefined;

                  if (selectedIdx === undefined) {
                    conditionResult = false;
                  } else {
                    conditionResult =
                      conditionRule.key === "any"
                        ? !!selectedIdx
                        : +selectedIdx !== +conditionRule.key;
                  }
                }
              } else {
                conditionResult = fieldValue !== +conditionRule.value;
              }
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
                const selectedOption = Array.isArray(
                  triggerField.selectedOption,
                )
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

          conditionResultData.push(conditionResult);
          if (
            condition.conditions.length > 1 &&
            index !== condition.conditions.length - 1
          ) {
            conditionResultData.push(conditionRule.logicalOperator);
          }
        },
      );

      let conditionResult: boolean = evaluateExpression(conditionResultData);

      if (triggerField.hidden && !triggerField.calculateHidden) {
        conditionResult = false;
      }

      switch (condition.action) {
        case "hide":
          targetField = hide(conditionResult, targetField, condition);
          break;
        case "show":
          targetField = show(conditionResult, targetField, condition);
          break;
        case "disable":
          targetField = disable(conditionResult, targetField, condition);
          break;
        case "unset":
          targetField = unset(conditionResult, targetField, condition);
          break;
        case "unset_option":
          targetField = unsetOption(conditionResult, targetField, condition);
          break;
        case "set_value":
          targetField = setValue(conditionResult, targetField, condition);
          break;
        case "set_value_and_disable":
          targetField = disable(
            conditionResult,
            targetField,
            condition,
            "set_value",
          );
          break;
        case "select_option":
          targetField = selectOption(conditionResult, targetField, condition);
          break;
        case "disable_option":
          targetField = disable(conditionResult, targetField, condition);
          break;
        case "select_option_and_disable":
          targetField = disable(
            conditionResult,
            targetField,
            condition,
            "select_option",
          );
          break;
        case "set_date":
          targetField = setDate(conditionResult, targetField, condition);
          break;
        case "set_date_and_disable":
          targetField = disable(
            conditionResult,
            targetField,
            condition,
            "set_date",
          );
          break;
        case "set_time":
          targetField = setTime(conditionResult, targetField, condition);
          break;
        case "set_period":
          targetField = setPeriod(conditionResult, targetField, condition);
          break;
        case "set_period_and_disable":
          targetField = disable(
            conditionResult,
            targetField,
            condition,
            "set_period",
          );
          break;
        case "set_time_and_disable":
          targetField = setTime(conditionResult, targetField, condition, true);
          break;
        case "set_location":
          targetField = setLocation(conditionResult, targetField, condition);
          break;
        case "set_location_and_disable":
          targetField = setLocation(
            conditionResult,
            targetField,
            condition,
            true,
          );
          break;
        case "hide_leave_in_total":
          targetField = hideAndLeaveTotal(
            conditionResult,
            targetField,
            condition,
          );
          break;
      }
    });

    if (
      [...multipleOptionsField, ...singleOptionsField].includes(
        targetField.fieldName,
      )
    ) {
      targetField.displayValue =
        singleFieldMixins.getMultipleOptionsFieldDisplayView(
          targetField as FieldWithMultipleDisplayView,
        );
    } else {
      if (
        !["datePicker", "validated_form", "timePicker", "text"].includes(
          targetField.fieldName,
        )
      ) {
        targetField.displayValue = singleFieldMixins.getCommonFieldDisplayView(
          targetField,
          targetField.value?.toString(),
        );
      }
    }

    fieldStore.updateField(targetField.alias, targetField, true);
  }
}

function setLocation(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
  disable?: boolean,
): Field {
  const callbackStore = useCallbackStore();
  if (result) {
    if ("selectedPoint" in targetField) {
      const pointIndex = targetCondition.setVal;
      if (typeof pointIndex === "undefined") return targetField;
      const data = targetField.multiplyLocations[Number(pointIndex)];
      targetField.selectedPoint = data;

      if (disable) {
        targetField.disabled = true;
      }

      callbackStore.runCallback("updateGeolocation", targetField.alias);
    }
  } else {
    targetField.disabled = false;
    if ("selectedPoint" in targetField) {
      targetField.selectedPoint = {
        addressName: "",
        addressLink: "",
        latitude: 0,
        longitude: 0,
      };
    }
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function hide(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  const cHistory = checkInConditionHistory(targetCondition);

  if (!targetField.hidden && result) {
    addConditionHistory(targetCondition);
    targetField.hidden = true;
    updateGroupElements(targetField);
  } else if (cHistory.length && !result) {
    removeFromConditionHistory(targetCondition);

    const conditionsStore = useConditionsStore();
    const check = conditionsStore.getConditionHistory.filter(
      (d) =>
        d.optionTo === targetCondition.optionTo &&
        d.action === targetCondition.action,
    );

    if (!check.length) {
      targetField.hidden = false;
    }

    updateGroupElements(targetField);

    setTimeout(() => {
      if (
        targetField.calculateHidden &&
        targetCondition.optionFrom !== targetCondition.optionTo
      ) {
        applyConditionForField(targetField.alias);
      }
    });
  }

  return targetField;
}

function hideAndLeaveTotal(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  const cHistory = checkInConditionHistory(targetCondition);
  if (result) {
    addConditionHistory(targetCondition);
    targetField.hideAndLeaveTotal = true;
  } else if (cHistory.length && !result) {
    const conditionsStore = useConditionsStore();
    removeFromConditionHistory(targetCondition);
    const check = conditionsStore.getConditionHistory.filter(
      (d) =>
        d.optionTo === targetCondition.optionTo &&
        d.action === targetCondition.action,
    );

    if (!check.length) {
      targetField.hideAndLeaveTotal = false;
    }
  }

  return targetField;
}

function show(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  const cHistory = checkInConditionHistory(targetCondition);
  if (result && !cHistory.length) {
    addConditionHistory(targetCondition);
    targetField.hidden = false;

    updateGroupElements(targetField);

    setTimeout(() => {
      if (targetCondition.optionFrom !== targetCondition.optionTo) {
        applyConditionForField(targetField.alias);
      }
    });
  } else if (cHistory.length && !result) {
    setTimeout(() => {
      if (targetCondition.optionFrom !== targetCondition.optionTo) {
        applyConditionForField(targetField.alias);
      }
    });

    removeFromConditionHistory(targetCondition);

    const conditionsStore = useConditionsStore();
    const check = conditionsStore.getConditionHistory.filter(
      (d) =>
        d.optionTo === targetCondition.optionTo &&
        d.action === targetCondition.action,
    );

    if (!check.length) {
      targetField.hidden = true;
    }

    updateGroupElements(targetField);
  }

  return targetField;
}

function disable(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
  action?: Actions,
): Field {
  const cHistory = checkInConditionHistory(targetCondition);
  const callbackStore = useCallbackStore();

  if (result && !cHistory.length) {
    if (
      singleOptionsField.includes(targetField.fieldName) &&
      targetCondition.action === "disable_option"
    ) {
      conditionHistoryLeaveOnlyCurrent(targetCondition);
    }

    addConditionHistory(targetCondition);
    if (targetCondition.action === "disable_option") {
      const disableOptions: number[] = [];
      const optionsFromCondition: string[] =
        targetCondition.setVal?.toString()?.split(",") || [];

      optionsFromCondition.forEach((option: string) => {
        disableOptions.push(+option);
      });

      if ("disableOptions" in targetField) {
        targetField.disableOptions = disableOptions;
        callbackStore.runCallback(
          "updateDisableOptions",
          targetField.alias,
          targetField.disableOptions,
        );
      }
    } else {
      targetField.disabled = true;
    }

    if (action) {
      switch (action) {
        case "set_value":
          targetField = setValue(result, targetField, targetCondition);
          break;
        case "select_option":
          targetField = selectOption(result, targetField, targetCondition);
          break;
        case "set_period":
          targetField = setPeriod(result, targetField, targetCondition);
          break;
        case "set_date":
          targetField = setDate(result, targetField, targetCondition);
          break;
      }
    }
  } else if (cHistory.length && !result) {
    removeFromConditionHistory(targetCondition);

    if (
      targetCondition.action === "disable_option" &&
      "disableOptions" in targetField
    ) {
      targetField.disableOptions = [];
      callbackStore.runCallback(
        "updateDisableOptions",
        targetField.alias,
        targetField.disableOptions,
      );
    } else {
      const conditionsStore = useConditionsStore();
      const check = conditionsStore.getConditionHistory.filter(
        (d) =>
          d.optionTo === targetCondition.optionTo &&
          d.action === targetCondition.action,
      );

      if (!check.length) {
        targetField.disabled = false;
      }
    }
  }

  return targetField;
}

function unset(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  const callbackStore = useCallbackStore();
  if (result) {
    targetField.value = fieldMinValue(targetField);
    if (
      multipleOptionsField.includes(targetField.fieldName) &&
      "selectedOption" in targetField
    ) {
      targetField.selectedOption = [];
    }

    if (
      singleOptionsField.includes(targetField.fieldName) &&
      "selectedOption" in targetField
    ) {
      targetField.selectedOption = undefined;
    }

    if (
      targetField.fieldName === "datePicker" &&
      "selectedDate" in targetField
    ) {
      targetField.selectedDate = undefined;
      callbackStore.runCallback(
        "updateDatePicker",
        targetField.selectedDate,
        targetField.alias,
        true,
      );
    }
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function unsetOption(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  if (result) {
    targetField.value = fieldMinValue(targetField);
    if (
      multipleOptionsField.includes(targetField.fieldName) &&
      "selectedOption" in targetField
    ) {
      const values: number[] =
        targetCondition.setVal
          ?.toString()
          ?.split(",")
          ?.map((n: string) => +n) || [];

      const selectedOption = targetField.options.filter(
        (_, idx: number) => !values.includes(idx),
      );

      targetField.selectedOption = selectedOption || [];
      let value: number = 0;
      for (const option of selectedOption) {
        value += +option.optionValue.split("_")[0] || 0;
      }

      targetField.value = +value;
    }
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function setValue(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  if (result) {
    const value: number = Number(targetCondition?.setVal || 0);
    const parseValue = prepareSetValue(targetField, value);

    if ("originalValue" in targetField) {
      targetField.originalValue = parseValue;
    }

    const callbackStore = useCallbackStore();

    if (result && targetField.fieldName === "quantity") {
      callbackStore.runCallback(
        "updateQuantity",
        true,
        parseValue,
        targetField.alias,
        true,
      );
    }

    if (result && targetField.fieldName === "range") {
      callbackStore.runCallback(
        "updateRange",
        parseValue,
        targetField.alias,
        true,
      );
    }
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function setPeriod(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  const callbackStore = useCallbackStore();

  if (result) {
    const data = JSON.parse(targetCondition?.setVal?.toString() || "{}");

    if (typeof data.start === "undefined" || typeof data.end === "undefined")
      return targetField;

    if (targetField.fieldName === "multi_range") {
      let value = 0;
      const { start, end } = data;

      if (end - start > 0) {
        value = end - start;
      }

      targetField.value = value;
      if ("values" in targetField) {
        targetField.values = [start, end];
        callbackStore.runCallback(
          "updateMultiRange",
          targetField.values,
          targetField.alias,
          true,
        );
      }
    } else if (targetField.fieldName === "datePicker") {
      const { parseDate } = useDatePickerFieldHelper();
      const { start, end } = data;
      const parsedStart = parseDate(start);
      const parsedEnd = parseDate(end);
      if ("selectedDate" in targetField) {
        targetField.selectedDate = [parsedStart, parsedEnd];
        callbackStore.runCallback(
          "updateDatePicker",
          targetField.selectedDate,
          targetField.alias,
        );
      }
    }
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function setTime(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
  disable?: boolean,
): Field {
  const callbackStore = useCallbackStore();

  if (result) {
    if ("range" in targetField && targetField.range) {
      const data = JSON.parse(targetCondition?.setVal?.toString() || "{}");

      callbackStore.runCallback(
        "updateRangeTimePicker",
        data,
        targetField.alias,
      );
    } else {
      callbackStore.runCallback(
        "updateSingleTimePicker",
        targetCondition.setVal,
        targetField.alias,
      );
    }

    if (disable) {
      targetField.disabled = true;
    }
  } else {
    targetField.disabled = false;
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function setDate(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  const callbackStore = useCallbackStore();
  if (result && targetField.fieldName === "datePicker") {
    if (
      ("range" in targetField && targetField.range) ||
      !targetCondition.setVal
    ) {
      return targetField;
    }

    const { parseDate } = useDatePickerFieldHelper();
    const date: Date = parseDate(targetCondition?.setVal?.toString());
    if ("selectedDate" in targetField) {
      targetField.selectedDate = date;
      callbackStore.runCallback(
        "updateDatePicker",
        targetField.selectedDate,
        targetField.alias,
      );
    }
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function selectOption(
  result: boolean,
  targetField: Field,
  targetCondition: ICondition,
): Field {
  if (result) {
    targetField.value = fieldMinValue(targetField);
    if (
      multipleOptionsField.includes(targetField.fieldName) &&
      "selectedOption" in targetField
    ) {
      targetField.selectedOption = [];

      const values: number[] =
        targetCondition.setVal
          ?.toString()
          ?.split(",")
          ?.map((n: string) => +n) || [];

      const options: IOptions[] = targetField.options.filter((_, idx: number) =>
        values.includes(idx),
      );
      targetField.selectedOption = options || [];

      let value: number = 0;
      if (options?.length) {
        const optionValues: number[] = options.map((o: IOptions) => {
          const optionValue: string = o.optionValue?.split("_")[0];
          return +optionValue || 0;
        });

        for (const val of optionValues) {
          value += val;
        }
      }

      targetField.value = +value;
    }

    if (
      singleOptionsField.includes(targetField.fieldName) &&
      "selectedOption" in targetField
    ) {
      targetField.selectedOption = undefined;
      const option = targetField.options.find(
        (_, idx: number) => idx === Number(targetCondition.setVal || 0),
      );
      targetField.selectedOption = option;

      let value: number | string = 0;
      if (option) {
        value = option.optionValue.split("_")[0] || 0;
      }

      targetField.value = +value;
    }
  }

  setTimeout(() => {
    if (
      (!targetField.hidden || targetField.calculateHidden) &&
      targetCondition.optionFrom !== targetCondition.optionTo
    ) {
      applyConditionForField(targetField.alias);
    }
  });

  return targetField;
}

function addConditionHistory(data: ICondition): ICondition[] {
  const conditionsStore = useConditionsStore();
  const conditionsHistory = conditionsStore.getConditionHistory;

  const check = checkInConditionHistory(data);

  if (!check.length) {
    conditionsHistory.push(data);
  }

  conditionsStore.updateConditionHistory(conditionsHistory);

  return conditionsHistory;
}

function removeFromConditionHistory(data: ICondition): ICondition[] {
  const conditionsStore = useConditionsStore();
  const conditionsHistory = conditionsStore.getConditionHistory;
  const idx = conditionsStore.getConditionHistory.findIndex(
    (d) =>
      d.optionTo === data.optionTo &&
      d.optionFrom === data.optionFrom &&
      d.action === data.action &&
      d.sort === data.sort,
  );

  if (idx !== -1) {
    conditionsHistory.splice(idx, 1);
  }

  conditionsStore.updateConditionHistory(conditionsHistory);

  return conditionsHistory;
}

function checkInConditionHistory(data: ICondition) {
  const conditionsStore = useConditionsStore();
  return conditionsStore.getConditionHistory.filter(
    (d) =>
      d.optionTo === data.optionTo &&
      d.optionFrom === data.optionFrom &&
      d.action === data.action &&
      d.sort === data.sort,
  );
}

function conditionHistoryLeaveOnlyCurrent(data: ICondition) {
  const conditionsStore = useConditionsStore();
  const conditionsHistory = conditionsStore.getConditionHistory;
  const indexesToRemove = [];
  for (let i = 0; i < conditionsHistory.length; i++) {
    const current = conditionsHistory[i];
    if (
      current.optionTo === data.optionTo &&
      current.optionFrom === data.optionFrom &&
      current.action === data.action &&
      current.sort !== data.sort
    ) {
      indexesToRemove.push(i);
    }
  }

  for (let i = indexesToRemove.length - 1; i >= 0; i--) {
    conditionsHistory.splice(indexesToRemove[i], 1);
  }

  conditionsStore.updateConditionHistory(conditionsHistory);
}

function evaluateExpression(expression: (boolean | string)[]): boolean {
  let result = expression[0] as boolean;

  for (let i = 1; i < expression.length; i += 2) {
    const operator = expression[i] as string;
    const nextValue = expression[i + 1] as boolean;

    if (operator === "||") {
      result = result || nextValue;
    } else if (operator === "&&") {
      result = result && nextValue;
    }
  }

  return result;
}

function fieldMinValue(field: Field): number {
  if ("minValue" in field) {
    return Number(field.minValue || 0);
  }

  if ("min" in field) {
    return +field.min;
  }
  return 0;
}

function prepareSetValue(field: Field, value: number) {
  const minValue: number = fieldMinValue(field);
  if (minValue && minValue > value) {
    return minValue;
  }

  const min: number = fieldMinValue(field);
  if (min && min > value) {
    return min;
  }

  return value;
}

function updateGroupElements(targetField: Field) {
  const fieldStore = useFieldsStore();
  if (targetField.fieldName === "group" && "groupElements" in targetField) {
    targetField.groupElements.forEach((elements, _) =>
      Array.from(elements.entries()).forEach(([_, element]) => {
        if (element.fieldName === "total") {
          element.hidden = targetField.hidden;
          fieldStore.updateField(element.alias, element, true);
        }
      }),
    );
  }
}

export function useConditions(): IConditionsResult {
  return { addConditions, applyConditionForField, triggerCondition };
}
