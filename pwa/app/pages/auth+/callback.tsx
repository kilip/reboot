import { LoaderFunctionArgs } from "@remix-run/node";
import { authenticator } from "~/pkg/auth.server";

export async function loader({ request }: LoaderFunctionArgs) {
  return await authenticator.authenticate("authentik", request, {
    successRedirect: "/",
    failureRedirect: "/auth/error",
  });
}

/*
export const loader: LoaderFunction = async ({ request }) => {
  return await authenticator.authenticate("authentik", request, {
    successRedirect: "/",
    failureRedirect: "/auth/error.tsx",
  });
};
*/
