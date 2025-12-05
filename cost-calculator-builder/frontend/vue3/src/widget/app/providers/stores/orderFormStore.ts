import { defineStore } from "pinia";
import { useOrderForm } from "@/widget/actions/pro-features/order-form/composable/useOrderForm.ts";
import { usePaymentStore } from "./paymentsStore";
import { useSubmissionStore } from "./submissionStore";
import { IFormField } from "@/widget/shared/types/fields";

const orderFormInstance = useOrderForm();

interface IOrderFormState {
  nextButtonStatus: boolean;
  nextButton: boolean;
  contactFormDisabled: boolean;
  formFields: IFormField[];
  errors: Record<number, string>;
  captchaToken: string;
}

export const useOrderFormStore = defineStore("order_form_store", {
  state: (): IOrderFormState => ({
    nextButtonStatus: true,
    nextButton: false,
    contactFormDisabled: false,
    formFields: [],
    errors: {},
    captchaToken: "",
  }),

  actions: {
    setCaptchaToken(token: string): void {
      this.captchaToken = token;
    },

    updateFormFields(fields: IFormField[]): void {
      this.formFields = fields;
    },

    updateErrors(errors: Record<number, string>): void {
      this.errors = errors;
    },

    updateNextButtonStatus(value: boolean): void {
      if (value === true) {
        this.contactFormDisabled = false;
      }

      this.nextButtonStatus = value;
    },

    updateNextButton(value: boolean): void {
      this.nextButton = value;
    },

    updateContactFormDisabled(value: boolean): void {
      this.contactFormDisabled = value;
    },

    initOrderForm(formFields: any[] = []): void {
      orderFormInstance.initializeFields(formFields);
    },

    resetBtnData(): void {
      const submissionStore = useSubmissionStore();
      const paymentStore = usePaymentStore();

      submissionStore.reset();
      paymentStore.reset();

      this.nextButtonStatus = true;
      this.nextButton = false;
      this.contactFormDisabled = false;
    },
  },

  getters: {
    getNextButtonStatus: (state: IOrderFormState): boolean =>
      state.nextButtonStatus,
    getNextButton: (state: IOrderFormState): boolean => state.nextButton,
    getContactFormDisabled: (state: IOrderFormState): boolean =>
      state.contactFormDisabled,
    getFormFields: (state: IOrderFormState): IFormField[] => state.formFields,
    getErrors: (state: IOrderFormState): Record<number, string> => state.errors,
    getCaptchaToken: (state: IOrderFormState): string => state.captchaToken,
  },
});
