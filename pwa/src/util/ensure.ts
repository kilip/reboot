import invariant from "tiny-invariant";

export function ensureEnv<T>(key: string): T {
  const value = process.env[ key ];
  invariant(value, `Environment variables "${value}" is not configured`);

  return value as T;
};
