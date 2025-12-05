import { computed, onMounted, ref, toRefs } from "vue";
import { IOptions, ISingleOptionsField } from "@/widget/shared/types/fields";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore";
import { colorFilter } from "@/widget/shared/utils/color-filter.utils";
export function useSingleOptionChildShared(
  props: {
    options: IOptions[];
    alias: string;
    current?: IOptions;
    repeater?: number;
    field: ISingleOptionsField;
  },
  emit: (event: "update", option: IOptions) => void,
) {
  const { options, alias, current, repeater } = toRefs(props);

  const optionValues = ref<string>("");

  const changeValue = () => {
    const found = options.value?.find(
      (f: IOptions) => f.optionValue === optionValues.value?.toString(),
    );
    emit("update", found as IOptions);
  };

  const getRandomId = () => {
    return Math.random().toString(36).substring(2, 15);
  };

  const getName = computed(() => {
    return typeof repeater?.value !== "undefined"
      ? `repeater_${repeater.value}_${alias.value}_${getRandomId()}`
      : `${alias.value}_${getRandomId()}`;
  });

  const getPriceValue = computed(() => {
    return (o: IOptions) => {
      const val = (o?.optionValue || "").split("_")[0];
      return val || 0;
    };
  });

  const getCurrentIndex = computed(() => {
    const index = (optionValues.value || "").split("_")[1];
    return index ? +index : "";
  });

  const getImageStyles = computed(() => {
    const appearanceStore = useAppearanceStore();
    const accentColor = appearanceStore.getAppearanceAccentColor;
    const svgColor = appearanceStore.getAppearanceSvgColor;
    if (svgColor && accentColor) {
      return colorFilter(accentColor);
    }

    return {};
  });

  onMounted(() => {
    if (current?.value) {
      optionValues.value = current.value?.optionValue || "";
    }
  });

  return {
    optionValues,
    changeValue,
    getName,
    current,
    getPriceValue,
    getCurrentIndex,
    getImageStyles,
  };
}
