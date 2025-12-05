import { toRefs } from "vue";
import { IValidatedFormField } from "@/widget/shared/types/fields/input.type";

export function useValidatedFormField(
  props: {
    field: IValidatedFormField;
  },
  emit: (event: "update", value: string) => void,
) {
  const { field } = toRefs(props);

  const onInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const value: string = target.value;
    emit("update", value);
  };

  return {
    field,
    onInput,
  };
}
