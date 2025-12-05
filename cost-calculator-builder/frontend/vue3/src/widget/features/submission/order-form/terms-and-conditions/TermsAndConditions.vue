<template>
  <div
    class="ccb-default-checkbox ccb-terms-check"
    :class="{
      'ccb-terms-and-conditions-required': isRequired,
    }"
  >
    <div class="ccb-order-field__wrapper">
      <RequiredHint v-if="isRequired" :text="termsAndConditionsField" />

      <label for="terms_and_conditions">
        <input
          type="checkbox"
          id="terms_and_conditions"
          name="terms_and_conditions"
          v-model="termValue"
          :value="true"
        />
        <span class="ccb-checkbox-label">
          {{ getTitle }}
          <a :href="getPageLink" target="_blank">
            {{ getPageName }}
            <i class="ccb-icon-click-out"></i>
          </a>
        </span>
      </label>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { computed } from "vue";
import RequiredHint from "@/widget/shared/ui/components/Required-hint/RequiredHint.vue";

const settingStore = useSettingsStore();

const termValue = computed({
  get() {
    return settingStore.getFormSettings?.termsAndConditions?.checkbox || false;
  },
  set(value: boolean) {
    settingStore.updateTermsValue(value);
  },
});

const getTitle = computed(() => {
  return settingStore.getFormSettings?.termsAndConditions?.text || "";
});

const termsAndConditionsField = computed(() => {
  return (
    settingStore.getWarningTexts?.formFields?.termsAndConditionsField ||
    "Please, check out our terms and click on the checkbox"
  );
});

const isRequired = computed(() => {
  return settingStore.checkTermsAndConditionsApproved;
});

const getPageName = computed(() => {
  return settingStore.getFormSettings?.termsAndConditions?.linkText || "";
});

const getPageLink = computed(() => {
  return settingStore.getFormSettings?.termsAndConditions?.link || "";
});
</script>

<style scoped lang="scss">
.ccb-default-checkbox {
  grid-column: span 12;
}

.ccb-order-field__wrapper {
  gap: 5px;
}

.ccb-terms-check {
  input {
    &:focus {
      outline-width: 0;
    }
  }
}

.ccb-checkbox-label {
  a {
    margin-left: 0.3rem;
    margin-bottom: 1px;
    text-decoration: none;
    outline: none;
    box-shadow: none;
    color: var(--ccb-accent-color) !important;
    font-weight: 400;
  }
}

.ccb-terms-and-conditions-required {
  label {
    &:before {
      border-color: var(--ccb-error-color) !important;
    }
  }
}

.ccb-default-checkbox {
  display: flex;
  gap: 10px;
  font-size: var(--ccb-field-size);
  font-weight: var(--ccb-field-weight);
  color: var(--ccb-text-color);
  padding: 5px 0;

  @media only screen and (max-width: 480px) {
    font-size: var(--ccb-mobile-field-size);
    font-weight: var(--ccb-mobile-field-weight);
  }

  label {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    padding: 0;
    margin: 0;
    cursor: pointer;
    line-height: 1.4;
    width: fit-content;
    padding: 0 0 0 25px;
    position: relative;

    &:before {
      left: 0;
      content: "";
      max-width: 20px;
      min-width: 20px;
      max-height: 20px;
      min-height: 20px;
      height: 20px;
      width: 20px;
      position: absolute;
      margin: 0 !important;
      background-color: var(--ccb-field-bg-color);
      border: 2px solid var(--ccb-fields-border-color);
      transition: transform 0.28s ease;
      box-sizing: border-box;
      border-radius: 3px;
    }

    &:after {
      left: 5px;
      height: 5px;
      width: 10px;
      content: "";
      display: block;
      position: absolute;
      border-left: 2px solid #fff;
      border-bottom: 2px solid #fff;
      transition: transform ease 0.25s;
      transform: rotate(-45deg) scale(0) translateY(-10%);
      box-sizing: border-box;
    }

    input {
      display: none;
    }

    .ccb-checkbox-label {
      word-break: break-word;
    }
  }

  label:has(input[type="checkbox"]:checked):before {
    background: var(--ccb-accent-color);
    border-color: var(--ccb-accent-color);
  }

  label:has(input[type="checkbox"]:checked):after {
    transform: rotate(-45deg) scale(1) translateY(-10%);
  }
}
</style>
