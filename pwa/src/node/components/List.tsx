"use client";

import { type NextPage } from "next";
import { PagedCollection } from "../../types/collection";
import { Node } from "../../types/Node";
import { useMercure } from "../../util/mercure";
import { FiltersProps } from "../../util/node";
import { Item } from "./Item";

export interface Props {
  data: PagedCollection<Node> | null;
  hubURL: string | null;
  filters: FiltersProps | null;
  page: number | null;
}

export const List: NextPage<Props> = ({
  data,
  hubURL,
  filters,
  page,
}: Props) => {
  const collection = useMercure(data, hubURL);

  return (
    <div className="flex flex-wrap gap-4">
      {!!collection && !!collection["hydra:member"] && (
        <>
          {collection["hydra:member"].length !== 0 &&
            collection["hydra:member"].map((node) => (
              <Item key={node["@id"]} node={node} />
            ))}
        </>
      )}
    </div>
  );
};
