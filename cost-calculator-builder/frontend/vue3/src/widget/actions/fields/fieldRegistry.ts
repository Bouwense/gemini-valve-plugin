import { defineAsyncComponent } from "vue";
export interface IFields {
  quantity: unknown;
  dropDown: unknown;
  text: unknown;
  radio: unknown;
  checkbox: unknown;
  dropDown_with_img: unknown;
  radio_with_img: unknown;
  checkbox_with_img: unknown;
  line: unknown;
  toggle: unknown;
  html: unknown;
  file_upload: unknown;
  range: unknown;
  multi_range: unknown;
  geolocation: unknown;
  datePicker: unknown;
  timePicker: unknown;
  repeater: unknown;
  group: unknown;
  validated_form: unknown;
}

export const fieldRegistry: IFields = {
  quantity: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Quantity"),
  ),
  dropDown: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Dropdown"),
  ),
  text: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Text"),
  ),
  radio: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Radio"),
  ),
  checkbox: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Checkbox"),
  ),
  radio_with_img: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/ImageRadio"),
  ),
  line: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Divider"),
  ),
  toggle: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Toggle"),
  ),
  html: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Html"),
  ),
  file_upload: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/FileUpload"),
  ),
  range: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Range/"),
  ),
  multi_range: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/MultiRange/"),
  ),
  dropDown_with_img: defineAsyncComponent(
    () =>
      import("@/widget/features/calculator-fields/components/ImageDropdown"),
  ),
  checkbox_with_img: defineAsyncComponent(
    () =>
      import("@/widget/features/calculator-fields/components/ImageCheckbox"),
  ),
  geolocation: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Geolocation"),
  ),
  datePicker: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/DatePicker"),
  ),
  timePicker: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/TimePicker"),
  ),
  repeater: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Repeater"),
  ),
  group: defineAsyncComponent(
    () => import("@/widget/features/calculator-fields/components/Group"),
  ),
  validated_form: defineAsyncComponent(
    () =>
      import("@/widget/features/calculator-fields/components/ValidatedForm"),
  ),
};
