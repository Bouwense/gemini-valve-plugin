<template>
  <div class="ccb-user-location">
    <div class="ccb-user-location__input-wrapper">
      <button class="ccb-user-location__action" @click="showPopup">
        <span class="icon">
          <i class="ccb-icon-location"></i>
        </span>
        <span class="label">{{
          translationsStore.getTranslations.openMap
        }}</span>
      </button>
      <div class="ccb-user-location__description">
        <span v-if="!addressName">{{
          translationsStore.getTranslations.chooseFromMap
        }}</span>
        <span v-else class="ccb-user-location__adressname">
          {{ addressName }}
        </span>
      </div>
    </div>
    <CCBPopup ref="popup">
      <div class="ccb-location-modal">
        <div class="ccb-location-modal__body">
          <div :id="randomID" class="ccb-location-modal__map"></div>
          <div class="ccb-location-modal__search">
            <i class="ccb-icon-Search-Magnifier"></i>
            <input
              :id="searchInputId"
              class="controls"
              type="text"
              :placeholder="translationsStore.getTranslations.searchLocation"
            />
          </div>
        </div>
        <div class="ccb-location-modal__footer">
          <CCBButton
            type="light"
            :text="translationsStore.getTranslations.clearLocation"
            :class="['clear', { disable: !addressName }]"
            :on-click="resetLocation"
          ></CCBButton>
          <CCBButton
            type="light"
            :text="translationsStore.getTranslations.cancel"
            :on-click="hidePopup"
          ></CCBButton>
          <CCBButton
            type="success"
            :text="translationsStore.getTranslations.saveLocation"
            :on-click="setLocation"
          ></CCBButton>
        </div>
      </div>
    </CCBPopup>
  </div>
</template>

<script setup lang="ts">
import CCBPopup from "@/widget/shared/ui/components/Popup/Popup.vue";
import CCBButton from "@/widget/shared/ui/components/Button/Button.vue";
import { computed, onMounted, ref, toRefs } from "vue";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import { IGeolocationField } from "@/widget/shared/types/fields";
import { useGeolocationFieldHelper } from "@/widget/actions/fields/composable/useGeolocationFieldHelper.ts";
import { Loader } from "@googlemaps/js-api-loader";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency.ts";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";

type Props = {
  field: IGeolocationField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const fieldStore = useFieldsStore();
const conditionsStore = useConditionsStore();
const singleFieldMixins = useSingleField();
const currencyInstance = useCurrency();
const translationsStore = useTranslationsStore();
const pageBreakerStore = usePageBreakerStore();

const popup = ref();

const {
  getGoogleApiKey,
  mapOptions,
  generateId,
  parseLocationCoordinates,
  calculateValueByEachCost,
  getMeasure,
  eachCost,
  getMarkerIconPath,
} = useGeolocationFieldHelper();

const loader = new Loader({
  apiKey: getGoogleApiKey(),
  version: "weekly",
  libraries: ["places", "geometry"],
});

// Location variables
let coordinates = ref({ lat: 51.554867, lng: -0.109112 });
let markers = ref<google.maps.marker.AdvancedMarkerElement[]>([]);
let addressName = ref("");
let randomID = generateId(4);
let distance = ref(0);
let distanceView = ref("");

let map: google.maps.Map;

// computed

const dataValue = computed(() => ({
  value: {
    pricingType: field.value.pricingType,
    eachCost: String(field.value.eachCost),
    distanceCostList: field.value.distanceCostList.map((costItem) => ({
      from: (costItem as any).from,
      to: (costItem as any).to,
      cost: String((costItem as any).cost),
    })),
    lastDistanceCost: {
      cost: String((field.value.lastDistanceCost as any)?.cost || "0"),
    },
  },
}));

const getUserLocation = computed(() => {
  return field.value.userLocation;
});

const getAdressnameView = computed(() => {
  return addressName.value.length > 40
    ? `${addressName.value.slice(0, 40)} + ...`
    : addressName.value;
});

const getDisplayValue = computed(() => {
  return field.value.useCurrency
    ? singleFieldMixins.getCommonFieldDisplayView(field.value)
    : field.value.value?.toString() || "";
});

const getDistanceView = computed(() => {
  const settings = currencyInstance.getCurrencyOptions(field.value);
  const currency = settings.currency;
  const position = settings.currencyPosition;
  let num = 0;
  if (field.value.value) {
    num = eachCost(distance.value, dataValue.value);
  }

  const formattedEachCost = currencyInstance.parseCurrencyPosition(
    num.toString(),
    currency,
    position || "left",
  );

  let cost = field.value.useCurrency ? `${formattedEachCost}` : num;
  return `${translationsStore.getTranslations.distance}: ${distance.value} ${getMeasure()} x ${cost}`;
});

const searchInputId = computed(() => {
  return `${field.value.alias}-search-input-${randomID}`;
});

// end computed

// methods
const initMap = () => {
  loader.load().then(async () => {
    const { Map } = (await google.maps.importLibrary(
      "maps",
    )) as google.maps.MapsLibrary;
    map = new Map(document.getElementById(randomID) as HTMLElement, {
      ...mapOptions(),
      mapId: randomID,
      center: {
        ...coordinates.value,
      },
    });

    const input = document.getElementById(
      searchInputId.value,
    ) as HTMLInputElement;
    const searchBox = new google.maps.places.SearchBox(input);

    map.addListener("bounds_changed", () => {
      searchBox.setBounds(map.getBounds() as google.maps.LatLngBounds);
    });

    searchBox.addListener("places_changed", () => {
      const places = searchBox.getPlaces();

      if (places?.length === 0) {
        return;
      }

      const bounds = new google.maps.LatLngBounds();

      places?.forEach((place: google.maps.places.PlaceResult) => {
        if (!place.geometry || !place.geometry.location) {
          return;
        }

        const latLngLiteral: google.maps.LatLngLiteral = {
          lat: place.geometry.location.lat(),
          lng: place.geometry.location.lng(),
        };

        setMarker(latLngLiteral);

        if (place.geometry.viewport) {
          bounds.union(place.geometry.viewport);
        } else {
          bounds.extend(place.geometry.location);
        }

        addressName.value = place.formatted_address || "";

        coordinates.value.lat = place.geometry.location.lat();
        coordinates.value.lng = place.geometry.location.lng();
      });

      map.fitBounds(bounds);
    });

    map.addListener("click", (event: google.maps.MapMouseEvent) => {
      const latLng = event.latLng;

      setMarker({
        lat: latLng?.lat() || 0,
        lng: latLng?.lng() || 0,
      });

      coordinates.value.lat = latLng?.lat() || 0;
      coordinates.value.lng = latLng?.lng() || 0;

      const geocoder = new google.maps.Geocoder();

      geocoder.geocode(
        { location: latLng },
        (
          results: google.maps.GeocoderResult[] | null,
          status: google.maps.GeocoderStatus,
        ) => {
          if (status === "OK") {
            if (Array.isArray(results) && results[0]) {
              addressName.value = results[0].formatted_address;
              input.value = results[0].formatted_address;
            }
          }
        },
      );
    });
  });
};

const clearAddressName = () => {
  addressName.value = "";
};

const showPopup = () => {
  popup.value.showPopup();
};

const hidePopup = () => {
  popup.value.hidePopup();
};

const clearMarkers = () => {
  markers.value.forEach((marker) => {
    marker.map = null;
  });
  markers.value = [];
};

const setMarker = async (latLng: google.maps.LatLngLiteral) => {
  clearMarkers();

  const glyphImg = document.createElement("img");
  glyphImg.style.width = "40px";
  glyphImg.style.height = "40px";
  glyphImg.src = getMarkerIconPath();

  const { AdvancedMarkerElement } = (await google.maps.importLibrary(
    "marker",
  )) as google.maps.MarkerLibrary;

  const newMarker = new AdvancedMarkerElement({
    position: latLng,
    map: map,
  });

  if (getMarkerIconPath()) {
    newMarker.content = glyphImg;
  }

  markers.value.push(newMarker);
};

const resetLocation = () => {
  if (addressName.value) {
    clearAddressName();
    const input = document.getElementById(randomID);
    clearMarkers();

    if (input) {
      (input as HTMLInputElement).value = "";
    }
    resetValue();
    hidePopup();
  }
};

const calculateDistance = () => {
  const directionsService = new google.maps.DirectionsService();
  const userCoordinates = parseLocationCoordinates(
    getUserLocation.value.toString(),
  );
  const selectedCoordinates = coordinates.value;

  const waypoints = [
    { location: userCoordinates, stopover: true },
    { location: selectedCoordinates, stopover: true },
  ];

  const request = {
    origin: userCoordinates,
    destination: selectedCoordinates,
    waypoints: waypoints,
    travelMode: google.maps.TravelMode.DRIVING,
  };

  const measure = getMeasure();

  return new Promise((resolve) => {
    directionsService.route(
      request,
      (
        result: google.maps.DirectionsResult | null,
        status: google.maps.DirectionsStatus,
      ) => {
        if (status === "OK") {
          const distanceInMeters =
            result?.routes[0]?.legs?.reduce(
              (total: number, leg: google.maps.DirectionsLeg) => {
                return total + (leg?.distance?.value || 0);
              },
              0,
            ) || 0;

          let distance;
          if (measure === "miles") {
            distance = (distanceInMeters * 0.000621371).toFixed(2);
          } else {
            distance = (distanceInMeters * 0.001).toFixed(2);
          }
          resolve(distance);
        } else {
          // distancePopup = true;
        }
      },
    );
  });
};

const setLocation = async () => {
  if (addressName.value) {
    distance.value = Number(await calculateDistance());
    distanceView.value = `${calculateValueByEachCost(
      distance.value,
      dataValue.value,
    )} ${getMeasure()}`;
    updateValue();
    hidePopup();
  }
};

const resetValue = () => {
  field.value.value = 0;
  field.value.displayValue = "0";
  field.value.extraDisplayView = [];
  fieldStore.updateField(field.value.alias, field.value);
  conditionsStore.applyConditionForField(field.value.alias);
  if (
    fieldStore.getPageBreakEnabled &&
    fieldStore.getActivePage.action === "not_skip"
  ) {
    pageBreakerStore.checkPageFieldsConditions();
  }
};

const updateValue = () => {
  field.value.value =
    parseFloat(
      calculateValueByEachCost(distance.value, dataValue.value).toFixed(2),
    ) || 0;
  field.value.displayValue = getDisplayValue.value;
  field.value.extraDisplayView = [
    getAdressnameView.value.toString(),
    getDistanceView.value.toString(),
  ];
  fieldStore.updateField(field.value.alias, field.value);
  conditionsStore.applyConditionForField(field.value.alias);
  if (
    fieldStore.getPageBreakEnabled &&
    fieldStore.getActivePage.action === "not_skip"
  ) {
    pageBreakerStore.checkPageFieldsConditions();
  }
};

// end methods

onMounted(() => {
  if (getUserLocation.value) {
    coordinates.value = parseLocationCoordinates(
      getUserLocation.value.toString(),
    );
  }

  if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;

        coordinates.value = {
          lat: latitude,
          lng: longitude,
        };
        initMap();
      },
      () => {
        initMap();
      },
    );
  } else {
    initMap();
  }
});
</script>

<style lang="scss" scoped>
.ccb-user-location {
  .ccb-popup {
    z-index: 999;
  }

  @media (max-width: 768px) {
    .ccb-location-modal {
      &__footer {
        display: grid;
        grid-template-columns: 1fr 1fr;

        .ccb-button {
          &:first-child {
            width: 100%;
            grid-column: span 2;
          }
        }
      }
    }
  }

  @media (max-width: 400px) {
    .ccb-location-modal {
      &__footer {
        display: flex;
        flex-direction: column;
      }
    }
  }

  .ccb-location-modal__body {
    position: relative;
    @media (max-width: 768px) {
      max-height: 400px;
      width: 100%;
    }

    .ccb-location-modal__search {
      left: 16px;
      position: absolute;
      top: 18px;
      z-index: 99;

      i {
        left: 10px;
        position: absolute;
        top: 12px;
        font-size: 16px;
        color: var(--ccb-text-color);
        opacity: 0.7;
      }

      input {
        font-size: var(--ccb-fields-button-size);
        padding: 11px 11px 11px 35px;

        @media only screen and (max-width: 480px) {
          font-size: var(--ccb-mobile-fields-button-size);
        }

        &::placeholder {
          color: var(--ccb-text-color);
          opacity: 0.7;
        }
      }
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
    max-width: 296px;
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
