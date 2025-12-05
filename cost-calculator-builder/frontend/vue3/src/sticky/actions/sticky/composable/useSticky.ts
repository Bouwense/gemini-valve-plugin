import $ from "jquery";
import { computed, ref } from "vue";
import { useAppStore } from "@/sticky/app/providers/app.store.ts";
import { Modal } from "bootstrap";
import { StickyFormulas } from "@/sticky/shared/types/app";
import { useStickyCurrency } from "../../currency/composable/useStickyCurrency";
import { Field } from "@/widget/shared/types/fields";
import { CurrencyFormatOptions } from "@/widget/shared/types/common/currency.type";

type CCBCustomEvent = Event & {
  detail: {
    calcId: string | number;
    formulas: any[];
    isBanner: boolean;
    hide: boolean;
    position: string;
  };
};

export function useSticky() {
  const classList = ref<string[]>([]);
  const total = ref<number>(0);
  const isBannerVisible = ref<boolean>(false);
  const bannerPosition = ref<string>("");
  const formulas = ref<StickyFormulas[]>([]);
  const calcFormulas = ref<Field[]>([]);

  const appStore = useAppStore();

  const updateClassList = (newValue: string[]): void => {
    classList.value = newValue;
  };

  const updateTotal = (newValue: number): void => {
    total.value = newValue;
  };

  const updateBannerStatus = (newValue: boolean): void => {
    isBannerVisible.value = newValue;
  };

  const updateBannerPosition = (newValue: string): void => {
    bannerPosition.value = newValue;
  };

  const updateFormulas = (newValue: StickyFormulas[]): void => {
    formulas.value = newValue;
  };

  const updateCalcFormulas = (newValue: Field[]): void => {
    calcFormulas.value = newValue;
  };

  const initListeners = (): void => {
    if (appStore.getStickySettings?.oneClickAction === "scroll_to") {
      document.addEventListener("scroll", scrollHandler);
      const event: CustomEvent<unknown> = new CustomEvent("scroll", {});
      document.dispatchEvent(event);
    }

    document.addEventListener("ccb_formula_update", (event: Event): void => {
      updateFormulasHandler(event as CCBCustomEvent);
    });

    document.addEventListener("ccb_add_hide_class", (event: Event): void =>
      updateBannerVisibleHandler(event as CCBCustomEvent),
    );

    document.addEventListener("ccb_close_banner", (): void => {
      if (
        appStore.getStickySettings?.displayType === "banner" &&
        !classList.value.includes("ccb-hide-button")
      ) {
        classList.value.push("ccb-hide-button");
      }
    });

    document.addEventListener("ccb_clear_banner", (): void => {
      if (appStore.getStickySettings?.displayType === "banner") {
        const newValue = classList.value.filter(
          (c): boolean => c !== "ccb-hide-button",
        );
        updateClassList([...newValue]);
      }
    });
  };

  const scrollHandler = (): void => {
    const query = `.ccb-sticky-calc.ccb-calc-id-${appStore.getCalcId}`;
    const prevAll = $(query)?.prevAll() || [];
    const nextAll = $(query)?.nextAll() || [];

    let prevNotExist = true;
    let nextNotExist = true;

    prevAll?.each(function () {
      const el = $(this)[0];
      const pos = $(el).data("position");
      if (
        appStore.getStickySettings?.displayType === "btn" &&
        pos === appStore.getStickySettings.btnPosition
      ) {
        prevNotExist = false;
      }
    });

    nextAll?.each(function () {
      const el = $(this)[0];
      const pos = $(el).data("position");
      if (
        appStore.getStickySettings?.displayType === "btn" &&
        pos === appStore.getStickySettings.btnPosition
      ) {
        nextNotExist = false;
      }
    });

    let hide = classControlHandler(nextNotExist, prevNotExist);
    customBannerEvent(hide);
  };

  const updateFormulasHandler = (event: CCBCustomEvent): void => {
    if (+event?.detail?.calcId === appStore.getCalcId) {
      const action: string = appStore.getStickySettings?.oneClickAction || "";
      const formulas: Field[] = event?.detail?.formulas || [];
      let aliases: string[] =
        appStore.getStickySettings?.formulas?.map((f) => f.alias) || [];

      const calcFormulasNewValue: Field[] = formulas.filter((f) =>
        aliases.includes(f.alias),
      );
      updateCalcFormulas(calcFormulasNewValue);

      if (
        [
          "woo_checkout",
          "woo_product_as_modal",
          "woo_product_with_redirect",
        ].includes(action)
      ) {
        aliases =
          appStore.getWooCheckoutSettings?.formulas?.map((f) => f.alias) || [];
      }

      updateTotal(0);
      if (aliases?.length === 1 && aliases[0] === undefined) {
        const f: Field = formulas[0] || {};
        let value = 0;
        if ("total" in f) {
          value = f.total as number;
        } else if ("value" in f) {
          value = f.value as number;
        }

        updateTotal(value);
      } else {
        let total = 0;
        for (const formula of formulas) {
          if (aliases.includes(formula.alias)) {
            let t = 0;
            if ("total" in formula) {
              t = formula.total as number;
            } else if ("value" in formula) {
              t = formula.value as number;
            }

            total += t;
          }
        }
        updateTotal(total);
      }
    }
  };

  const updateBannerVisibleHandler = (event: CCBCustomEvent): void => {
    const { isBanner, hide, position } = event.detail;
    const isBannerVisibleValue = isBanner && hide;
    const bannerPositionValue = position;

    updateBannerStatus(isBannerVisibleValue);
    updateBannerPosition(bannerPositionValue);
  };

  const classControlHandler = (
    nextNotExist: boolean = true,
    prevNotExist: boolean = true,
  ): boolean => {
    const elem = document.querySelector(
      `.ccb-main-widget[data-calc-id="${appStore.getCalcId}"]`,
    );

    let hide = true;
    const rect = elem?.getBoundingClientRect();

    if (
      !(
        (rect?.bottom || 0) < (rect?.height || 0) - 120 ||
        (rect?.top || 0) > window.innerHeight - 300
      ) &&
      nextNotExist &&
      prevNotExist
    ) {
      if (!classList.value.includes("ccb-hide-button")) {
        updateClassList([...classList.value, "ccb-hide-button"]);
      }

      hide = false;
    } else {
      const newValue = classList.value.filter((c) => c !== "ccb-hide-button");
      updateClassList([...newValue]);
    }

    return hide;
  };

  const customBannerEvent = (hide: boolean): void => {
    const event = new CustomEvent("ccb_add_hide_class", {
      detail: {
        hide,
        isBanner: appStore.getStickySettings?.displayType !== "btn",
        position: appStore.getStickySettings?.bannerPosition,
      },
    });
    document.dispatchEvent(event);
  };

  const closeBanner = (): void => {
    const event: CustomEvent<unknown> = new CustomEvent("ccb_close_banner");
    document.dispatchEvent(event);
  };

  const clearBanner = (): void => {
    const event: CustomEvent<unknown> = new CustomEvent("ccb_clear_banner");
    document.dispatchEvent(event);
  };

  const openBanner = (): void => {
    const event: CustomEvent<unknown> = new CustomEvent("ccb_open_banner");
    document.dispatchEvent(event);
  };

  const clickAction = () => {
    const action: string = appStore.getStickySettings?.oneClickAction || "";
    if (action === "scroll_to") {
      scrollIntoCalc();
    } else if (["pdf", "invoice"].includes(action)) {
      if (action === "pdf") {
        window.dispatchEvent(new CustomEvent("ccbDownLoadPdf"));
      } else if (action === "invoice") {
        window.dispatchEvent(new CustomEvent("ccbOpenModal"));
      }
    } else if (
      ["open_modal", "woo_product_as_modal", "pro_features"].includes(action)
    ) {
      openModal();
    } else if (["woo_checkout", "woo_product_with_redirect"].includes(action)) {
      window.dispatchEvent(new CustomEvent("ccbWooCheckout"));
    }
  };

  const openModal = () => {
    const id = `#ccb-sticky-calc-modal-${appStore.getCalcId}`;
    const modalElement = document.querySelector(id);
    if (modalElement) {
      const modal = new Modal(modalElement);

      closeBanner();
      modal.show();

      $(id).on("click", function () {
        setTimeout(() => {
          const $modal = $(this);
          if (!$modal?.hasClass("show")) {
            const $banner = $(".sticky-calculator-banner");
            if ($banner?.hasClass("modal-action")) {
              clearBanner();
            } else {
              const event = new Event("scroll");
              document.dispatchEvent(event);
            }
          }
        });
      });
    }
  };

  const scrollIntoCalc = () => {
    const query = `#ccb_app_${appStore.getCalcId}`;
    const offsetTop = $(query)?.offset()?.top || 0;
    const offset = offsetTop - ($(window)?.scrollTop() || 0);
    if (offset > window.innerHeight - 300 || offset < 20) {
      $([document.documentElement, document.body]).animate(
        { scrollTop: offsetTop - 100 },
        500,
      );
    }
  };

  const showTotal = computed(() => {
    const action: string = appStore.getStickySettings?.oneClickAction || "";
    const woo_actions = [
      "woo_checkout",
      "woo_product_as_modal",
      "woo_product_with_redirect",
    ];

    if (woo_actions.includes(action)) {
      return !!appStore.getStickySettings?.showTotal;
    }

    return appStore.getStickySettings?.formulas?.length;
  });

  const getTitle = computed(() => {
    let title = appStore.getCalcTitle;
    if (appStore.getStickySettings?.title === "custom") {
      title = appStore.getStickySettings.customText;
    }

    return title;
  });

  const getShortTitle = computed(() => {
    let title = getTitle.value;
    if (title.length >= 23) {
      title = title.substring(0, 20) + "...";
    }

    return title;
  });

  const getFormattedTotal = computed(() => {
    const currencyFormatter = useStickyCurrency();

    if (calcFormulas.value.length === 1) {
      const current = calcFormulas.value[0];

      let value = 0;
      if ("total" in current) {
        value = current.total as number;
      } else if ("value" in current) {
        value = current.value as number;
      }

      const options: CurrencyFormatOptions =
        currencyFormatter.getCurrencyOptions(current);
      return currencyFormatter.formatCurrency(value, options);
    }

    const currencySettings =
      appStore.getCurrencySettings as CurrencyFormatOptions;
    return currencyFormatter.formatCurrency(total.value, currencySettings);
  });

  return {
    total,
    formulas,
    classList,
    calcFormulas,
    bannerPosition,
    isBannerVisible,

    updateTotal,
    updateFormulas,
    updateClassList,
    updateBannerStatus,
    updateCalcFormulas,
    updateBannerPosition,

    initListeners,
    closeBanner,
    clearBanner,
    openBanner,
    getTitle,
    getFormattedTotal,
    showTotal,
    clickAction,
    scrollHandler,
    customBannerEvent,
    getShortTitle,
  };
}
