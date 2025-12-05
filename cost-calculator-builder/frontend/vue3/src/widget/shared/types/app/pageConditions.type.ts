import { ConditionLogicalOperator, ConditionLogic } from "./conditions.type";
export interface IConditionRule {
  key: number | "any";
  value: string | number;
  condition: ConditionLogic;
  logicalOperator: ConditionLogicalOperator;
  checkedValues: number[];
  sort: number;
  field: string;
}

export interface ICondition {
  page: {
    field: string;
    condition: ConditionLogic;
    key: string;
    value: string;
  };
}

export interface IConditionList {
  condition: ICondition[];
  optionsTo: string;
  optionsFrom: string;
}

export interface IConditionsStore {}

export interface IConditionsResult {
  addConditions: (conditions: IConditionList[]) => void;
  checkPageFieldCondition: (alias: string) => void;
}
