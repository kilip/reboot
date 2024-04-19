import { config } from "@/util/config";
import { type Session as DefaultSession, TokenSet, User } from "next-auth";
import NextAuth from "next-auth/next";
import AuthentikProvider from "next-auth/providers/authentik";
import { signOut as logout, type SignOutParams } from "next-auth/react";

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

interface SignOutResponse {
  url: string;
}

export async function signOut<R extends boolean = true>(
  session: DefaultSession,
  options?: SignOutParams<R>
): Promise<R extends true ? undefined : SignOutResponse> {
  return await logout({
    // @ts-ignore
    callbackUrl: `${config.oidcIssuer}/end-session`,
  });
}

export const handler = NextAuth({
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
        const response = await fetch(`${config.oidcTokenUrl}`, {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            clientId: config.oidcClientId,
            grant_type: "refresh_token",
            refresh_token: token.refreshToken,
          }),
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
