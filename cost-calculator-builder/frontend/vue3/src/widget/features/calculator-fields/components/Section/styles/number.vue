<template>
  <div
    v-if="hasFields"
    class="ccb-section"
    :class="[{ collapsed }, effectiveStyle || field.styles?.style]"
  >
    <div class="ccb-section__header" v-if="showTitle" @click="toggleCollapse">
      <div class="ccb-section__header-left">
        <div class="ccb-section__icon" v-if="field.styles?.iconPath">
          <img :src="field.styles?.iconPath" alt="section icon" />
        </div>
        <h2
          class="ccb-section__title"
          :style="{ color: field.styles?.textColor }"
        >
          {{ field.label }}
        </h2>
      </div>
      <div class="ccb-section__actions" v-if="field.collapsible">
        <div class="ccb-section__actions-item">
          <i
            class="ccb-icon-Path-3398"
            :style="{ color: field.styles?.textColor }"
          ></i>
        </div>
      </div>
    </div>
    <div class="ccb-section__content">
      <Transition name="fade">
        <div class="ccb-section__body">
          <div class="ccb-section__fields" ref="fields">
            <template v-for="sectionField in field.fields">
              <template
                v-for="sectionElement in getFieldsFromMap(sectionField)"
              >
                <CalculatorField
                  v-if="
                    sectionElement.fieldName &&
                    !sectionElement.hidden &&
                    !sectionElement.hideAndLeaveTotal &&
                    !sectionElement.alias.includes('total')
                  "
                  :name="sectionElement.fieldName"
                  :field="sectionElement"
                  :key="sectionElement.alias"
                />
              </template>
            </template>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, toRefs } from "vue";
import { ISectionField, Field } from "@/widget/shared/types/fields";
import CalculatorField from "@/widget/features/calculator-fields/CalculatorField.vue";

type Props = {
  field: ISectionField;
  effectiveStyle?: string;
  hasFields?: boolean;
  collapsed: boolean;
  toggleCollapse: () => void;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const getFieldsFromMap = computed(() => {
  return (data: Map<string, Field> | [string, Field]): Field[] => {
    if (Array.isArray(data)) {
      return [data[1]];
    }
    return Array.from(data.values());
  };
});

const showTitle = computed(() => field.value.showName);
</script>

<style lang="scss" scoped>
.ccb-section.number {
  padding: 20px;
  background-color: var(--ccb-container-converted);
  backdrop-filter: var(--ccb-container-invert);
  -webkit-backdrop-filter: var(--ccb-container-invert);
  border-bottom: 1px solid #ddd;
  box-shadow: var(--ccb-container-shadow-x-offset)
    var(--ccb-container-shadow-y-offset) var(--ccb-container-shadow-blur)
    var(--ccb-container-shadow-color);
  counter-increment: ccb-section;
  border: var(--ccb-container-border) var(--ccb-container-border-style)
    var(--ccb-container-border-color);
  border-radius: var(--ccb-container-border-radius);

  .ccb-section__header-left {
    position: relative;
    padding-left: 40px;

    &::before {
      content: counter(ccb-section);
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background: #0f2a3d;
      color: #fff;
      font-weight: 700;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }

  &.collapsed {
    .ccb-section__header {
      margin-bottom: 0;
    }
    .ccb-section__content {
      display: none;
    }

    .ccb-section__actions {
      transform: rotate(180deg);
    }
  }

  .ccb-section__actions {
    transition: transform 0.3s ease-in-out;
  }

  .ccb-section__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;

    .ccb-section__header-left {
      display: flex;
      align-items: center;
      gap: 12px;

      .ccb-section__icon {
        width: 24px;
        height: 24px;
        display: flex;
        img {
          width: 100%;
        }
      }
      .ccb-section__title {
        font-size: 16px;
        font-weight: 700;
        color: var(--ccb-text-color);
        margin-bottom: 10px;
        margin-top: 10px;
      }
    }
  }

  .ccb-section__actions {
    cursor: pointer;
    .ccb-section__actions-item {
      display: flex;
      i {
        transform: rotate(270deg);
        color: var(--ccb-text-color, #fff);
      }
    }
    &:hover {
      color: var(--ccb-primary-color);
    }
  }
}
</style>
