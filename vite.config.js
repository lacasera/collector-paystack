import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";

export default defineConfig({
  plugins: [
    laravel({
      // Single JS entry (it imports the CSS) so the bundle can be inlined as one
      // self-contained IIFE. See build.rollupOptions.output below.
      input: ["resources/js/app.tsx"],
      refresh: true,
    }),
    react(),
  ],
  resolve: {
    alias: {
      "@": new URL("./resources/js", import.meta.url).pathname,
    },
  },
  build: {
    outDir: "public",
    emptyOutDir: false,
    rollupOptions: {
      output: {
        // The billing portal inlines app.js into a classic <script> tag, so the
        // bundle must be a single self-contained IIFE — no ES import/export and
        // no code-split chunks.
        format: "iife",
        inlineDynamicImports: true,
        entryFileNames: "js/[name].js",
        // Force the extracted stylesheet name the backend inlines.
        assetFileNames: (asset) =>
          asset.name?.endsWith(".css")
            ? "css/collector.css"
            : "assets/[name][extname]",
      },
    },
  },
});
