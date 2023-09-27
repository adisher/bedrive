export type PartialWithRequired<T, K extends keyof T> = Pick<T, K> & Partial<T>;
