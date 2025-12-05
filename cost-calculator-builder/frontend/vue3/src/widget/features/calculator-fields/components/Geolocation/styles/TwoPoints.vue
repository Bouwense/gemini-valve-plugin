<template>
  <div class="ccb-two-points-location">
    <div class="ccb-two-points-location__input-wrapper">
      <button class="ccb-two-points-location__action" @click="showPopup">
        <span class="icon">
          <i class="ccb-icon-location"></i>
        </span>
        <span class="label">{{
          translationsStore.getTranslations.openMap
        }}</span>
      </button>
      <div class="ccb-two-points-location__description">
        <span v-if="!showInfo">{{
          translationsStore.getTranslations.chooseFromMap
        }}</span>
        <span v-else class="ccb-two-points-location__adressname">
          <span>{{ translationsStore.getTranslations.from }}:</span>
          {{ addressNames.from.addressName }} <br />
          <span>{{ translationsStore.getTranslations.to }}:</span>
          {{ addressNames.to.addressName }}
        </span>
      </div>
    </div>
    <CCBPopup ref="popup">
      <div class="ccb-location-modal">
        <div class="ccb-location-modal__body">
          <div class="ccb-location-modal__sidebar">
            <div class="ccb-location-modal__inputs">
              <div class="ccb-location-modal__input">
                <label for="latitude">{{
                  translationsStore.getTranslations.from
                }}</label>
                <input
                  :id="locationFromId"
                  type="text"
                  :placeholder="translationsStore.getTranslations.enterAddress"
                  v-model="addressNames.from.addressName"
                  @click="setActiveInput('from')"
                />
              </div>
              <div class="ccb-location-modal__input">
                <label for="longitude">{{
                  translationsStore.getTranslations.toDestination
                }}</label>
                <input
                  :id="locationToId"
                  v-model="addressNames.to.addressName"
                  type="text"
                  :placeholder="translationsStore.getTranslations.enterAddress"
                  @click="setActiveInput('to')"
                />
              </div>
            </div>
            <div class="ccb-location-modal__distance">
              {{ translationsStore.getTranslations.distance }}:
              <span>{{ `${distance} ${getMeasure()}` }}</span>
            </div>
          </div>
          <div :id="randomID" class="ccb-location-modal__map"></div>
        </div>
        <div class="ccb-location-modal__footer">
          <CCBButton
            type="light"
            :text="translationsStore.getTranslations.clearLocation"
            :class="['clear', { disable: !showInfo }]"
            @click="clearLocation"
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
import { useSingleField } from "@/widget/actions/fields/composable/useSingleField.ts";
import { useConditionsStore } from "@/widget/app/providers/stores/conditionsStore.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore.ts";
import { useCurrency } from "@/widget/actions/fields/composable/useCurrency.ts";
import { ref, toRefs, defineProps, computed, onMounted } from "vue";
import { IGeolocationField } from "@/widget/shared/types/fields";
import { useGeolocationFieldHelper } from "@/widget/actions/fields/composable/useGeolocationFieldHelper.ts";
import { Loader } from "@googlemaps/js-api-loader";
import { useTranslationsStore } from "@/widget/app/providers/stores/translationsStore";
import { usePageBreakerStore } from "@/widget/app/providers/stores/pageBreakerStore.ts";

type Props = {
  field: IGeolocationField;
};

const props = defineProps<Props>();
const { field } = toRefs(props);

const singleFieldMixins = useSingleField();
const conditionsStore = useConditionsStore();
const fieldStore = useFieldsStore();
const currencyInstance = useCurrency();
const translationsStore = useTranslationsStore();
const pageBreakerStore = usePageBreakerStore();

const popup = ref();

const {
  getGoogleApiKey,
  mapOptions,
  generateId,
  getMeasure,
  getMarkerIconPath,
  calculateValueByEachCost,
  eachCost,
  generateGoogleLink,
} = useGeolocationFieldHelper();

// Location Variables
let randomID = generateId(4);
let coordinates = ref({ lat: 51.554867, lng: -0.109112 });
let addressNames = ref({
  from: { addressName: "", coordinates: { lat: 0, lng: 0 } },
  to: { addressName: "", coordinates: { lat: 0, lng: 0 } },
});

let distance = ref<string>("");
let activeInput = ref<"from" | "to">("from");
let directionsRenderer = ref<google.maps.DirectionsRenderer | null>(null);
let markers = ref<{
  from: google.maps.marker.AdvancedMarkerElement | null;
  to: google.maps.marker.AdvancedMarkerElement | null;
}>({
  from: null,
  to: null,
});

const loader = new Loader({
  apiKey: getGoogleApiKey(),
  version: "weekly",
  libraries: ["places", "geometry"],
});

onMounted(() => {
  if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const latitude = position.coords.latitude;
        const longitude = position.coords.longitude;

        let result = {
          lat: latitude,
          lng: longitude,
        };

        coordinates.value = result;
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

// Methods

let map: google.maps.Map;

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

    const inputFrom = document.getElementById(
      locationFromId.value,
    ) as HTMLInputElement;
    const inputTo = document.getElementById(
      locationToId.value,
    ) as HTMLInputElement;

    const searchBoxFrom = new google.maps.places.SearchBox(inputFrom);
    const searchBoxTo = new google.maps.places.SearchBox(inputTo);

    searchBoxFrom.addListener("places_changed", () => {
      handlePlacesChanged(searchBoxFrom, "from");
    });

    searchBoxTo.addListener("places_changed", () => {
      handlePlacesChanged(searchBoxTo, "to");
    });

    google.maps.event.addListener(
      map,
      "click",
      (event: google.maps.MapMouseEvent) => {
        if (activeInput) {
          const position = {
            lat: event.latLng ? event.latLng.lat() : 0,
            lng: event.latLng ? event.latLng.lng() : 0,
          };

          addressNames.value[activeInput.value].coordinates = position;
          setMarker(activeInput.value, position);
          const inputId =
            activeInput.value === "from"
              ? locationFromId.value
              : locationToId.value;
          const geocoder = new google.maps.Geocoder();
          geocoder.geocode(
            { location: event.latLng },
            (
              results: google.maps.GeocoderResult[] | null,
              status: google.maps.GeocoderStatus,
            ) => {
              if (status === "OK" && results && results.length > 0) {
                const formattedAddress = results[0].formatted_address;

                document
                  .getElementById(inputId)
                  ?.setAttribute("value", formattedAddress);
                addressNames.value[activeInput.value].addressName =
                  formattedAddress;

                if (
                  addressNames.value.from.coordinates &&
                  addressNames.value.to.coordinates
                ) {
                  calculateAndDisplayRoute();
                }
              } else {
                console.error("Geocoding failed:", status);
              }
            },
          );
        }
      },
    );
  });
};

const setLocation = () => {
  if (showInfo.value) {
    updateValue();
  }
};

const updateValue = () => {
  field.value.value = parseFloat(
    calculateValueByEachCost(
      parseFloat(distance.value),
      dataValue.value,
    ).toFixed(2),
  );
  field.value.displayValue = getDisplayValue.value;
  field.value.extraDisplayView = [
    `${translationsStore.getTranslations.from}: ${
      addressNames.value.from.addressName.length > 30
        ? addressNames.value.from.addressName.slice(0, 30) + "..."
        : addressNames.value.from.addressName
    }`,
    `${translationsStore.getTranslations.toDestination}: ${
      addressNames.value.to.addressName.length > 34
        ? addressNames.value.to.addressName.slice(0, 34) + "..."
        : addressNames.value.to.addressName
    }`,
    `${getDistanceView.value}`,
  ];

  const userSelectedOptions = {
    distance: distance.value,
    distance_view: getDistanceView.value,
    measure: getMeasure(),
    eachCost: field.value.eachCost,
    twoPoints: {
      from: {
        addressName: addressNames.value.from.addressName,
        coordinates: addressNames.value.from.coordinates,
        addressLink: generateGoogleLink(addressNames.value.from.coordinates),
      },
      to: {
        addressName: addressNames.value.to.addressName,
        coordinates: addressNames.value.to.coordinates,
        addressLink: generateGoogleLink(addressNames.value.to.coordinates),
      },
    },
  };
  field.value.userSelectedOptions = userSelectedOptions;
  fieldStore.updateField(field.value.alias, field.value);
  conditionsStore.applyConditionForField(field.value.alias);

  if (
    fieldStore.getPageBreakEnabled &&
    fieldStore.getActivePage.action === "not_skip"
  ) {
    pageBreakerStore.checkPageFieldsConditions();
  }

  hidePopup();
};

const setActiveInput = (type: "from" | "to") => {
  activeInput.value = type;
};

const clearLocation = () => {
  field.value.userSelectedOptions = {};

  Object.values(markers.value).forEach(
    (marker: google.maps.marker.AdvancedMarkerElement | null) => {
      if (marker) {
        marker.map = null;
      }
    },
  );

  markers.value.from = null;
  markers.value.to = null;

  if (directionsRenderer.value !== null) {
    directionsRenderer.value.setMap(null);
  }

  distance.value = "0";
  addressNames.value = {
    from: { addressName: "", coordinates: { lat: 0, lng: 0 } },
    to: { addressName: "", coordinates: { lat: 0, lng: 0 } },
  };

  field.value.value = 0;
  field.value.displayValue = "";
  field.value.extraDisplayView = [];
  fieldStore.updateField(field.value.alias, field.value);
  conditionsStore.applyConditionForField(field.value.alias);

  if (
    fieldStore.getPageBreakEnabled &&
    fieldStore.getActivePage.action === "not_skip"
  ) {
    pageBreakerStore.checkPageFieldsConditions();
  }

  hidePopup();
};

const setMarker = async (
  type: "from" | "to",
  latLng: google.maps.LatLngLiteral,
) => {
  if (type === "from" && markers.value.from) {
    markers.value.from.map = null;
    markers.value.from = null;
  } else if (type === "to" && markers.value.to) {
    markers.value.to.map = null;
    markers.value.to = null;
  }

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

  if (type === "from") {
    markers.value.from = newMarker;
  } else {
    markers.value.to = newMarker;
  }
};

const handlePlacesChanged = (
  searchBox: google.maps.places.SearchBox,
  type: "from" | "to",
) => {
  const places = searchBox.getPlaces() || [];

  if (!Array.isArray(places) || places?.length === 0) {
    return;
  }

  const place = places[0];

  const position = {
    lat: place?.geometry?.location?.lat() || 0,
    lng: place?.geometry?.location?.lng() || 0,
  };

  const addressName = place.formatted_address;

  addressNames.value[type].coordinates = {
    lat: position.lat,
    lng: position.lng,
  };
  addressNames.value[type].addressName = addressName || "";

  if (
    addressNames.value.from.coordinates &&
    addressNames.value.to.coordinates
  ) {
    calculateAndDisplayRoute();
  }
};

const calculateAndDisplayRoute = async () => {
  try {
    const dist = await calculateDistance();
    distance.value = dist as string;

    if (directionsRenderer.value) {
      directionsRenderer.value.setMap(null);
      directionsRenderer.value = null;
    }

    const directionsService = new google.maps.DirectionsService();
    directionsRenderer.value = new google.maps.DirectionsRenderer({
      suppressMarkers: true,
    });

    if (directionsRenderer.value) {
      directionsRenderer.value.setMap(map);
    }

    const waypoints = [
      { location: addressNames.value.from.coordinates, stopover: true },
      { location: addressNames.value.to.coordinates, stopover: true },
    ];

    const fromAddress = new google.maps.LatLng(
      addressNames.value.from.coordinates,
    );
    const toAddress = new google.maps.LatLng(addressNames.value.to.coordinates);

    const request = {
      origin: fromAddress,
      destination: toAddress,
      waypoints: waypoints,
      travelMode: google.maps.TravelMode.DRIVING,
    };

    directionsService.route(
      request,
      (
        result: google.maps.DirectionsResult | null,
        status: google.maps.DirectionsStatus,
      ) => {
        if (status === "OK") {
          directionsRenderer.value?.setDirections(result);
          calculateDistance().then((dist: unknown) => {
            distance.value = dist as string;
          });
        }
      },
    );
  } catch (error) {
    throw error;
  }
};

const calculateDistance = () => {
  const directionsService = new google.maps.DirectionsService();

  const waypoints = [
    {
      location: new google.maps.LatLng(
        addressNames.value.from.coordinates.lat,
        addressNames.value.from.coordinates.lng,
      ),
      stopover: true,
    },
    {
      location: new google.maps.LatLng(
        addressNames.value.to.coordinates.lat,
        addressNames.value.to.coordinates.lng,
      ),
      stopover: true,
    },
  ];

  const request = {
    origin: new google.maps.LatLng(
      addressNames.value.from.coordinates.lat,
      addressNames.value.from.coordinates.lng,
    ),
    destination: new google.maps.LatLng(
      addressNames.value.to.coordinates.lat,
      addressNames.value.to.coordinates.lng,
    ),
    waypoints: waypoints,
    travelMode: google.maps.TravelMode.DRIVING,
  };

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
          if (getMeasure() === "miles") {
            distance = (distanceInMeters * 0.000621371).toFixed(2);
          } else {
            distance = (distanceInMeters * 0.001).toFixed(2);
          }
          resolve(distance);
        } else {
        }
      },
    );
  });
};

const showPopup = () => {
  popup.value.showPopup();
};

const hidePopup = () => {
  popup.value.hidePopup();
};

// Computed
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
    num = eachCost(parseFloat(distance.value), dataValue.value);
  }

  const formattedEachCost = currencyInstance.parseCurrencyPosition(
    num.toString(),
    currency,
    position || "left",
  );

  let cost = field.value.useCurrency ? `${formattedEachCost}` : num;
  return `${translationsStore.getTranslations.distance}: ${distance.value} ${getMeasure()} x ${cost}`;
});

const showInfo = computed(() => {
  return (
    addressNames.value.from.addressName &&
    addressNames.value.to.addressName &&
    distance.value
  );
});

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

const locationFromId = computed(() => {
  return `${field.value.alias}-locationFrom-${randomID}`;
});

const locationToId = computed(() => {
  return `${field.value.alias}-locationTo-${randomID}`;
});
</script>

<style lang="scss" scoped>
.ccb-two-points-location {
  @media (max-width: 1024px) {
    .ccb-location-modal__body {
      height: 500px;
    }
  }

  @media (max-width: 768px) {
    .ccb-location-modal {
      &__body {
        display: grid;
        grid-template-columns: 1fr;
        width: 100%;
      }

      &__sidebar {
        height: 232px;
        box-sizing: border-box;
      }

      &__input {
        margin-bottom: 5px;
        max-width: 100%;

        input {
          width: 100%;
        }
      }

      &__map {
        height: 268px;
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

  @media (max-width: 400px) {
    .ccb-location-modal__sidebar {
      height: 200px;
      padding: 17px;
    }

    .ccb-location-modal__body {
      height: 400px;
    }

    .ccb-location-modal__map {
      height: 201px;
    }

    .ccb-location-modal {
      &__footer {
        display: flex;
        flex-direction: column;
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
  }

  &__adressname {
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: 20px;
    color: var(--ccb-text-color);

    span {
      font-weight: 700;
    }
  }
}
</style>
