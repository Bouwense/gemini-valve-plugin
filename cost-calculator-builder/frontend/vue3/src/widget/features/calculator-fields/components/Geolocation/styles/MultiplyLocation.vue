<template>
  <div class="ccb-multiply-location">
    <div class="ccb-multiply-location__input-wrapper">
      <button class="ccb-multiply-location__action" @click="showPopup">
        <span class="icon">
          <i class="ccb-icon-location"></i>
        </span>
        <span class="label">{{
          translationsStore.getTranslations.openMap
        }}</span>
      </button>
      <div class="ccb-multiply-location__description">
        <span v-if="!field.selectedPoint.addressName">{{
          translationsStore.getTranslations.chooseFromMap
        }}</span>
        <span v-else class="ccb-multiply-location__adressname">
          {{ field.selectedPoint.addressName }}
        </span>
      </div>
    </div>
    <CCBPopup ref="popup">
      <div class="ccb-location-modal">
        <div class="ccb-location-modal__body">
          <div class="ccb-location-modal__sidebar">
            <div class="ccb-location-modal__title">
              {{ translationsStore.getTranslations.selectLocation }}
            </div>
            <div class="ccb-location-modal__locations">
              <div
                v-for="location in multiplyLocations"
                :key="location.addressName"
                class="ccb-default-radio"
              >
                <label>
                  <input
                    v-model="field.selectedPoint"
                    type="radio"
                    :name="location.label"
                    :value="location"
                    :checked="
                      field.selectedPoint.addressName === location.addressName
                    "
                  />
                  <span class="ccb-radio-label">{{
                    location.label.length
                      ? location.label
                      : location.addressName
                  }}</span>
                </label>
              </div>
            </div>
          </div>
          <div :id="randomID" class="ccb-location-modal__map"></div>
        </div>
        <div class="ccb-location-modal__footer">
          <CCBButton
            type="light"
            :text="translationsStore.getTranslations.clearLocation"
            :class="['clear', { disable: disableClear }]"
            @click="resetLocation"
          ></CCBButton>
          <CCBButton
            type="light"
            :text="translationsStore.getTranslations.cancel"
            @click="hidePopup"
          ></CCBButton>
          <CCBButton
            type="success"
            :text="translationsStore.getTranslations.saveLocation"
            @click="setLocation"
          ></CCBButton>
        </div>
      </div>
    </CCBPopup>
  </div>
</template>

<script setup lang="ts">
import CCBPopup from "@/widget/shared/ui/components/Popup/Popup.vue";
import CCBButton from "@/widget/shared/ui/components/Button/Button.vue";
import {
  onMounted,
  ref,
  toRefs,
  defineProps,
  computed,
  watch,
  ComputedRef,
} from "vue";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import { IGeolocationField } from "@/widget/shared/types/fields";
import { useGeolocationFieldHelper } from "@/widget/actions/fields/composable/useGeolocationFieldHelper.ts";
import { Loader } from "@googlemaps/js-api-loader";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";

import { useCallbackStore } from "@/widget/app/providers/stores/callbackStore.ts";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";

type Props = {
  field: IGeolocationField & {
    selectedPoint: { addressName?: string; coordinates?: any; label: string };
    userSelectedOptions: {
      addressName?: string;
      coordinates?: any;
      addressLink?: string;
      measure?: string;
    };
  };
};

const fieldStore = useFieldsStore();
const conditionsStore = useConditionsStore();
const translationsStore = useTranslationsStore();
const callbackStore = useCallbackStore();
const pageBreakerStore = usePageBreakerStore();

const props = defineProps<Props>();
const { field } = toRefs(props);

const popup = ref();

interface IMultiplyLocation {
  addressName: string;
  label: string;
  coordinates: any;
}

const {
  getGoogleApiKey,
  mapOptions,
  generateId,
  parseLocationCoordinates,
  generateGoogleLink,
  getMeasure,
  getPickUpIconPath,
} = useGeolocationFieldHelper();

const loader = new Loader({
  apiKey: getGoogleApiKey(),
  version: "weekly",
  libraries: ["places", "geometry"],
});

// Variables
let randomID = generateId(4);
let disableClear = ref(true);

// Methods
const showPopup = () => {
  popup.value.showPopup();
};

const hidePopup = () => {
  popup.value.hidePopup();
};

let LocationMap: google.maps.Map;

const initMap = () => {
  let firsLocation = (field.value.multiplyLocations[0] as IMultiplyLocation)
    .coordinates;
  loader.load().then(() => {
    const $element = document.getElementById(randomID);
    if ($element) {
      LocationMap = new google.maps.Map($element, {
        ...mapOptions(),
        center: {
          ...parseLocationCoordinates(firsLocation),
        },
      });

      multiplyLocations.value.forEach((location) => {
        let settings: google.maps.MarkerOptions = {
          position: parseLocationCoordinates(location.coordinates),
          map: LocationMap,
        };

        if (getPickUpIconPath()) {
          settings.icon = {
            url: getPickUpIconPath(),
            scaledSize: new google.maps.Size(40, 40),
          };
        }

        new google.maps.Marker(settings);
      });
    }
  });
};

const resetLocation = () => {
  let firsLocation = (field.value.multiplyLocations[0] as IMultiplyLocation)
    .coordinates;
  field.value.selectedPoint = {
    addressName: "",
    coordinates: firsLocation,
    label: "",
  };
  disableClear.value = true;
  updateValue();
  hidePopup();
};

const setLocation = () => {
  if (field.value.selectedPoint.addressName) {
    disableClear.value = false;
    const userSelectedOptions = {
      addressName: field.value.selectedPoint.addressName,
      coordinates: parseLocationCoordinates(
        field.value.selectedPoint.coordinates,
      ),
      addressLink: generateGoogleLink(
        parseLocationCoordinates(field.value.selectedPoint.coordinates),
      ),
      measure: getMeasure(),
    };

    field.value.userSelectedOptions = userSelectedOptions;
    updateValue();
    hidePopup();
  } else {
    hidePopup();
  }
};

const getDisplayValue = computed(() => {
  let displayValue =
    field.value.selectedPoint.label ||
    field.value.selectedPoint.addressName ||
    "";

  return displayValue.length > 15
    ? displayValue.slice(0, 25) + "..."
    : displayValue;
});

const updateValue = (alias?: string, fromCondition?: boolean) => {
  if (alias && alias !== field.value.alias) {
    return;
  }

  field.value.displayValue = getDisplayValue.value;
  fieldStore.updateField(field.value.alias, field.value, fromCondition);
  conditionsStore.applyConditionForField(field.value.alias);

  if (
    fieldStore.getPageBreakEnabled &&
    fieldStore.getActivePage.action === "not_skip"
  ) {
    pageBreakerStore.checkPageFieldsConditions();
  }
};

callbackStore.add("updateGeolocation", (alias) => {
  setTimeout(() => {
    updateValue(alias, true);
  }, 100);
});

// computed
const multiplyLocations: ComputedRef<IMultiplyLocation[]> = computed(() => {
  return Array.isArray(field.value.multiplyLocations)
    ? (field.value.multiplyLocations as IMultiplyLocation[])
    : [];
});

watch(
  () => field.value.selectedPoint,
  (val) => {
    if (val && val.coordinates) {
      LocationMap.panTo(parseLocationCoordinates(val.coordinates));
      LocationMap.setZoom(15);
    }
  },
  { deep: true },
);

onMounted(() => {
  initMap();
});
</script>

<style lang="scss" scoped>
.ccb-multiply-location {
  @media (max-width: 540px) {
    .ccb-location-modal {
      &__body {
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        width: 100%;
        height: 400px;
      }

      &__map {
        width: 100% !important;
      }

      &__sidebar {
        height: 170px;
        box-sizing: border-box;
        width: 100% !important;
      }

      &__locations {
        height: 80%;
        overflow-x: auto;
      }

      &__inputs,
      &__distance,
      &__footer,
      &__sidebar {
        width: 100%;
      }

      &__footer {
        display: grid;
        grid-template-columns: 1fr 1fr;

        .ccb-button {
          &:first-child {
            grid-column: span 2;
          }

          &.clear {
            width: 100%;
          }
        }
      }
    }
  }

  @media (max-width: 820px) {
    .ccb-location-modal__sidebar {
      width: 32%;
      padding: 25px 17px;
    }

    .ccb-location-modal__body {
      width: 100%;
    }

    .ccb-location-modal__map {
      width: 75%;
    }
  }

  &__input-wrapper {
    display: flex;
    justify-content: flex-start;
    align-items: center;
  }

  &__action {
    background: var(--ccb-fields-color);
    color: var(--ccb-accent-color);
    font-size: var(--ccb-fields-button-size);
    font-weight: var(--ccb-fields-button-weight);
    border: var(--ccb-button-border) var(--ccb-button-border-style)
      var(--ccb-accent-color);
    border-radius: var(--ccb-button-border-radius);
    height: var(--ccb-field-button-height);
    padding: 0 15px 0 15px;
    cursor: pointer;
    transition: 300ms ease;
    margin-right: 14px;
    white-space: nowrap;
    @media only screen and (max-width: 480px) {
      min-height: var(--ccb-mobile-field-button-height);
      font-size: var(--ccb-mobile-fields-button-size);
      font-weight: var(--ccb-mobile-fields-button-weight);
    }

    &:hover {
      background: var(--ccb-accent-color);
      color: var(--ccb-fields-color);
    }

    .icon {
      margin-right: 5px;
    }
  }

  &__description {
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: 20px;
    color: var(--ccb-fields-description-color);
  }

  &__adressname {
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: 20px;
    color: var(--ccb-text-color);
  }
}
</style>
