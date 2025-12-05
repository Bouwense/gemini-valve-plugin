import { createApp, ref, App as VueApp } from "vue";
import App from "@/widget/app/App.vue";
import { createPinia } from "pinia";
import "@/styles/widget/global.scss";

const rootElements: NodeListOf<Element> =
  document.querySelectorAll(".ccb-main-widget");

rootElements.forEach((rootElement) => {
  const rootRef = ref<VueApp<Element> | null>(null);
  const calcId = rootElement.getAttribute("data-calc-id");
  const app: VueApp<Element> = createApp(App).provide("calc_id", calcId);
  rootRef.value = app;

  const pinia = createPinia();
  app.use(pinia);

  app.mount(rootElement);
});
