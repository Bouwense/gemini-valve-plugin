import { Component } from "vue";

export interface AnalyticsPages {
  label: string;
  slug: string;
  icon: string;
  deleted?: boolean;
  component: Component;
  id: number;
}
