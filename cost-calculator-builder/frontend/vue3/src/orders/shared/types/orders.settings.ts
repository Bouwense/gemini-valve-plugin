export interface IOrdersSettingsItem {
  value: boolean;
  sort_id: number;
  key: string;
  label: string;
}

export interface IOrdersSettings {
  form_details: IOrdersSettingsItem;
  calculator_details: IOrdersSettingsItem;
  attachments: IOrdersSettingsItem;
  notes: IOrdersSettingsItem;
}
