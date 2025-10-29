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

const appName = import.meta.env.VITE_APP_NAME || "Collector PayStack";

createInertiaApp({
  title: (title: string) => `${title} - ${appName}`,
  resolve: (name: string) => {
    const pages = import.meta.glob("./Pages/**/*.tsx", { eager: true });
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
