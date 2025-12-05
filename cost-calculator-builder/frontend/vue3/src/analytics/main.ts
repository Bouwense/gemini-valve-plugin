import { createApp, App as VueApp } from "vue";
import App from "@/analytics/app/App.vue";
import { createPinia } from "pinia";

import "@/styles/admin/global.scss";
import "@/styles/admin/analytics/styles.scss";

const documentElement = document.querySelector("#ccb-admin-app");
const calcId = documentElement?.getAttribute("data-calc-id") || null;
const demo = documentElement?.getAttribute("data-demo") || false;

const app: VueApp<Element> = createApp(App)
  .provide("calc_id", calcId)
  .provide("demo", demo);

const pinia = createPinia();
app.use(pinia);

app.mount("#ccb-admin-app");
