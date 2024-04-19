import { ensureEnv } from "./ensure";

export type Config = {
  oidcClientId: string,
  oidcClientSecret: string,
  oidcIssuer: string,
  oidcScope: string;
  oidcTokenUrl: string;
};

export const config: Config = {
  oidcClientId: ensureEnv('OIDC_CLIENT_ID'),
  oidcClientSecret: ensureEnv('OIDC_CLIENT_SECRET'),
  oidcIssuer: ensureEnv('OIDC_ISSUER'),
  oidcScope: ensureEnv('OIDC_SCOPE'),
  oidcTokenUrl: ensureEnv('OIDC_TOKEN_URL'),
};
