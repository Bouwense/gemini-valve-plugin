<template>
  <div
    class="ccb-order-field ccb-order-space"
    :class="{ [`ccb-col-${field.fieldWidth}`]: true }"
  ></div>
</template>

<script lang="ts" setup>
import { IFormField } from "@/widget/shared/types/fields";
import { toRefs } from "vue";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);
</script>

<style lang="scss">
.ccb-order-space {
  height: 40px;
}
</style>
