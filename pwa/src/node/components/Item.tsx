import { FunctionComponent } from "react";
import { Node } from "../../types/Node";

interface Props {
  node: Node;
}

export const Item: FunctionComponent<Props> = ({ node }) => {
  return (
    <div className="p-8 bg-white" data-testid="node">
      <div className="items-center">
        <p>{node.hostname}</p>
      </div>
      <div className="items-center">
        <p className="font-sans">{node.online ? "Online" : "Offline"}</p>
      </div>
    </div>
  );
};
