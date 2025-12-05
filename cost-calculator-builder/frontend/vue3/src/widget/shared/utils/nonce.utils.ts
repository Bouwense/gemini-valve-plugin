const getNonces = (): Record<string, string> => {
  if (
    "ccb_frontend_nonces" in window &&
    typeof window.ccb_frontend_nonces === "object" &&
    window.ccb_frontend_nonces !== null
  ) {
    return window.ccb_frontend_nonces;
  }

  return window.ccb_nonces || {};
};

export const getNonce = (key: string): string => {
  const data: Record<string, any> = getNonces();
  return data[key].toString() || "";
};
