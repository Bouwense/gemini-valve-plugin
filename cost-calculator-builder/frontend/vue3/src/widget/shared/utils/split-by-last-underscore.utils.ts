export function splitByLastUnderscore(str: string): string {
  const lastIndex: number = str.lastIndexOf("_");
  return lastIndex !== -1 ? str.substring(0, lastIndex) : str;
}
