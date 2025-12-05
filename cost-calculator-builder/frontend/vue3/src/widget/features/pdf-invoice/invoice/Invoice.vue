<template>
  <div class="ccb-invoice">
    <vue3-html2pdf
      id="pdfContent"
      :enable-download="download"
      :show-layout="false"
      :float-layout="true"
      :preview-modal="false"
      :filename="pdfName"
      :pdf-quality="2"
      :manual-pagination="true"
      pdf-format="a4"
      pdf-orientation="portrait"
      ref="invoice"
      @beforeDownload="beforeDownload($event)"
    >
      <template v-slot:pdf-content>
        <div class="ccb-pdf-tool-manager-preview">
          <pdf-layout :pdf="pdfData" :content="content"></pdf-layout>
        </div>
      </template>
    </vue3-html2pdf>
  </div>
</template>

<script setup lang="ts">
import Vue3Html2pdf from "vue3-html2pdf";
import {
  ref,
  defineExpose,
  defineEmits,
  computed,
  onMounted,
  watch,
} from "vue";
import { useAppStore } from "@/widget/app/providers/stores/appStore.ts";
import pdfLayout from "@/widget/features/pdf-invoice/invoice/components/layouts/index.vue";
import { useSettingsStore } from "@/widget/app/providers/stores/settingsStore";
import { useSubmissionStore } from "@/widget/app/providers/stores/submissionStore.ts";
// @ts-ignore: jsPDF is used indirectly
import jsPDF from "jspdf";
import { IPdfSettings } from "@/widget/shared/types/settings";
const emit = defineEmits(["generate-quote"]);

interface Vue3Html2pdf {
  generatePdf: () => Promise<void>;
}

const invoice = ref<Vue3Html2pdf | null>(null);
const appStore = useAppStore();
const settingsStore = useSettingsStore();

const download = ref(false);
const pdfString = ref("");

const content = ref({
  id: "",
  orderDetails: [],
  paymentMethodType: "",
  total: "",
  totals: [],
});

onMounted(() => {
  content.value.id = getOrderId.value?.toString() || "";
  const pmRecord: Record<string, string> = {
    cash_payment: "Cash Payment",
    stripe: "Stripe",
    paypal: "Paypal",
    no_payments: "No Payment",
    no_payment: "No Payment",
  };

  content.value.paymentMethodType =
    pmRecord[paymentMethodType.value] || "No Payment";
});

const pdfData = computed((): IPdfSettings => {
  return settingsStore.getPdfSettings || ({} as IPdfSettings);
});

const beforeDownload = async ({
  html2pdf,
  options,
  pdfContent,
}: {
  html2pdf: () => any;
  options: Record<string, any>;
  pdfContent: HTMLElement;
}) => {
  let conf = options;
  conf.enableLinks = true;
  (
    pdfContent.querySelector(".ccb-pdf-tool-manager-preview") as HTMLElement
  ).style.width = "794px";
  if (download.value) {
    await html2pdf()
      .from(pdfContent)
      .set(conf)
      .output("blob")
      .then((pdfBlob: Blob) => {
        const blobUrl = URL.createObjectURL(pdfBlob);
        window.open(blobUrl);
        setTimeout(() => {
          URL.revokeObjectURL(blobUrl);
        }, 1000);
      });
  } else {
    await html2pdf()
      .set(conf)
      .from(pdfContent)
      .toPdf()
      .get("pdf")
      .output("datauristring")
      .then((pdfAsString: string) => {
        pdfString.value = pdfAsString.split(",")[1];
        emit("generate-quote", { data: pdfString.value });
      });
  }
};

const generateQuote = async () => {
  download.value = false;
  if (invoice.value) {
    await invoice.value.generatePdf();
  }
};

// Todo: get order id from store
const getOrderId = computed(() => {
  const submissionStore = useSubmissionStore();
  11;
  return submissionStore.getOrderId;
});

const paymentMethodType = computed(() => {
  const submissionStore = useSubmissionStore();

  const paymentMethod = submissionStore.getSendPaymentType;

  if (paymentMethod) {
    return paymentMethod;
  }

  return "No Payment";
});

const pdfName = computed(() => {
  return appStore.title;
});

const generate = () => {
  download.value = true;
  if (invoice.value) {
    invoice.value.generatePdf();
  }
};

watch(paymentMethodType, () => {
  const pmRecord: Record<string, string> = {
    cash_payment: "Cash Payment",
    stripe: "Stripe",
    paypal: "Paypal",
    no_payments: "No Payment",
    no_payment: "No Payment",
  };

  content.value.paymentMethodType =
    pmRecord[paymentMethodType.value] || "No Payment";
});

defineExpose({
  generate,
  generateQuote,
});
</script>

<style></style>
