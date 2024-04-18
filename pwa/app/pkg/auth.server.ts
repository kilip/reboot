import { Authenticator } from "remix-auth";
import { OAuth2Strategy, OAuth2StrategyOptions } from "remix-auth-oauth2";
import invariant from "tiny-invariant";
import { sessionStorage } from "~/pkg/session.server";

type User = string;

// Create an instance of the authenticator, pass a generic with what
// strategies will return and will store in the session
export const authenticator = new Authenticator<User>(sessionStorage);

invariant(process.env.OIDC_AUTHORIZE_URL, "OIDC authorize url should be configured");
invariant(process.env.OIDC_TOKEN_URL, "OIDC token url should be configured");
invariant(process.env.OIDC_CALLBACK_URL);
invariant(process.env.OIDC_CLIENT_ID);
invariant(process.env.OIDC_CLIENT_SECRET);

const callbackURL = process.env.NODE_ENV === "production" ? process.env.OIDC_CALLBACK_URL : "https://localhost/auth/callback";
const options: OAuth2StrategyOptions = {
  authorizationURL: process.env.OIDC_AUTHORIZE_URL,
  tokenURL: process.env.OIDC_TOKEN_URL,
  callbackURL,
  clientID: process.env.OIDC_CLIENT_ID,
  clientSecret: process.env.OIDC_CLIENT_SECRET,
  scope: "openid email profile offline_access",
};

const authentikStrategy = new OAuth2Strategy(
  options,
  async ({
    accessToken,
    refreshToken,
    extraParams,
    profile,
    context,
    request
  }) => {
    console.log({
      profile,
      context,
      request,
      extraParams,
      refreshToken,
      accessToken
    });
    return profile.emails[ 0 ];
  }
);

authenticator.use(authentikStrategy, 'authentik');
