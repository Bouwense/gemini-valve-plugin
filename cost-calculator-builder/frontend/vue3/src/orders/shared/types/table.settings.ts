export interface ITableSettingsItem {
  value: boolean;
  sort_id: number;
  key: string;
  label: string;
}

export interface ITableSettings {
  order_id: ITableSettingsItem;
  type: ITableSettingsItem;
  email: ITableSettingsItem;
  date: ITableSettingsItem;
  calculator: ITableSettingsItem;
  payment: ITableSettingsItem;
  status: ITableSettingsItem;
  amount: ITableSettingsItem;
}
