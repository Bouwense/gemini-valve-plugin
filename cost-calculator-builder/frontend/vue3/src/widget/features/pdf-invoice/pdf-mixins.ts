import { defineAsyncComponent } from "vue";
import { IPdfSettings } from "@/widget/shared/types/settings";

export function pdfMixin() {
  const getBorderStyles = (pdf: IPdfSettings) => {
    const styles: { [key: string]: string } = {};
    const documentTabs = pdf.document?.tabs;

    if (documentTabs?.border) {
      const border = documentTabs.border;
      if (border.data?.showBorder?.value) {
        const borderType = border.data?.borderType?.value;
        const borderSize = border.data?.borderSize?.value;
        const borderColor = border.data?.borderColor?.value;
        const borderStyle = border?.data?.borderStyle?.value || "solid";

        switch (borderType) {
          case "solid":
            (styles as any).border =
              `${borderSize}px ${borderStyle} ${borderColor}`;
            break;
          case "left":
            (styles as any).borderLeft =
              `${borderSize}px ${borderStyle} ${borderColor}`;
            break;
          case "right":
            (styles as any).borderRight =
              `${borderSize}px ${borderStyle} ${borderColor}`;
            break;
          case "bottom":
            (styles as any).borderBottom =
              `${borderSize}px ${borderStyle} ${borderColor}`;
            break;
          case "top":
            (styles as any).borderTop =
              `${borderSize}px ${borderStyle} ${borderColor}`;
            break;
        }
      }
    }

    if (documentTabs?.body) {
      const body = documentTabs.body;
      (styles as any).padding = `${body.data?.sidePaddings?.value}px`;
    }

    return styles;
  };

  const getDocumentSidebarStyles = (pdf: IPdfSettings) => {
    const styles = {};
    const documentTabs = pdf.document?.tabs;

    if (documentTabs?.sidebar) {
      const sidebar = documentTabs.sidebar;
      if (sidebar.data?.textColor?.data?.status) {
        (styles as any).color = sidebar.data.textColor.value;
      }
      if (sidebar.data?.backgroundColor?.data?.status) {
        (styles as any).backgroundColor = sidebar.data.backgroundColor.value;
      }
    }

    return styles;
  };

  const getComponentName = (componentName: string) => {
    if (componentName === "brand_block") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/BrandBlock.vue"
          ),
      );
    } else if (componentName === "company_block") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/CompanyBlock.vue"
          ),
      );
    } else if (componentName === "customer_block") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/CustomerBlock.vue"
          ),
      );
    } else if (componentName === "additional_text_block") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/AdditionalTextBlock.vue"
          ),
      );
    } else if (componentName === "top_text_block") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/TopTextBlock.vue"
          ),
      );
    } else if (componentName === "order_block") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/OrderBlock.vue"
          ),
      );
    } else if (componentName === "footer_text") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/FooterText.vue"
          ),
      );
    } else if (componentName === "order_id_and_date") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/OrderIdAndDate.vue"
          ),
      );
    } else if (componentName === "image_block") {
      return defineAsyncComponent(
        () =>
          import(
            "@/widget/features/pdf-invoice/invoice/components/items/ImageBlock.vue"
          ),
      );
    }

    return "";
  };

  return {
    getBorderStyles,
    getDocumentSidebarStyles,
    getComponentName,
  };
}
