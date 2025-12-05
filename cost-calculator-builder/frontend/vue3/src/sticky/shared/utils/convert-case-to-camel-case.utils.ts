const toCamelCase = (str: string): string => {
  return str.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
};

export function convertKeysToCamelCase<T>(data: any): T {
  if (Array.isArray(data)) {
    return data.map(convertKeysToCamelCase) as T;
  } else if (data !== null && typeof data === "object") {
    return Object.keys(data).reduce((result: any, key: string) => {
      const camelKey = toCamelCase(key);
      result[camelKey] = convertKeysToCamelCase<T>(data[key]);
      return result;
    }, {});
  }
  return data;
}
