<template>
  <div class="ccb-admin-header">
    <div class="ccb-admin-header__left">
      <div class="ccb-admin-header__left-logo">
        <img :src="logo_url" alt="Logo" />
        <div class="ccb-admin-header__left-logo-text">
          <span class="ccb-admin-header__left-logo-text-title">
            Cost Calculator
          </span>
          <span class="ccb-admin-header__left-logo-text-version">
            Version {{ version }}
          </span>
        </div>
      </div>

      <div class="ccb-admin-header__left-navigation">
        <div
          class="ccb-admin-header__left-navigation-item"
          v-for="item in items"
          :key="item.key"
          :class="{ 'ccb-header-active': current_page === item.key }"
        >
          <a
            :href="item.link"
            class="ccb-admin-header__left-navigation-item-link"
          >
            <i :class="item.icon"></i>
            <span class="ccb-admin-header__left-navigation-item-title">
              {{ item.title }}
            </span>
          </a>
        </div>
      </div>
    </div>
    <div class="ccb-admin-header__right"></div>
  </div>
</template>

<script setup lang="ts">
import { toRefs } from "vue";
import { IAdminMenu } from "@/common/shared/types/IAdminMenu";

const props = defineProps<{
  logo_url: string;
  version: string;
  items: IAdminMenu[];
  current_page: string;
}>();

const { logo_url, version, items, current_page } = toRefs(props);
</script>

<style scoped lang="scss">
.ccb-admin-header {
  display: flex;
  height: 60px;
  width: 100%;
  border-radius: 16px;
  background: #001931;
  justify-content: space-between;
  padding: 0 12px;

  &__left {
    display: flex;
    align-items: center;
    column-gap: 30px;

    &-logo {
      display: flex;
      align-items: center;
      column-gap: 10px;

      &-text {
        display: flex;
        flex-direction: column;
        row-gap: 5px;
        line-height: 1;

        &-title {
          color: #fff;
          font-size: 15px;
          font-weight: 600;
        }

        &-version {
          color: #fff;
          font-size: 13px;
          font-weight: 400;
        }
      }
    }

    &-navigation {
      display: flex;
      align-items: center;
      column-gap: 10px;
      height: 100%;
    }

    &-navigation-item {
      display: flex;
      align-items: center;
      column-gap: 10px;
      height: 100%;

      &-link {
        display: flex;
        height: 100%;
        align-items: center;
        padding: 0 20px;
        color: #fff;
        opacity: 0.7;
        transition: 200ms opacity ease-in-out;
        border-bottom: 3px solid transparent;
        text-decoration: none !important;
        outline: none !important;
        box-shadow: none !important;
        column-gap: 7px;
        line-height: 1;

        &:hover {
          opacity: 0.85;
        }

        i {
          font-size: 16px;
          font-weight: 500;
        }

        span {
          font-size: 14px;
          font-weight: 500;
        }
      }

      &.ccb-header-active {
        .ccb-admin-header__left-navigation-item-link {
          opacity: 1;
          border-bottom: 3px solid #00b163;
          background-color: #193046;
        }
      }
    }
  }

  &__right {
  }
}
</style>
