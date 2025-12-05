import { ref, toRefs, onUnmounted, Ref } from "vue";
import { IFormField } from "@/widget/shared/types/fields";

export function useOrderFormDropdown(
  props: { field: IFormField },
  emit: (event: "toggle", value: boolean) => void,
  dropdownWrapper: Ref<HTMLElement | null>,
) {
  const { field } = toRefs(props);
  const isBodyVisible = ref<boolean>(false);

  const handleClickOutside = (event: MouseEvent) => {
    if (
      dropdownWrapper.value &&
      "contains" in dropdownWrapper.value &&
      !dropdownWrapper.value.contains(event.target as Node)
    ) {
      isBodyVisible.value = false;
      emit("toggle", false);
    }
  };

  const toggleBody = () => {
    isBodyVisible.value = !isBodyVisible.value;
    emit("toggle", isBodyVisible.value);

    if (isBodyVisible.value) {
      document.addEventListener("click", handleClickOutside);
    } else {
      document.removeEventListener("click", handleClickOutside);
    }
  };

  onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
  });

  return {
    field,
    isBodyVisible,
    toggleBody,
  };
}
