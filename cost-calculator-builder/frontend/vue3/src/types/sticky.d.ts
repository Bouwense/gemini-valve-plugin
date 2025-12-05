declare global {
  interface Window {
    ccb_sticky_data?: Record<number, IncomingStickyData>;
  }
}
