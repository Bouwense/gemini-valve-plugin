import { createApp } from "vue";
import ThankYouPage from "@/widget/pages/thank-you-page/ThankYouPage.vue";
import { createPinia } from "pinia";
import "@/styles/widget/global.scss";

const rootElements: NodeListOf<Element> = document.querySelectorAll(
  ".ccb-thank-you-page",
);

rootElements.forEach((rootElement) => {
  const calcId = rootElement.getAttribute("data-calc-id");
  const app = createApp(ThankYouPage).provide("calc_id", calcId);

  const pinia = createPinia();
  app.use(pinia);

  app.mount(rootElement);
});
