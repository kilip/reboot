import { type Session } from "../app/auth";
import { config } from "./config";

const MIME_TYPE = "application/ld+json";

interface Violation {
  message: string;
  propertyPath: string;
}

export interface FetchResponse<TData> {
  hubURL: string | null;
  data: TData;
  text: string;
}

export interface FetchError {
  message: string;
  status: string;
  fields: { [ key: string ]: string; };
}

const extractHubURL = (response: Response): null | URL => {
  const linkHeader = response.headers.get("Link");
  if (!linkHeader) return null;

  const matches = linkHeader.match(
    /<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/
  );

  return matches && matches[ 1 ] ? new URL(matches[ 1 ], config.entrypoint) : null;
};

export const fetchApi = async <TData>(
  id: string,
  init: RequestInit = {},
  session?: Session | null
): Promise<FetchResponse<TData> | undefined> => {
  if (typeof init.headers === "undefined") init.headers = {};
  if (!init.headers.hasOwnProperty("Accept")) {
    init.headers = { ...init.headers, Accept: MIME_TYPE };
  }
  if (
    init.body !== undefined &&
    !(init.body instanceof FormData) &&
    !init.headers?.hasOwnProperty("Content-Type")
  ) {
    init.headers = { ...init.headers, "Content-Type": init.method === "PATCH" ? "application/merge-patch+json" : MIME_TYPE };
  }
  if (session && !init.headers?.hasOwnProperty("Authorization")) {
    // @ts-ignore
    init.headers = { ...init.headers, Authorization: `Bearer ${session?.accessToken}` };
  }
  console.log(config.entrypoint + id);
  const resp = await fetch(config.entrypoint + id, init);
  if (resp.status === 204) return;

  const text = await resp.text();
  const json = JSON.parse(text);
  if (resp.ok) {
    return {
      hubURL: extractHubURL(resp)?.toString() || null, // URL cannot be serialized as JSON, must be sent as string
      data: json,
      text,
    };
  }

  const errorMessage = json[ "hydra:title" ];
  const status = json[ "hydra:description" ] || resp.statusText;
  if (!json.violations) throw Error(errorMessage);
  const fields: { [ key: string ]: string; } = {};
  json.violations.map(
    (violation: Violation) =>
      (fields[ violation.propertyPath ] = violation.message)
  );

  throw { message: errorMessage, status, fields } as FetchError;
};

export const getItemPath = (
  uriVariables: string | object | undefined,
  pathTemplate: string
): string => {
  if (!uriVariables) {
    return "";
  }

  if (typeof uriVariables === "string") {
    uriVariables = { id: uriVariables.split("/").slice(-1)[ 0 ] };
  }

  // @ts-ignore
  [ ...pathTemplate.matchAll(/\[([^\]]+)\]/g) ].forEach((m) => {
    // @ts-ignore
    pathTemplate = pathTemplate.replace(m[ 0 ], uriVariables[ m[ 1 ] ]);
  });

  return pathTemplate;
};

export const parsePage = (path: string) =>
  parseInt(
    new RegExp(/[?&]page=(\d+)/).exec(path)?.[ 1 ] ?? "1",
    10
  );
