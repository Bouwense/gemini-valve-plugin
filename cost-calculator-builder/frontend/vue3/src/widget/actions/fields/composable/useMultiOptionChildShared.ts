import { toRefs, computed, ref, onMounted, watch } from "vue";
import { IMultiOptionsField, IOptions } from "@/widget/shared/types/fields";
import { colorFilter } from "@/widget/shared/utils/color-filter.utils";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore";

export function useMultiOptionChildShared(
  props: {
    field: IMultiOptionsField;
    values: string[];
    repeater?: number;
  },
  emit: (event: "update", value: string) => void,
) {
  const { field, values } = toRefs(props);
  const optionValues = ref<string[]>([]);

  watch(
    () => props.values,
    (newOptions) => {
      optionValues.value = newOptions;
    },
  );

  const repeater = ref<number | undefined>(field.value.repeaterIdx);
  const alias = ref<string>(field.value.alias);

  const getName = computed(() => {
    return typeof repeater?.value !== "undefined"
      ? `repeater_${repeater.value}_${alias.value}`
      : alias.value;
  });

  const changeValue = () => {
    const optionsLength = optionValues.value.length;
    if (+field.value.maxAllowedOptions < optionsLength) {
      optionValues.value.shift();
    }

    const newValue: string = Object.values(optionValues.value).join(",");
    emit("update", newValue);
  };

  const getPriceValue = computed(() => {
    return (o: IOptions) => {
      const val = (o?.optionValue || "").split("_")[0];
      return val || 0;
    };
  });

  const selectedOptions = computed(() => {
    return (
      field.value.selectedOption?.map((f: IOptions) => f.optionValue) || []
    );
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

  const showPrice = computed(() => {
    return field.value.showValueInOption || false;
  });

  onMounted(() => {
    if (Array.isArray(values.value)) {
      optionValues.value = values.value;
    }
  });

  return {
    optionValues,
    changeValue,
    getName,
    getPriceValue,
    selectedOptions,
    getImageStyles,
    showPrice,
  };
}
