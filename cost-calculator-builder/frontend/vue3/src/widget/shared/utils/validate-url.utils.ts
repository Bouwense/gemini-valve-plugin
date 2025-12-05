export const validateUrl = (websiteUrl: string): boolean => {
  const regex =
    /^(http(s)?:\/\/)?(www\.)?[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+(\.[a-zA-Z]{2,})?$/;

  return regex.test(websiteUrl);
};
