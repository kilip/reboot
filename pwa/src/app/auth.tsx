import { type TokenSet } from "@auth/core/types";
import NextAuth, { type Session as DefaultSession, type User } from "next-auth";
import Authentik from "next-auth/providers/authentik";
import { config } from "../util/config";

export interface Session extends DefaultSession {
  error?: "RefreshAccessTokenError";
  accessToken: string;
  idToken: string;
  user?: User;
}

interface JWT {
  accessToken: string;
  idToken: string;
  expiresAt: number;
  refreshToken: string;
  error?: "RefreshAccessTokenError";
}

interface Account {
  access_token: string;
  id_token: string;
  expires_in: number;
  refresh_token: string;
}

export const {
  handlers: { GET, POST },
  auth,
} = NextAuth({
  callbacks: {
    // @ts-ignore
    async jwt({
      token,
      account,
    }: {
      token: JWT;
      account: Account;
    }): Promise<JWT> {
      if (account) {
        // Save the access token and refresh token in the JWT on the initial login
        return {
          ...token,
          accessToken: account.access_token,
          idToken: account.id_token,
          expiresAt: Math.floor(Date.now() / 1000 + account.expires_in),
          refreshToken: account.refresh_token,
        };
      } else if (Date.now() < token.expiresAt * 1000) {
        // If the access token has not expired yet, return it
        return token;
      } else {
        // If the access token has expired, try to refresh it
        try {
          const response = await fetch(`${config.authentikTokenUrl}`, {
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
              client_id: config.authentikClientId,
              client_secret: config.authentikClientSecret,
              grant_type: "refresh_token",
              refresh_token: token.refreshToken,
            }),
            method: "POST",
          });

          const tokens: TokenSet = await response.json();

          if (!response.ok) throw tokens;

          return {
            ...token, // Keep the previous token properties
            // @ts-ignore
            accessToken: tokens.access_token,
            // @ts-ignore
            idToken: tokens.id_token,
            // @ts-ignore
            expiresAt: Math.floor(Date.now() / 1000 + tokens.expires_at),
            // Fall back to old refresh token, but note that
            // many providers may only allow using a refresh token once.
            refreshToken: tokens.refresh_token ?? token.refreshToken,
          };
        } catch (error) {
          console.error("Error refreshing access token", error);
          // The error property will be used client-side to handle the refresh token error
          return {
            ...token,
            error: "RefreshAccessTokenError" as const,
          };
        }
      }
    },
    // @ts-ignore
    async session({
      session,
      token,
    }: {
      session: Session;
      token: JWT;
    }): Promise<Session> {
      // Save the access token in the Session for API calls
      if (token) {
        session.accessToken = token.accessToken;
        session.idToken = token.idToken;
        session.error = token.error;
      }

      return session;
    },
  },
  providers: [
    Authentik({
      clientId: config.authentikClientId,
      clientSecret: config.authentikClientSecret,
      issuer: config.authentikIssuer,
      authorization: {
        scope: "openid email profile offline_access",
      },
    }),
  ],
});
