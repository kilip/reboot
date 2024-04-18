FROM node:20-slim AS base

ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"
RUN corepack enable \
	&& corepack prepare --activate pnpm@latest

COPY . /app
WORKDIR /app

# Development image
FROM base as dev

EXPOSE 5173
ENV PORT 5173
ENV HOSTNAME localhost

CMD ["sh", "-c", "pnpm install; pnpm dev --host"]

# base prod deps
FROM base as prod-deps
RUN --mount=type=cache,id=pnpm,target=/pnpm/store pnpm install --prod --frozen-lockfile

# base prod build
FROM base AS build
RUN --mount=type=cache,id=pnpm,target=/pnpm/store pnpm install --frozen-lockfile
RUN pnpm run build

# production image
FROM base AS prod
COPY --from=prod-deps /app/node_modules /app/node_modules
COPY --from=build /app/build /app/build

EXPOSE 3000
ENV PORT 3000
ENV HOSTNAME localhost

CMD ["pnpm","start"]
