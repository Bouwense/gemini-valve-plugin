<template>
  <button
    :class="['ccb-button', type, { disable: disabled }]"
    @click="clickHandler"
    v-if="!isLink"
  >
    <i :class="icon" v-if="icon && iconPosition === 'before'"></i>
    <span>{{ text }}</span>
    <i :class="icon" v-if="icon && iconPosition === 'after'"></i>
    <ProBadge v-if="isDemo" />
  </button>

  <a
    :class="['ccb-button ccb-button--link', type, { disable: disabled }]"
    @click="clickHandler"
    :href="href"
    v-else
  >
    <i :class="icon" v-if="icon && iconPosition === 'before'"></i>
    <span>{{ text }}</span>
    <i :class="icon" v-if="icon && iconPosition === 'after'"></i>
    <ProBadge v-if="isDemo" />
  </a>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";

type Props = {
  type: "success" | "light" | "danger" | "success-outlined";
  icon?: string;
  iconPosition?: "before" | "after";
  text: string;
  onClick: ((param?: unknown) => void) | (() => void);
  disabled?: boolean;
  isDemo?: boolean;
  isLink?: boolean;
  href?: string;
};

const props = defineProps<Props>();
const { type, text, onClick, icon, iconPosition, isDemo, href } = toRefs(props);

const clickHandler = () => {
  if (onClick.value) {
    onClick.value();
  }
};
</script>

<style lang="scss">
.ccb-button {
  padding: 10px 20px;
  outline: none;
  box-shadow: none;
  min-height: var(--ccb-field-button-height);
  border-radius: var(--ccb-fields-border-radius);
  font-size: var(--ccb-fields-button-size);
  font-weight: var(--ccb-fields-button-weight);

  span {
    margin: 0 6px;
    width: max-content;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  i {
    margin-left: 6px;
  }

  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: 200ms linear;

  &.disable {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
  }

  &.success {
    cursor: pointer;
    transition: 300ms ease;
    font-size: var(--ccb-fields-button-size);
    font-weight: var(--ccb-fields-button-weight);
    background: var(--ccb-accent-color);
    color: var(--ccb-fields-color);
    border: none;

    @media only screen and (max-width: 480px) {
      font-size: var(--ccb-mobile-fields-button-size);
      font-weight: var(--ccb-mobile-fields-button-weight);
    }

    &:hover {
      background: hsl(from var(--ccb-accent-color) h s calc(l - 5));
    }
  }

  &.success-outlined {
    cursor: pointer;
    transition: 300ms ease;
    font-size: var(--ccb-fields-button-size);
    font-weight: var(--ccb-fields-button-weight);
    background: var(--ccb-fields-bg-color);
    color: var(--ccb-accent-color);
    border: 2px solid var(--ccb-accent-color);

    @media only screen and (max-width: 480px) {
      font-size: var(--ccb-mobile-fields-button-size);
      font-weight: var(--ccb-mobile-fields-button-weight);
    }

    &:hover {
      background-color: var(--ccb-accent-color);
      color: #fff;
    }
  }

  &.light {
    background: var(--ccb-fields-bg-color);
    color: var(--ccb-text-color);
    border: 1px solid var(--ccb-fields-border-color);

    &:hover {
      background: hsl(from var(--ccb-fields-bg-color) h s calc(l - 10));
    }
  }

  &.danger {
    background: var(--ccb-fields-bg-color);
    color: var(--ccb-error-color);
    border: 1px solid var(--ccb-error-color);

    &:hover {
      background-color: color-mix(
        in srgb,
        var(--ccb-error-color) 15%,
        transparent
      );
    }
  }

  &--link {
    text-decoration: none;
    color: var(--ccb-text-color);
    padding: 0 8px;
  }

  @media only screen and (max-width: 480px) {
    min-height: var(--ccb-mobile-field-button-height);
    font-size: var(--ccb-mobile-fields-button-size);
  }
}
</style>
