import { computed } from "vue";
import { Field } from "@/widget/shared/types/fields";

export const useIsFieldHidden = () => {
  return computed(() => {
    return (field: Field) => {
      if (field?.hidden) {
        return !field.hidden;
      }

      if (field?.hideAndLeaveTotal) {
        return !field.hideAndLeaveTotal;
      }

      return true;
    };
  });
};
