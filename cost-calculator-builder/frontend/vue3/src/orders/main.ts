import { createApp, App as VueApp } from "vue";
import App from "@/orders/app/App.vue";
import { createPinia } from "pinia";
import Vue3Toastify, { type ToastContainerOptions } from "vue3-toastify";

import "@/styles/admin/global.scss";
import "@/styles/admin/orders/styles.scss";
import "vue3-toastify/dist/index.css";

const documentElement = document.querySelector("#calculator_orders");
const calcId = documentElement?.getAttribute("data-calc-id") || null;
const demo = documentElement?.getAttribute("data-demo") || false;

const app: VueApp<Element> = createApp(App)
  .provide("calc_id", calcId)
  .provide("demo", demo);

const pinia = createPinia();
app.use(pinia);

app.use(Vue3Toastify, {
  autoClose: 3000,
} as ToastContainerOptions);

app.mount("#calculator_orders");
