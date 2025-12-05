<template>
  <div
    class="ccb-order-form"
    :class="{
      'ccb-order-form-legacy': !settings.getFormSettings?.contactFormId,
      'is-live-form': !orderFormStore.getNextButtonStatus && isLive,
    }"
  >
    <ProBadge />

    <template
      v-if="
        orderFormStore.getNextButtonStatus && getPaymentType !== 'woocommerce'
      "
    >
      <div class="ccb-order-form__submit ccb-col-12">
        <Button
          type="success"
          :text="getMakeOrderText"
          :on-click="nextButton"
          :disabled="btnDisabledStatus"
        />
      </div>
    </template>
    <template v-else>
      <template v-if="showSendData && getPaymentType !== 'woocommerce'">
        <template v-if="settings.getFormSettings?.contactFormId">
          <div
            class="ccb-contact-form7"
            :class="{
              'calc-cf7-disabled': orderFormStore.getContactFormDisabled,
            }"
          >
            <div
              class="ccb-contact-form7-container"
              v-html="getCf7Content"
            ></div>
          </div>
        </template>
        <template v-else>
          <OrderFormItem
            v-for="formField in formFields"
            :field="formField"
            :key="formField.id"
            :name="formField.type"
          />
          <TermsAndConditions
            v-if="settings.getFormSettings?.accessTermsEmail"
          />
          <div
            :id="getCalcId"
            class="g-rec"
            v-if="captchaStatus"
            style="max-width: 100px"
          ></div>
        </template>
      </template>

      <div class="ccb-order-form__submit ccb-col-12" v-if="showButton">
        <Button
          type="success"
          :text="getSubmitOrderText"
          :on-click="submitForm"
          :disabled="btnDisabledStatus"
        />
      </div>
      <Transition name="fade">
        <DemoNotice v-if="showDemoNotice" />
      </Transition>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed, toRefs, ref, watch, onMounted } from "vue";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";
import { usePaymentStore } from "@/widget/app/providers/stores/paymentsStore.ts";
import { usePaymentAfterSubmitStore } from "@/widget/app/providers/stores/paymentAfterSubmit.ts";
import { useOrderFormStore } from "@/widget/app/providers/stores/orderFormStore.ts";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore.ts";
import { useFieldsStore } from "@/widget/app/providers/stores/fieldsStore";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import { ContactFormFields } from "@/widget/shared/types/orders/contact-form-fields.type";
import { parseHtml } from "@/widget/shared/utils/html-parser.utils";
import DemoNotice from "@/widget/shared/ui/components/Demo-notice/DemoNotice.vue";
import OrderFormItem from "./OrderFormItem.vue";
import Button from "@/widget/shared/ui/components/Button/Button.vue";
import TermsAndConditions from "@/widget/features/submission/order-form/terms-and-conditions/TermsAndConditions.vue";
import ProBadge from "@/widget/shared/ui/components/Pro-badge/ProBadge.vue";
import { IRecaptcha } from "@/widget/shared/types/settings/settings.type";
import { useNotificationsStore } from "@/widget/app/providers/stores/notificationsStore.ts";

type Props = {
  payment?: boolean;
};

const captchaIntegrated = ref(false);

const props = defineProps<Props>();
const { payment } = toRefs(props);

const { validateOrderFormSettings } = useOrderForm();
const orderFormStore = useOrderFormStore();
const submissionStore = useSubmissionStore();
const settings = useSettingsStore();
const appStore = useAppStore();
const myCf7Root = ref<HTMLElement | null>(null);
const allowContactForm = ref<boolean>(false);
const fieldsStore = useFieldsStore();

const formFields = computed(() => {
  return orderFormStore.getFormFields;
});

const getMakeOrderText = computed((): string => {
  return settings.getFormSettings?.openModalBtnText || "Make Order";
});

const captchaStatus = computed((): boolean => {
  const captcha = settings.getRecaptchaSettings;
  return (captcha?.enable && captcha?.type === "v2") || false;
});

const getSubmitOrderText = computed((): string => {
  const summaryDisplay = settings.getFormSettings?.summaryDisplay;

  if (getPaymentType.value === "woocommerce") {
    return "Add to cart";
  }

  if (summaryDisplay?.enable) {
    return summaryDisplay.submitBtnText || "View Summary";
  }

  return settings.getFormSettings?.submitBtnText || "Submit Order";
});

// IF demo or live site ( demonstration only )
const showDemoNotice = ref(false);
const isLive = computed(() => {
  return appStore.getIsLive;
});
// END| IF demo or live site ( demonstration only )

const submitForm = () => {
  // IF demo or live site ( demonstration only )
  if (isLive.value) {
    showDemoNotice.value = true;
    setTimeout(() => {
      showDemoNotice.value = false;
    }, 5000);
    return;
  }
  // END| IF demo or live site ( demonstration only )

  const captcha = settings.getRecaptchaSettings;
  let captcha_access = true;
  const notificationsStore = useNotificationsStore();

  if (captcha?.enable) {
    if (captcha.type === "v2") {
      const token = orderFormStore.getCaptchaToken;
      if (!token) {
        notificationsStore.updateNotifications({
          status: true,
          type: "error",
          message: "reCAPTCHA verification failed",
        });
        return;
      }
    } else if (captcha.type === "v3") {
      captcha_access = false;
      window.grecaptcha?.ready(() => {
        window.grecaptcha
          ?.execute(captcha.siteKey, { action: "submit" })
          .then((token: string) => {
            orderFormStore.setCaptchaToken(token);
            captcha_access = true;
            proceedWithSubmission();
          })
          .catch((_: Error) => {
            notificationsStore.updateNotifications({
              status: true,
              type: "error",
              message: "reCAPTCHA v3 verification failed",
            });
          });
      });
      return;
    }
  }

  if (captcha_access) {
    proceedWithSubmission();
  }
};

const proceedWithSubmission = () => {
  if (getPaymentType.value === "woocommerce" || !validateOrderFormSettings()) {
    submissionStore.submissionCreateOrder();
  }
};

const customSubmissionCreateOrder = (event: any) => {
  const orderInputs: ContactFormFields[] = event.detail.inputs.map(
    (input: any) => {
      return {
        label: input.name || "",
        value: input.value.replace(/\n/g, " ") || "",
        attributes: {
          label: input.name || "",
          type: input.name || "",
        },
      };
    },
  );

  submissionStore.submissionCreateOrder(orderInputs);
};

const showSendData = computed((): boolean => {
  const paymentStore = usePaymentStore();
  const paymentAfterSubmitStore = usePaymentAfterSubmitStore();

  if (!payment.value) return true;

  if (
    paymentAfterSubmitStore.isPaymentAfterSubmit &&
    paymentAfterSubmitStore.getSubmit
  ) {
    return false;
  }

  return paymentStore.showForm;
});

const getPaymentType = computed((): string => {
  const paymentStore = usePaymentStore();
  return paymentStore.paymentType;
});

const showButton = computed((): boolean => {
  if (settings.getFormSettings?.contactFormId) {
    return false;
  }

  const paymentAfterSubmitStore = usePaymentAfterSubmitStore();
  return !(
    paymentAfterSubmitStore.isPaymentAfterSubmit &&
    paymentAfterSubmitStore.getSubmit
  );
});

const btnDisabledStatus = computed((): boolean => {
  const paymentStore = usePaymentStore();

  if (
    fieldsStore.pageBreakEnabled &&
    fieldsStore.getActivePageIndex !== fieldsStore.getPages.length - 1 &&
    !settings.getPageBreakerSettings?.summaryAfterLastPage
  ) {
    return true;
  }

  if (showSendData.value) return false;
  if (paymentStore.paymentType && !fieldsStore.checkRequiredFields()) {
    return true;
  }

  return !paymentStore.paymentType;
});

const nextButton = () => {
  if (fieldsStore.checkRequiredFields()) {
    orderFormStore.updateNextButtonStatus(false);
    orderFormStore.updateNextButton(true);
    allowContactForm.value = true;

    setTimeout(() => {
      initContactFormActions();
    });
  }
};

const getCf7Content = computed((): string => {
  const calcId = getCalcId.value;
  const key: any = `ccb_front_template_${calcId}`;
  const contactFormData = window[key] as unknown as { cf7_form: string };
  if (contactFormData && "cf7_form" in contactFormData) {
    return contactFormData.cf7_form;
  }

  return "";
});

const getCalcId = computed((): string => {
  return appStore.getCalcId?.toString() || "";
});

const cleanFormData = () => {};

const showContactForm = () => {
  myCf7Root.value = document.querySelector(`#ccb_app_${getCalcId.value}`);

  if (!myCf7Root.value) return;

  if (
    myCf7Root.value?.getElementsByClassName("ccb-contact-form7").length <= 0
  ) {
    return;
  }

  if (
    myCf7Root.value
      .getElementsByClassName("ccb-contact-form7")[0]
      .getElementsByTagName("form").length <= 0
  ) {
    return;
  }

  let form = myCf7Root.value
    .getElementsByClassName("ccb-contact-form7")[0]
    .getElementsByTagName("form")[0];

  let formRows = [...form.getElementsByTagName("p")];
  formRows.forEach((cfRow) => {
    if (cfRow.classList.contains("ccb-hidden")) {
      cfRow.classList.remove("ccb-hidden");
      cfRow.style.display = "block";
    }
  });

  /** clean message **/
  if (form.getElementsByClassName("wpcf7-response-output").length > 0) {
    form.getElementsByClassName("wpcf7-response-output")[0].innerHTML = "";
  }
};

const cfSentCallback = (event: any): void => {
  event.preventDefault();
  if (isLive.value) {
    return;
  }

  if (allowContactForm.value) {
    customSubmissionCreateOrder(event);
    allowContactForm.value = false;

    if (event?.detail?.status === "mail_sent") {
      orderFormStore.updateContactFormDisabled(true);
    }
  }
};

const cfInitContent = () => {
  const fieldsStore = useFieldsStore();

  if (settings.getFormSettings?.contactFormId) {
    let text = settings.getFormSettings?.body;
    const $form = myCf7Root.value?.querySelector(".wpcf7-form");

    if (text.indexOf("[ccb-subtotal]") !== -1) {
      let subtotal: string = fieldsStore.parseContactFormDescriptions();
      let regex = "[ccb-subtotal]";
      text = text.replaceAll(regex, subtotal);
    }

    const formFormulas = settings.getFormSettings?.formulas?.map(
      (f) => f.alias,
    );

    let totalSubtotals = fieldsStore.parseContactFormTotals(formFormulas);

    if (!totalSubtotals) {
      totalSubtotals =
        fieldsStore.getTotalsList
          .map((item) => item.displayValue)
          .filter(Boolean)
          .map((value) => (Array.isArray(value) ? value.join(", ") : value))
          .find(Boolean) ?? "";
    }

    const regexPattern = /\[ccb-total(?:-\d+)?\]/g;
    text = text.replaceAll(regexPattern, `${totalSubtotals}\n`);

    const $textarea = $form?.querySelector("textarea");
    if ($textarea) $textarea.value = parseHtml(text);
    if (window.wpcf7 && "init" in window.wpcf7) {
      const forms = myCf7Root.value?.querySelectorAll(".wpcf7 > form");
      forms?.forEach((form) => {
        window.wpcf7?.init(form as HTMLElement);
        if (isLive.value) {
          form.addEventListener("submit", (e) => {
            e.preventDefault();
            showDemoNotice.value = true;
            setTimeout(() => {
              showDemoNotice.value = false;
            }, 5000);
          });
        }
      });
    }
  }
};

const cfSentHandler = () => {
  document.addEventListener("wpcf7mailsent", cfSentCallback, false);
};

const initContactFormActions = () => {
  cleanFormData();
  showContactForm();
  cfSentHandler();
  cfInitContent();
};

const initRecaptcha = () => {
  const captcha = settings.getRecaptchaSettings;
  if (captcha?.enable && captcha?.siteKey && captcha?.secretKey) {
    renderCaptchaFunc(captcha);
    renderCaptchaScript(captcha);
  }
};

const renderCaptchaFunc = (captcha: IRecaptcha) => {
  const notificationsStore = useNotificationsStore();

  if (captcha.type === "v2") {
    const g_res = document.querySelectorAll(".g-rec");
    window.ccbCaptchaFnc = () => {
      g_res.forEach((element) => {
        try {
          const ccb_id = window?.grecaptcha?.render(element as HTMLElement, {
            sitekey: captcha.siteKey,
            callback: (token: string) => {
              orderFormStore.setCaptchaToken(token);
            },
            "expired-callback": () => {
              orderFormStore.setCaptchaToken("");
            },
          });

          if (ccb_id) {
            element.setAttribute("data-widget_id", ccb_id.toString());
          }
        } catch (_) {
          notificationsStore.updateNotifications({
            status: true,
            type: "error",
            message: "Error rendering reCAPTCHA",
          });
        }
      });
    };
  } else if (captcha.type === "v3") {
    window.ccbCaptchaFnc = () => {
      window.grecaptcha?.ready(() => {
        window.grecaptcha
          ?.execute(captcha.siteKey, { action: "submit" })
          .then((token: string) => {
            orderFormStore.setCaptchaToken(token);
          })
          .catch((_: Error) => {
            notificationsStore.updateNotifications({
              status: true,
              type: "error",
              message: "Error executing reCAPTCHA v3",
            });
          });
      });
    };
  }
};

const renderCaptchaScript = async (captcha: IRecaptcha) => {
  if (captchaIntegrated.value && captcha?.type === "v3") return;

  const src_store = {
    v2: "https://www.google.com/recaptcha/api.js?onload=ccbCaptchaFnc&render=explicit",
    v3: `https://www.google.com/recaptcha/api.js?render=${captcha.siteKey}`,
  };

  const script = document.createElement("script");
  script.src = src_store[captcha.type];
  script.setAttribute("defer", "");
  script.setAttribute("async", "");

  const firstScriptTag = document.querySelectorAll("script")[0];
  firstScriptTag?.parentNode?.insertBefore(script, firstScriptTag);
  captchaIntegrated.value = true;
};

const getNextButtonStatus = computed((): boolean => {
  return orderFormStore.getNextButtonStatus;
});

watch(getNextButtonStatus, (newVal) => {
  const captcha = settings.getRecaptchaSettings;
  if (!newVal && captcha?.enable && captcha?.type === "v2") {
    setTimeout(() => {
      initRecaptcha();
    });
  } else if (newVal && captcha?.enable && captcha?.type === "v2") {
    orderFormStore.setCaptchaToken("");
  }
});

onMounted(() => {
  const captcha = settings.getRecaptchaSettings;
  if (captcha?.enable && captcha?.type === "v3" && !captchaIntegrated.value) {
    setTimeout(() => {
      initRecaptcha();
    }, 1000);
  }
});
</script>

<style lang="scss">
.ccb-order-form {
  .is-pro {
    display: none;
  }
  &.is-live-form {
    position: relative;
    margin-top: 30px;
    margin-bottom: 15px;
    padding-top: 0;
    .ccb-order-form__submit {
      z-index: 1;
    }
    &::before {
      content: "";
      position: absolute;
      border: 1px solid #8bdef9;
      left: -20px;
      top: -12px;
      right: -20px;
      bottom: -12px;
      z-index: 0;
    }
    .is-pro {
      display: block;
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      margin-left: 0;
      top: -20px;
      width: 40%;
      text-align: center;
    }
  }
  &__submit {
    display: flex;
    button {
      width: 100%;
    }
  }

  &.ccb-order-form-legacy {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 20px;

    .ccb-col-2 {
      grid-column: span 2;
    }

    .ccb-col-4 {
      grid-column: span 4;
    }

    .ccb-col-6 {
      grid-column: span 6;
    }

    .ccb-col-8 {
      grid-column: span 8;
    }

    .ccb-col-10 {
      grid-column: span 10;
    }

    .ccb-col-12 {
      grid-column: span 12;
    }
  }

  .ccb-contact-form7-container {
    .wpcf7-submit {
      padding: 10px 20px;
      outline: none;
      box-shadow: none;
      min-height: var(--ccb-field-button-height);
      border-radius: var(--ccb-fields-border-radius);
      display: flex;
      justify-content: center;
      align-items: center;
      column-gap: 6px;
      cursor: pointer;
      transition: 300ms ease;
      font-size: var(--ccb-fields-button-size);
      font-weight: var(--ccb-fields-button-weight);
      background: var(--ccb-accent-color);
      color: var(--ccb-fields-color);
      border: none;
      width: 100%;
      @media only screen and (max-width: 480px) {
        min-height: var(--ccb-mobile-field-button-height);
        font-size: var(--ccb-mobile-fields-button-size);
        font-weight: var(--ccb-mobile-fields-button-weight);
      }
    }
  }
}

.ccb-contact-form7 {
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  label {
    font-size: 14px;
    font-weight: 400;
    width: 100%;

    input,
    textarea {
      width: 100%;
    }

    input {
      padding: 10px;
      margin-bottom: 10px;
    }

    textarea {
      padding: 10px;
      resize: vertical;
      margin-bottom: 10px;
    }
  }

  &.calc-cf7-disabled {
    opacity: 0.5;
    pointer-events: none;
  }

  .wpcf7-response-output {
    display: none !important;
    visibility: hidden !important;
    z-index: -1 !important;
  }
}
</style>
