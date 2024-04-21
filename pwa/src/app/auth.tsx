import { config } from "@/util/config";
import { type TokenSet } from "@auth/core/types";
import NextAuth, { type Session as DefaultSession, type User } from "next-auth";
import AuthentikProvider from "next-auth/providers/authentik";

export interface Session extends DefaultSession {
  error?: "RefreshAccessTokenError";
  accessToken: string;
  refreshToken: string;
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
        return {
          ...token,
          accessToken: account.access_token,
          idToken: account.id_token,
          expiresAt: Math.floor(Date.now() / 1000 + account.expires_in),
          refreshToken: account.refresh_token,
        } as JWT;
      } else if (Date.now() < token.expiresAt * 1000) {
        // If the access token has not expired yet, return it
        return token;
      }

      try {
        const url =
          `${config.oidcTokenUrl}?` +
          new URLSearchParams({
            clientId: config.oidcClientId,
            clientSecret: config.oidcClientSecret,
            grantType: "refresh_token",
            refreshToken: token.refreshToken,
          });

        const response = await fetch(url, {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          method: "POST",
        });

        const tokens: TokenSet = await response.json();

        if (!response.ok) throw tokens;

        return {
          ...token,
          // @ts-ignore
          accessToken: tokens.access_token,
          // @ts-ignore
          idToken: tokens.id_token,
          // @ts-ignore
          expiresAt: Math.floor(Date.now() / 1000 + tokens.expires_at),
          refreshToken: tokens.refresh_token ?? token.refreshToken,
        };
      } catch (error) {
        console.log("Error refreshing access token", error);
        return {
          ...token,
          error: "RefreshAccessTokenError" as const,
        };
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
      if (token) {
        session.accessToken = token.accessToken;
        session.idToken = token.idToken;
        session.error = token.error;
        session.refreshToken = token.refreshToken;
      }

      return session;
    },
  },
  providers: [
    AuthentikProvider({
      clientId: config.oidcClientId,
      clientSecret: config.oidcClientSecret,
      issuer: config.oidcIssuer,
      authorization: {
        params: {
          scope: config.oidcScope,
        },
      },
    }),
  ],
});
