import { defineStore } from "pinia";

interface IMainStore {
  calcId: number | null;
}

export const useMainStore = defineStore("main_store", {
  state: (): IMainStore => ({
    calcId: null,
  }),

  getters: {
    getCalcId: (state: IMainStore): number | null => state.calcId,
  },

  actions: {
    setCalcId(id: number | null): void {
      this.calcId = id;
    },
  },
});
