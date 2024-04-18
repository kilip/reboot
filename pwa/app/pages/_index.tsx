import { json, LoaderFunctionArgs } from "@remix-run/node";
import { Link } from "@remix-run/react";
import { getSession } from "~/pkg/session.server";

export async function loader({ request }: LoaderFunctionArgs) {
  const auth = await getSession();
  console.log(auth.get("rebootToken"));
  return json({});
}

export default function Page() {
  return (
    <div>
      <h1 className="font-bold text-2xl">Olympus Command Center</h1>

      <Link to="/auth/login">Login</Link>
    </div>
  );
}
