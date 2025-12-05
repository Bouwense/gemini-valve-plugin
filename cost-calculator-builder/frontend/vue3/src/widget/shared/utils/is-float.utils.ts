export const isFloat = (n: string): boolean => {
  if (n === undefined || n?.length < 0) {
    return false;
  }
  const regExp = /^[-+]?[0-9]+\.[0-9]+$/;
  return !!n.toString().match(regExp);
};
