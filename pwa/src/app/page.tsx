"use client";

import { signIn, signOut, useSession } from "next-auth/react";

export default function Home() {
  const { data: session, status } = useSession();

  const handleLogin = () => {
    signIn("authentik", {
      redirect: true,
      callbackUrl: "/",
    });
  };

  const handleLogout = () => {
    signOut();
  };

  return (
    <main className="flex min-h-screen flex-col items-center justify-between p-24">
      <div className="flex-row items-center justify-between">
        <div className="flex-row">
          <h1 className="flex-row text-2xl font-extrabold">Olympus</h1>
          <span className="flex-row block">The homelab command center</span>
          <span>{status}</span>
        </div>

        <div className="flex-row">
          <button onClick={handleLogin}>Login</button>
          <button onClick={handleLogout}>Logout</button>
        </div>
      </div>
    </main>
  );
}
