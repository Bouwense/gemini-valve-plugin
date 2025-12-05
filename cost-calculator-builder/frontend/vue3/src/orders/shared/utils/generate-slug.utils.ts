import { slugify } from "transliteration";

export function generateSlug(title: string): string {
  return slugify(title, { lowercase: true });
}
