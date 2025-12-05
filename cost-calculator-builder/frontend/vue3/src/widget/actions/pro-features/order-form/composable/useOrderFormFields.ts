import { toRefs } from "vue";
import { IFormField } from "@/widget/shared/types/fields";

export function useOrderFormFields(props: { field: IFormField }) {
  const { field } = toRefs(props);

  return {
    field,
  };
}
