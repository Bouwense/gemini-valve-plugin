<template>
  <div
    class="ccb-field ccb-field-geolocation"
    :class="{
      required: isRequired,
      'ccb-field-disabled': field.disabled,
      [additionalClasses]: additionalClasses,
    }"
  >
    <div class="ccb-field__label">
      <RequiredHint :text="requiredWarningText" v-if="isRequired" />
      <div class="ccb-field__title">
        {{ field.label
        }}<span v-if="field.required" class="ccb-field-required-mark">*</span
        ><ProBadge />
      </div>
    </div>

    <div
      v-if="appearanceStore.getAppearanceDescriptionPosition === 'before'"
      class="ccb-field__descriptions ccb-before"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>

    <div class="ccb-field__input-wrapper">
      <component :is="currentComponents" :alias="field.alias" :field="field">
      </component>
    </div>

    <div
      v-if="appearanceStore.getAppearanceDescriptionPosition === 'after'"
      class="ccb-field__descriptions ccb-after"
    >
      <div v-if="field.description" class="ccb-field__description">
        {{ field.description }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { toRefs, computed, defineAsyncComponent } from "vue";
import { IGeolocationField } from "@/widget/shared/types/fields";
import { useAppearanceStore } from "@/widget/app/providers/stores/appearanceStore.ts";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

const fieldStore = useFieldsStore();

type Props = {
  field: IGeolocationField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const appearanceStore = useAppearanceStore();

const requiredWarningText = computed(() => {
  const settingStore = useSettingsStore();
  return settingStore.getWarningTexts?.requiredMsg || "";
});

const getLocationType = computed(() => {
  return field.value?.geoType;
});

const isRequired = computed(() => {
  return fieldStore.checkFieldRequired(field.value);
});

const currentComponents = computed(() => {
  const style = getLocationType;
  if (style.value === "userLocation") {
    return defineAsyncComponent(() => import("./styles/UserLocation.vue"));
  } else if (style.value === "twoPointLocation") {
    return defineAsyncComponent(() => import("./styles/TwoPoints.vue"));
  } else if (style.value === "multiplyLocation") {
    return defineAsyncComponent(() => import("./styles/MultiplyLocation.vue"));
  }

  return "";
});

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
.ccb-location-modal {
  border-radius: 10px;
  overflow: hidden;
  color: var(--ccb-text-color);

  &__map {
    width: 100%;
    height: 100%;
  }

  &__locations {
    .ccb-default-radio {
      display: flex;
      gap: 10px;
      font-size: var(--ccb-field-size);
      font-weight: var(--ccb-field-weight);
      color: var(--ccb-text-color);
      padding: 5px 0px;
      flex-direction: flex-start;
      min-width: 220px;

      @media only screen and (max-width: 480px) {
        font-size: var(--ccb-mobile-field-size);
        font-weight: var(--ccb-mobile-field-weight);
      }

      &.ccb-vertical-radio {
        flex-direction: column;
      }

      label {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        padding: 0;
        margin: 0;
        cursor: pointer;
        line-height: 1;
        width: fit-content;

        input {
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          outline: none;
          max-width: 20px;
          min-width: 20px;
          max-height: 20px;
          min-height: 20px;
          border-radius: 50%;
          position: relative;
          border: 2px solid var(--ccb-fields-border-color);
          background-color: var(--ccb-container-color);
          -webkit-appearance: none;
          margin-right: 7px;
          margin-top: 0;
          outline: none !important;
          box-shadow: none !important;
          box-sizing: border-box;
          padding: 0px;
          margin: 0px;
          margin-right: 7px;
          transition: 300ms ease;

          &[type="radio"]:checked {
            border-color: var(--ccb-accent-color);
            background-color: var(--ccb-accent-color);
          }

          &:before {
            content: "";
            width: 10px;
            min-width: 10px;
            height: 10px;
            display: block;
            border-radius: 50%;
            background-color: var(--ccb-container-color) !important;
          }
        }

        .ccb-radio-label {
          word-break: break-word;
        }
      }
    }
  }

  &__title {
    font-size: 20px;
    font-style: normal;
    font-weight: 700;
    line-height: normal;
    margin-bottom: 17px;
  }

  &__sidebar {
    padding: 30px;
    background: var(--ccb-fields-bg-color);
    display: flex;
    flex-direction: column;
  }

  &__input {
    margin-bottom: 30px;
    max-width: 215px;
  }

  &__inputs {
    label {
      cursor: pointer;
      font-size: 12px;
      font-style: normal;
      font-weight: 700;
      line-height: normal;
    }
  }

  &__distance {
    font-size: 14px;
    font-style: normal;
    font-weight: 400;
    line-height: 20px;
    margin-top: auto;

    span {
      font-weight: 600;
    }
  }

  &__body {
    max-width: 920px;
    height: 620px;
    width: 920px;
    background: transparent;
    display: flex;
  }

  &__footer {
    width: 100%;
    padding: 18px;
    background: var(--ccb-container-color);
    gap: 10px;
    display: flex;
    justify-content: flex-end;
    box-sizing: border-box;

    .clear {
      margin-right: auto;
    }

    button {
      padding: 10px 25px;
    }
  }
}
</style>
