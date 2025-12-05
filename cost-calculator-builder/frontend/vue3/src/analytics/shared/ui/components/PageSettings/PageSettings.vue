<template>
  <div class="ccb-list-button-wrapper" ref="dropdownRef">
    <div class="ccb-list-button-block" @click="toggleList">
      <span class="ccb-list-button-value">{{ placeholder }}</span>
    </div>
    <div v-if="showList" class="ccb-list-button-dropdown">
      <div class="ccb-list-button-dropdown--header">
        <div class="ccb-list-button-dropdown--header-title">
          {{ placeholder }}
        </div>
      </div>

      <div class="ccb-list-button-dropdown--content">
        <div class="ccb-list-button-dropdown--content-tabs">
          <div class="ccb-list-button-dropdown--content-tabs-header">
            <div
              class="ccb-list-button-dropdown--content-tabs-header-item"
              :class="{ 'ccb-tab-selected': selectedTab === tab.value }"
              v-for="tab in tabs"
              :key="tab.value"
              @click="selectedTab = tab.value"
            >
              <div
                class="ccb-list-button-dropdown--content-tabs-header-item-title"
              >
                {{ tab.label }}
              </div>
            </div>
          </div>
          <div class="ccb-list-button-dropdown--content-tabs-content">
            <div class="ccb-list-button-dropdown--content-tabs-content-item">
              <div
                class="ccb-list-button-dropdown--content-tabs-content-item-title"
              >
                <SettingsToggle
                  :options="getOptions"
                  :name="selectedTab"
                  :key="selectedTab + '_' + updateCounter"
                  @update="updateSettings"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="ccb-list-button-dropdown--footer">
        <div class="ccb-list-button-dropdown--footer-restore">
          <span
            class="ccb-list-button-dropdown--footer-restore-text"
            @click="restoreDefaults"
          >
            {{ translations.enableAll }}
          </span>
        </div>

        <div class="ccb-list-button-dropdown--footer-actions">
          <span
            class="ccb-list-button-dropdown--footer-actions-text cancel"
            @click="cancelSettings"
          >
            {{ translations.cancel }}
          </span>
          <span
            class="ccb-list-button-dropdown--footer-actions-text apply"
            @click="applySettings"
          >
            {{ translations.ok }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  ref,
  computed,
  defineEmits,
  toRefs,
  onMounted,
  onUnmounted,
} from "vue";
import { SettingsToggle } from "@/analytics/shared/ui/components/SettingsToggle";
import { useAdminTranslationsStore } from "@/analytics/store/analytics/translationsStore";

const translationsStore = useAdminTranslationsStore();

const translations = computed(() => translationsStore.getTranslations);

const props = defineProps<{
  placeholder: string;
  items: Record<string, { key: string; title: string; value: boolean }[]>;
  tabs: { label: string; value: string }[];
}>();

const { items, placeholder, tabs } = toRefs(props);

const tempSettings = ref<Record<string, Record<string, boolean>>>({});
const selectedTab = ref<string>("");
const showList = ref<boolean>(false);
const updateCounter = ref<number>(0);

const emit = defineEmits<{
  (e: "update", value: Record<string, Record<string, boolean>>): void;
}>();

const dropdownRef = ref<HTMLElement | null>(null);

const toggleList = (): void => {
  showList.value = !showList.value;
};

const closeDropdown = (): void => {
  showList.value = false;
};

const handleClickOutside = (event: MouseEvent): void => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown();
  }
};

const getOptions = computed(() => {
  return items.value[selectedTab.value] || [];
});

const updateSettings = (key: string, value: boolean): void => {
  if (!tempSettings.value[selectedTab.value]) {
    tempSettings.value[selectedTab.value] = {};
  }

  tempSettings.value[selectedTab.value][key] = value;
};

const applySettings = (): void => {
  const result: Record<string, Record<string, boolean>> = {};
  for (const key in items.value) {
    for (const item of items.value[key]) {
      if (!result[key]) {
        result[key] = {};
      }

      if (tempSettings.value[key] && item.key in tempSettings.value[key]) {
        result[key][item.key] = tempSettings.value[key][item.key];
      } else {
        result[key][item.key] = item.value;
      }
    }
  }

  emit("update", result);
  showList.value = false;
  updateCounter.value++;
};

const cancelSettings = (): void => {
  tempSettings.value = {};
  showList.value = false;
  updateCounter.value++;
};

const restoreDefaults = (): void => {
  tempSettings.value = {};

  for (const key in items.value) {
    items.value[key].forEach((item) => {
      item.value = true;
    });
  }

  updateCounter.value++;
};

onMounted(() => {
  selectedTab.value = tabs.value[0].value;
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<style lang="scss" scoped>
.ccb-list-button-wrapper {
  position: relative;
}

.ccb-list-button-block {
  display: flex;
  height: 40px;
  padding: 0 25px;
  justify-content: center;
  align-items: center;
  border-radius: 4px;
  background: #eef1f7;
  cursor: pointer;
  user-select: none;
  transition: background 0.2s;

  &:hover {
    background: #e3e6ea;
  }
}

.ccb-list-button-value {
  flex: 1 1 auto;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
  color: #011a30;
  font-size: 14px;
  font-style: normal;
  font-weight: 700;
}

.ccb-list-button-dropdown {
  position: absolute;
  top: 100%;
  width: 100%;
  background: #fff;
  border: 1.5px solid #e3e6ea;
  border-radius: 12px;
  margin-top: 6px;
  box-shadow: 0 2px 8px 0 rgba(16, 30, 54, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 10;
  min-width: 360px;
  left: -100%;
  transform: translate(-19%, 0px);
  padding: 24px;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0px 5px 30px 0px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  row-gap: 24px;
  z-index: 12;

  &--header {
    &-title {
      color: #162432;
      font-size: 20px;
      font-style: normal;
      font-weight: 700;
    }
  }

  &--content {
    &-tabs {
      &-header {
        display: flex;
        border-bottom: 1px solid rgba(22, 36, 50, 0.05);

        &-item {
          flex: 1;
          padding: 12px 0;
          text-align: center;
          cursor: pointer;
          transition: background 0.2s;

          &:hover {
            background: rgba(22, 36, 50, 0.05);
          }

          &-title {
            overflow: hidden;
            color: rgba(22, 36, 50, 0.5);
            text-align: center;
            text-overflow: ellipsis;
            font-size: 13px;
            font-style: normal;
            font-weight: 500;
          }

          &.ccb-tab-selected {
            border-bottom: 3px solid #162432;
            .ccb-list-button-dropdown--content-tabs-header-item-title {
              color: #162432;
            }
          }
        }
      }

      &-content {
        padding-top: 24px;
      }
    }
  }

  &--footer {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .ccb-list-button-dropdown--footer-restore {
      cursor: pointer;

      &-text {
        color: rgba(22, 36, 50, 0.3);
        font-size: 12px;
        font-style: normal;
        font-weight: 500;
        line-height: 16px;
        transition: color 0.2s;

        &:hover {
          color: rgba(22, 36, 50, 0.5);
        }
      }
    }

    .ccb-list-button-dropdown--footer-actions {
      display: flex;
      align-items: center;
      column-gap: 10px;

      &-text {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        width: 72px;
        height: 32px;
        border-radius: 8px;
        border: 2px solid rgba(22, 36, 50, 0.05);
        color: rgba(22, 36, 50, 0.85);
        font-size: 14px;
        font-style: normal;
        font-weight: 500;

        &:hover {
          background: rgba(22, 36, 50, 0.05);
        }

        &.apply {
          background: #00b163;
          color: #fff;

          &:hover {
            background: #009153;
          }
        }
      }
    }
  }
}
</style>
