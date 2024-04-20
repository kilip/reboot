import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";
import { auth } from "./auth";
import {SessionProvider} from "next-auth/react";
import {ReactNode} from "react";


const inter = Inter({ subsets: ["latin"] });

export const metadata: Metadata = {
  title: "Reboot",
  description: "The Homelab Command Center",
};

export default async function RootLayout({
  children,
}:{
  children: ReactNode;
}) {
  const session = await auth();
  return (
    <html lang="en">
      <body className={inter.className}>
      <SessionProvider session={session}>
        {children}
      </SessionProvider>
      </body>
    </html>
  );
}
