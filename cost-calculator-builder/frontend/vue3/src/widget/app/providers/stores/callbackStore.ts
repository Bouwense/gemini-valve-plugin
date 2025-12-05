import { defineStore } from "pinia";

export const useCallbackStore = defineStore("callback_store", {
  state: () => ({
    callbacks: {} as Record<string, ((...args: any[]) => void)[]>,
  }),

  actions: {
    add(key: string, callback: (...args: any[]) => void) {
      if (!this.callbacks[key]) {
        this.callbacks[key] = [];
      }
      this.callbacks[key].push(callback);
    },

    remove(key: string) {
      delete this.callbacks[key];
    },

    runCallback(key: string, ...args: any[]) {
      if (this.callbacks[key]) {
        this.callbacks[key].forEach((callback) => callback(...args));
      }
    },
  },
});
