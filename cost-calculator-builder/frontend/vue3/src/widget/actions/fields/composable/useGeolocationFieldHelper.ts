import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";

import { ref } from "vue";

const useStore = useSettingsStore();

interface DataValue {
  pricingType: string;
  eachCost: string;
  distanceCostList: { from: number; to: number; cost: string }[];
  lastDistanceCost: { cost: string };
}

const getPickUpIconPath = () => {
  return useStore.geolocation
    ? useStore.geolocation.pickUpIconPath
    : "https://maps.gstatic.com/mapfiles/place_api/icons/v1/png_71/geocode-71.png";
};

const getMarkerIconPath = () => {
  return useStore.geolocation?.markerIconPath
    ? useStore.geolocation.markerIconPath
    : "";
};

const eachCost = (val: number, data: { value: DataValue }): number => {
  const pricingType = data.value.pricingType;
  const distance = parseFloat(val.toString());

  if (pricingType === "each") {
    return parseFloat(data.value.eachCost);
  } else if (pricingType === "fixed") {
    let cost = NaN;
    const costList = data.value.distanceCostList;
    const lastCost = data.value.lastDistanceCost;

    costList.forEach((costItem: { from: number; to: number; cost: string }) => {
      if (distance > costItem.from && distance < costItem.to) {
        cost = parseFloat(costItem.cost);
      }
    });

    if (isNaN(cost)) {
      cost = parseFloat(lastCost.cost);
    }

    return cost;
  }
  return 0; // Default return value
};

const calculateValueByEachCost = (
  val: number,
  data: { value: DataValue },
): number => {
  const distance = parseFloat(val.toString());
  const each = eachCost(distance, data);
  return parseFloat(parseFloat(each.toString()).toFixed(2)) * distance;
};

const getMeasure = (): string => {
  return useStore.geolocation ? useStore.geolocation.measure : "";
};

const getGoogleApiKey = (): string => {
  return useStore.geolocation ? useStore.geolocation.publicKey : "";
};

const mapOptions = (): object => {
  return {
    zoom: 12,
    streetViewControl: false,
    disableDefaultUI: true,
    zoomControl: true,
  };
};

const generateId = (length: number) => {
  let result = "";
  const characters = "0123456789abcdefghijklmnopqrstuvwxyz";
  const charactersLength = characters.length;
  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
};

const popup = ref(false);

const showPopup = () => {
  popup.value = true;
};

const hidePopup = () => {
  popup.value = false;
};

const generateGoogleLink = (coordinates: { lat: number; lng: number }) => {
  const baseUrl = "https://www.google.com/maps/place/";
  return baseUrl + coordinates.lat + "," + coordinates.lng;
};

const parseLocationCoordinates = (coordinates: string) => {
  const coordinatesString = coordinates;
  const [lat, lng] = coordinatesString.split(",").map(parseFloat);

  const coordinatesObject = { lat, lng };

  return coordinatesObject;
};

export function useGeolocationFieldHelper() {
  return {
    getGoogleApiKey,
    mapOptions,
    generateId,
    showPopup,
    hidePopup,
    calculateValueByEachCost,
    parseLocationCoordinates,
    getMeasure,
    eachCost,
    generateGoogleLink,
    getMarkerIconPath,
    getPickUpIconPath,
  };
}
