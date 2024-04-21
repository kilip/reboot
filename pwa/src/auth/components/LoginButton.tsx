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
    <div className="flex w-full">
      <div className="flex flex-row">
        {status == "authenticated" ? (
          <button onClick={handleLogout}>Logout</button>
        ) : (
          <button onClick={handleLogin}>Login</button>
        )}
      </div>

      <div className="flex flex-row">
        <br />
        {status}
        {session?.expires}
      </div>
    </div>
  );
}
