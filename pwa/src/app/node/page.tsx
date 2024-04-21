"use client";

import Link from "next/link";

export default function Page() {
  return (
    <div className="flex flex-row min-h-screen items-center justify-between">
      <div className="flex flex-row items-center">
        <div className="flex">
          <h1>Node List</h1>
          <Link href="/">Back To Home</Link>
        </div>
      </div>
    </div>
  );
}
