<template>
  <div
    class="ccb-field"
    v-html="field.htmlContent"
    :class="{ [additionalClasses]: true }"
  ></div>
</template>

<script setup lang="ts">
import { toRefs, computed } from "vue";
import { IHtmlField } from "@/widget/shared/types/fields";

type Props = {
  field: IHtmlField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
.ccb-field {
  img {
    max-width: 100%;
    max-height: 100%;
    height: auto;
    width: auto;
    display: block;
  }
}
</style>
