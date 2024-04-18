import { ActionFunctionArgs } from "@remix-run/node";
import { authenticator } from "~/pkg/auth.server";

export async function loader({ request }: ActionFunctionArgs) {
  return await authenticator.authenticate("authentik", request);
}
