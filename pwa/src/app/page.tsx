import { PagedCollection } from "@/types/collection";
import { fetchApi, FetchResponse } from "@/util/dataAccess";
import { buildUriFromFilters, FiltersProps } from "@/util/node";
import LoginButton from "../auth/components/LoginButton";
import { List, type Props as ListProps } from "../node/components/List";
import { type Node } from "../types/Node";
import { auth, Session } from "./auth";

interface Query extends URLSearchParams {
  page?: number | string | undefined;
  author?: string | undefined;
  title?: string | undefined;
  condition?: string | undefined;
  "condition[]"?: string | string[] | undefined;
  "order[title]"?: string | undefined;
}

async function getServerSideProps(
  query: Query,
  session: Session | null
): Promise<ListProps> {
  const filters: FiltersProps = { draft: false };
  const page = Number(query.page ?? 1);

  try {
    console.log(buildUriFromFilters("/nodes", filters));

    const response: FetchResponse<PagedCollection<Node>> | undefined =
      await fetchApi(
        buildUriFromFilters("/nodes", filters),
        {
          cache: "no-cache",
        },
        session
      );

    if (!response?.data) {
      throw new Error("Unable to retrieve data from /nodes.");
    }
    return { data: response.data, hubURL: response.hubURL, filters, page };
  } catch (error) {
    console.log(error);
  }

  return { data: null, hubURL: null, filters, page };
}

interface Props {
  searchParams: Query;
}

export default async function Home({ searchParams }: Props) {
  // @ts-ignore
  const session: Session | null = await auth();
  const props = await getServerSideProps(searchParams, session);
  return (
    <main className="flex flex-col min-w-full">
      <div className="flex flex-col">
        <h1 className="text-3xl block">Welcome to Reboot!</h1>
        <h2 className="text-lg block">The Command Center for your Homelab</h2>
        <LoginButton />
      </div>
      <List {...props} />
    </main>
  );
}
