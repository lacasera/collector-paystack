import "../css/collector.css";
import React from "react";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { Toaster } from "react-hot-toast";
import axios from "axios";

// Configure axios globally
window.axios = axios;
axios.defaults.headers.common["Content-Type"] = "application/json";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Configure CSRF token if available
const token = document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
if (token) {
  axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

// Rendered by the server into the page head. A build-time value could never
// work here: this bundle ships pre-built inside the package, so it is compiled
// long before any application installs it.
const appNameMeta = document.head.querySelector(
  'meta[name="collector-app-name"]',
) as HTMLMetaElement | null;
const appName = appNameMeta?.content || "Billing";

createInertiaApp({
  title: (title: string) => `${title} - ${appName}`,
  resolve: (name: string) => {
    const pages = import.meta.glob(
      ["./Pages/**/*.tsx", "!./Pages/**/*.test.tsx"],
      { eager: true },
    );
    return pages[`./Pages/${name}.tsx`] as any;
  },
  setup({ el, App, props }) {
    const root = createRoot(el);
    root.render(
      <React.StrictMode>
        <App {...props} />
        <Toaster position="top-right" />
      </React.StrictMode>
    );
  },
  progress: {
    color: "#4B5563",
  },
});
