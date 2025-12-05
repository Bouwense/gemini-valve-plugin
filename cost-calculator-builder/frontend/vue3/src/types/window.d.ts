declare global {
  interface Window {
    $ccbSticky?: {
      destroy: () => void;
      initialize: () => void;
    };
    $ccbStickyRendered?: boolean;
    StickySidebar?: any;
    ccb_nonces?: {
      [key: string]: string;
    };
    ccb_frontend_nonces?: {
      [key: string]: string;
    };
    wpcf7?: {
      init: (form: HTMLElement) => void;
    };
    ccb_rep_files?: Record<string, any>;
    ccbCaptchaFnc?: () => void;
    grecaptcha?: {
      render: (element: HTMLElement, options: any) => string;
      reset: (widgetId: string) => void;
      ready: (fn: () => void) => void;
      execute: (siteKey: string, options: any) => Promise<string>;
      getResponse: (widgetId: string) => string;
    };
  }
}

export {};
