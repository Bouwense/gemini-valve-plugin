<template>
  <div
    class="ccb-order-field ccb-order-dropdown"
    :class="{
      active: isBodyVisible,
      [`ccb-col-${field.fieldWidth}`]: true,
      'ccb-order-field--required': isRequired,
    }"
  >
    <div class="ccb-order-field__title">
      <RequiredHint
        v-if="isRequired"
        :text="translationsStore.getTranslations.requiredField"
      />
      <label class="ccb-order-field__label">
        {{ field.label }}
        <span class="ccb-order-required-mark" v-if="field.required">*</span>
      </label>
    </div>
    <component :is="getComponent" :field="field" @toggle="toggleHandler" />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, toRefs, ref } from "vue";
import { IFormField } from "@/widget/shared/types/fields";
import { useOrderFormFieldsRequired } from "@/widget/actions/pro-features/order-form/composable/useOrderFormFieldsRequired.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

type Props = {
  field: IFormField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const { checkFieldRequired } = useOrderFormFieldsRequired();
const translationsStore = useTranslationsStore();

const isBodyVisible = ref<boolean>(false);

const toggleHandler = (value: boolean): void => {
  isBodyVisible.value = value;
};

const getComponent = computed(() => {
  if (field.value?.attributes?.multiselect) {
    return defineAsyncComponent(
      () =>
        import(
          "@/widget/features/submission/order-form/fields/OrderDropdown/styles/MultiSelectDropDown.vue"
        ),
    );
  }

  return defineAsyncComponent(
    () =>
      import(
        "@/widget/features/submission/order-form/fields/OrderDropdown/styles/Default.vue"
      ),
  );
});

const isRequired = computed(() => {
  if (!field.value.required) return false;
  return checkFieldRequired(field.value);
});
</script>

<style lang="scss">
@use "@/styles/widget/_mixins.scss" as mixins;

.ccb-order-dropdown {
  position: relative;
  &__input {
    @include mixins.field;
  }

  &__input {
    display: grid;
    grid-template-columns: 1fr auto;
    justify-content: space-between;
    cursor: pointer;

    @media only screen and (max-width: 480px) {
      padding: 12px var(--ccb-mobile-field-side-indent);
      min-height: var(--ccb-mobile-field-button-height);
    }
  }

  &__icon {
    font-size: 8px;
    display: grid;
    place-items: center;
    transition: 300ms ease;
    color: color-mix(in srgb, var(--ccb-fields-bg-color) 75%, black);
  }

  &__list {
    margin-top: 5px;
    border-radius: 4px;
    background: #ffffff;
    border-color: transparent;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    max-height: 261px;
    position: absolute;
    top: 25px;
    left: 0;
    width: 100%;
    z-index: 100;
    visibility: hidden;
    opacity: 0;
    transition: 200ms ease;

    ul {
      list-style: none;
      padding: 0;
      margin: 0;
      background: var(--ccb-fields-bg-color);

      li {
        padding: 0;
        margin: 0;
        background: var(--ccb-fields-bg-color);
        color: var(--ccb-text-color);
        font-size: var(--ccb-field-size);
        font-weight: var(--ccb-field-weight);

        padding: 20px;
        border-bottom: 1px solid var(--ccb-fields-border-color);
        cursor: pointer;

        @media only screen and (max-width: 480px) {
          font-size: var(--ccb-mobile-field-size);
          font-weight: var(--ccb-mobile-field-weight);
        }

        &:hover {
          background: var(--ccb-fields-hover-color) !important;
        }
      }
    }
  }
  &.active {
    .ccb-order-dropdown__input {
      border-color: var(--ccb-accent-color);
    }
  }
}

.ccb-order-dropdown {
  &.active {
    .ccb-order-dropdown__list {
      visibility: visible;
      opacity: 1;
      top: 45px;
    }

    .ccb-order-dropdown__icon {
      transform: rotate(180deg);
    }
  }
}
</style>
