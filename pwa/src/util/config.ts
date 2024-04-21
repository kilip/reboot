
export type Config = {
  authentikClientId: string;
  authentikClientSecret: string;
  authentikIssuer: string;
  authentikTokenUrl: string;
};
export const config: Config = {
  authentikClientId: process.env.AUTHENTIK_CLIENT_ID || 'changeme',
  authentikClientSecret: process.env.AUTHENTIK_CLIENT_SECRET || 'secret',
  authentikIssuer: process.env.AUTHENTIK_ISSUER || 'https://localhost/issuer/url',
  authentikTokenUrl: process.env.AUTHENTIK_TOKEN_URL || 'https://localhost/token/url'
};
