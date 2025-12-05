export function arraysEqualAsSets<T>(a: T[], b: T[]): boolean {
  if (a.length !== b.length) return false;

  const setA = new Set(a);
  const setB = new Set(b);

  if (setA.size !== setB.size) return false;

  for (const value of setA) {
    if (!setB.has(value)) return false;
  }

  return true;
}
