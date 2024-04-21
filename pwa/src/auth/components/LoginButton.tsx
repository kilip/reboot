"use client";

import { signIn, signOut, useSession } from "next-auth/react";

export default function LoginButton() {
  const { data: session, status } = useSession();
  const handleLogin = () => {
    signIn("authentik");
  };

  const handleLogout = () => {
    signOut();
  };

  return (
    <div>
      {status === "authenticated" ? (
        <button onClick={handleLogout}>Logout</button>
      ) : (
        <button onClick={handleLogin}>Login</button>
      )}
    </div>
  );
}
