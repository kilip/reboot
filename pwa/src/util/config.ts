
export type Config = {
  oidcClientId: string;
  oidcClientSecret: string;
  oidcIssuer: string;
  oidcTokenUrl: string;
};
export const config: Config = {
  oidcClientId: process.env.OIDC_CLIENT_ID || 'changeme',
  oidcClientSecret: process.env.OIDC_CLIENT_SECRET || 'secret',
  oidcIssuer: process.env.OIDC_ISSUER || 'https://localhost/issuer/url',
  oidcTokenUrl: process.env.OIDC_TOKEN_URL || 'https://localhost/token/url'
};
