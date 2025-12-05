export type Actions =
  | "hide"
  | "show"
  | "set_value"
  | "select_option"
  | "disable"
  | "disable_option"
  | "set_value_and_disable"
  | "select_option_and_disable"
  | "hide_leave_in_total"
  | "unset"
  | "set_date"
  | "set_date_and_disable"
  | "set_time"
  | "set_period"
  | "set_period_and_disable"
  | "set_location"
  | "set_location_and_disable"
  | "set_time_and_disable"
  | "unset_option";

export type ConditionLogic =
  | "=="
  | "!="
  | ">="
  | "<="
  | "in"
  | "contains"
  | "not in";

export type ConditionLogicalOperator = "&&" | "||";

export interface IConditionRule {
  key: number | "any";
  value: string | number;
  condition: ConditionLogic;
  logicalOperator: ConditionLogicalOperator;
  checkedValues: number[];
  sort: number;
}

export interface ICondition {
  optionTo: string;
  optionFrom: string;
  conditions: IConditionRule[];
  setVal?: string | number;
  setOptions?: any;
  action: Actions;
  sort: number;
}

export interface IConditionList {
  condition: ICondition[];
  optionsFrom: any;
  optionsTo: any;
}

export interface IConditionsStore {
  [optionFrom: string]: {
    [optionTo: string]: ICondition[];
  };
}

export interface IConditionsResult {
  addConditions: (conditions: IConditionList[]) => void;
  applyConditionForField: (alias: string) => void;
  triggerCondition: () => void;
}
