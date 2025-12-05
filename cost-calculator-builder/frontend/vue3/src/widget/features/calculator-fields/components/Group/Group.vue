<template>
  <div
    class="ccb-group"
    :class="{ 'ccb-group-collapsed': collapsed, [additionalClasses]: true }"
    :data-accordion="accordion"
    :data-group-id="field.id"
  >
    <div class="ccb-group__header" @click="toggleCollapse">
      <div class="ccb-group__icon" v-if="collapsible">
        <i class="ccb-icon-Path-3514"></i>
      </div>
      <div class="ccb-group__label" v-if="showName">{{ groupLabel }}</div>
      <ProBadge />
    </div>
    <Transition name="fade">
      <div class="ccb-group__body" :class="{ collapsed: collapsed }">
        <div class="ccb-group__fields" ref="fields">
          <template v-for="groupFieldMap in field.groupElements">
            <template v-for="groupElement in getFieldsFromMap(groupFieldMap)">
              <CalculatorField
                :name="groupElement.fieldName"
                :field="groupElement"
              />
            </template>
          </template>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, toRefs, onMounted, nextTick } from "vue";
import CalculatorField from "@/widget/features/calculator-fields/CalculatorField.vue";
import { IGroupField, Field } from "@/widget/shared/types/fields";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";

type Props = {
  field: IGroupField;
};

onMounted(() => {
  if (collapsedDefault.value) {
    collapsed.value = true;
  }

  if (accordion.value) {
    collapsed.value = true;

    nextTick(() => {
      const groups = document.querySelectorAll(".ccb-group");
      const bodies = document.querySelectorAll(".ccb-group__body");

      bodies.forEach((body) => {
        body.classList.add("collapsed");
      });

      groups.forEach((group) => {
        group.classList.add("ccb-group-collapsed");
      });

      const firstGroup = groups[0] as HTMLElement;
      const firstGroupId = firstGroup?.dataset.groupId;

      if (
        firstGroupId !== undefined &&
        Number(firstGroupId) === field.value.id
      ) {
        firstGroup.classList.remove("ccb-group-collapsed");
        const firstBody = firstGroup.querySelector(".ccb-group__body");
        if (firstBody) {
          firstBody.classList.remove("collapsed");
        }
        collapsed.value = false;
      }
    });
  }
});

const props = defineProps<Props>();
const { field } = toRefs(props);
const collapsed = ref(false);

const groupLabel = computed(() => {
  return field.value.label ? field.value.label : "Group";
});

const getFieldsFromMap = computed(() => {
  return (data: Map<string, Field>): Field[] => {
    return Array.from(data.values());
  };
});

const showName = computed(() => {
  return field.value.showTitle;
});

const collapsible = computed(() => {
  return field.value.collapsible;
});

const accordion = computed(() => {
  return field.value.accordion;
});

const collapsedDefault = computed(() => {
  return field.value.collapse;
});

const toggleCollapse = () => {
  if (!collapsible.value) return;

  if (accordion.value) {
    const groups = document.querySelectorAll(".ccb-group");

    groups.forEach((groupEl) => {
      const group = groupEl as HTMLElement;
      const firstGroupId = group?.dataset.groupId;
      const body = group.querySelector(".ccb-group__body");

      if (
        firstGroupId !== undefined &&
        Number(firstGroupId) !== +field.value.id
      ) {
        group.classList.add("ccb-group-collapsed");
        body?.classList.add("collapsed");
        collapsed.value = true;
      } else {
        const isCurrentlyCollapsed = group.classList.contains(
          "ccb-group-collapsed",
        );

        if (isCurrentlyCollapsed) {
          group.classList.remove("ccb-group-collapsed");
          body?.classList.remove("collapsed");
          collapsed.value = false;
        } else {
          group.classList.add("ccb-group-collapsed");
          body?.classList.add("collapsed");
          collapsed.value = true;
        }
      }
    });
  } else {
    collapsed.value = !collapsed.value;
  }
};

const additionalClasses = computed(() => {
  return field.value?.additionalStyles || "";
});
</script>

<style lang="scss">
.ccb-group {
  overflow: hidden;
  border-bottom: 2px solid var(--ccb-container-dark-color);
  padding-bottom: 20px;

  &__fields {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 0 10px;
  }

  &__body {
    overflow: visible;
    transition:
      max-height 0.4s ease,
      opacity 0.4s ease;
    max-height: 5000px;
    opacity: 1;

    &.collapsed {
      max-height: 0;
      opacity: 0;
    }
  }

  &.ccb-group-collapsed {
    .ccb-group__icon {
      transform: rotate(180deg);
    }
  }

  &__header {
    color: var(--ccb-text-color);
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    margin-block: 10px;
  }

  &__label {
    font-size: var(--ccb-field-size);
    font-weight: var(--ccb-field-label-weight);
    color: var(--ccb-text-color);

    @media only screen and (max-width: 480px) {
      font-size: var(--ccb-mobile-field-size);
      font-weight: var(--ccb-mobile-field-label-weight);
    }
  }

  &__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: 300ms ease;
  }
}

.ccb-horizontal {
  .ccb_field_with_group {
    grid-column: 1 / span 2;
  }
}
</style>
