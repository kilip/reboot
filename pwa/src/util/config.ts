import invariant from "tiny-invariant";
import { ensureEnv } from "./ensure";

export type Config = {
  oidcClientId: string,
  oidcClientSecret: string,
  oidcIssuer: string,
  oidcScope: string;
  oidcTokenUrl: string;
};

const issuer: string = ensureEnv('OIDC_ISSUER');
const matches = issuer.match(/.*\/o\//);
invariant(matches, `OIDC_ISSUER environment variables is not in the correct format: "${issuer}"`);
const tokenUrl: string = matches[ 0 ];

export const config: Config = {
  oidcClientId: ensureEnv('OIDC_CLIENT_ID'),
  oidcClientSecret: ensureEnv('OIDC_CLIENT_SECRET'),
  oidcIssuer: ensureEnv('OIDC_ISSUER'),
  oidcScope: ensureEnv('OIDC_SCOPE'),
  oidcTokenUrl: tokenUrl
};
