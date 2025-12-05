import { defineStore } from "pinia";
import { ITranslations } from "@/widget/shared/types/app/translations.type";
import { convertKeysToCamelCase } from "@/widget/shared/utils/convert-case-to-camel-case.utils";

type TranslationsState = {
  translations: ITranslations;
};

export const useTranslationsStore = defineStore("translations_store", {
  state: (): TranslationsState => ({
    translations: {} as ITranslations,
  }),

  actions: {
    initTranslations(translations: ITranslations): void {
      this.translations = convertKeysToCamelCase(translations);
    },
  },

  getters: {
    getTranslations: (state: TranslationsState): ITranslations =>
      state.translations,
  },
});
