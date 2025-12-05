import { createApp, ref, App as VueApp } from "vue";
import App from "@/sticky/app/App.vue";
import { createPinia } from "pinia";

const rootElements: NodeListOf<Element> =
  document.querySelectorAll(".ccb-sticky-calc");

rootElements.forEach((rootElement) => {
  const rootRef = ref<VueApp<Element> | null>(null);
  const app: VueApp<Element> = createApp(App).provide(
    "calc_id",
    rootElement.getAttribute("data-calc-id"),
  );
  rootRef.value = app;

  app.use(createPinia());
  app.mount(rootElement);
});
