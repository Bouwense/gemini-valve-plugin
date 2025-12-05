export const parseHtml = (encodedStr: string): string => {
  const parser = new DOMParser();
  const dom = parser.parseFromString(
    "<!doctype html><body>" + encodedStr,
    "text/html",
  );

  return dom.body.textContent || "";
};
