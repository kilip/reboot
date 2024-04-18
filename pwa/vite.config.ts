import { vitePlugin as remix } from "@remix-run/dev";
import { installGlobals } from "@remix-run/node";
import { flatRoutes } from "remix-flat-routes";
import { defineConfig } from "vite";
import tsconfigPaths from "vite-tsconfig-paths";

installGlobals();

export default defineConfig({
  plugins: [
    remix({
      ignoredRouteFiles: [ "**/*" ],
      routes: async defineRoutes => {
        const routeDirs = [
          'pages'
        ];
        return flatRoutes(routeDirs, defineRoutes);
      },
    }),
    tsconfigPaths()
  ],
  ssr: {
    noExternal: [ "@radix-ui/themes" ],
  },
  server: {
    // strictPort: true,
    hmr: {
      protocol: 'ws',
      clientPort: 5173
    },
  }
});
