import { defineStore } from "pinia";
import { convertKeysToCamelCase } from "@/widget/shared/utils/convert-case-to-camel-case.utils";
import { IAdminTranslations } from "@/analytics/shared/types/analytics/translations.type";

type TranslationsState = {
  translations: IAdminTranslations;
};

export const useAdminTranslationsStore = defineStore(
  "admin_translations_store",
  {
    state: (): TranslationsState => ({
      translations: {} as IAdminTranslations,
    }),
    actions: {
      initTranslations(translations: IAdminTranslations): void {
        this.translations = convertKeysToCamelCase(translations);
      },
    },
    getters: {
      getTranslations: (state: TranslationsState): IAdminTranslations =>
        state.translations,
    },
  },
);
