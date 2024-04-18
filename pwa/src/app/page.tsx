"use client";
import { signIn, useSession } from "next-auth/react";

export default function Home() {
  const handleLogin = () => {
    signIn("authentik", {
      redirect: true,
      callbackUrl: "/",
    });
  };

  const { data: session, status } = useSession();
  console.log(session?.accessToken);
  return (
    <main className="flex min-h-screen flex-col items-center justify-between p-24">
      <div className="flex-row items-center justify-between">
        <h1 className="flex-row text-2xl font-extrabold">Olympus</h1>
        <span className="flex-row block">The homelab command center</span>

        <button onClick={handleLogin}>Login</button>
      </div>
    </main>
  );
}
