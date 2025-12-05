import { defineStore } from "pinia";

export interface AppearanceStore {
  boxStyle: string;
  descriptionPosition: string;
  loaderType: "";
  accentColor: string;
  svgColor: string;
}

export const useAppearanceStore = defineStore("appearance", {
  state: (): AppearanceStore => ({
    boxStyle: "vertical",
    descriptionPosition: "after",
    accentColor: "",
    svgColor: "",
    loaderType: "",
  }),

  getters: {
    getAppearanceBoxStyle: (state: AppearanceStore): string => state.boxStyle,
    getAppearanceDescriptionPosition: (state: AppearanceStore): string =>
      state.descriptionPosition,
    getAppearanceLoaderType: (state: AppearanceStore): string =>
      state.loaderType,
    getAppearanceAccentColor: (state: AppearanceStore): string =>
      state.accentColor,
    getAppearanceSvgColor: (state: AppearanceStore): string => state.svgColor,
  },

  actions: {
    initAppearanceStore(data: AppearanceStore) {
      this.boxStyle = data.boxStyle;
      this.descriptionPosition = data.descriptionPosition;
      this.loaderType = data.loaderType;
      this.accentColor = data.accentColor;
      this.svgColor = data.svgColor;
    },
  },
});
