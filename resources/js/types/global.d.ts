/// <reference types="vite/client" />

import { AxiosStatic } from 'axios';

declare global {
  interface Window {
    axios: AxiosStatic;
  }
}

interface ImportMetaEnv {
  readonly VITE_APP_NAME?: string;
  // add more env variables as needed
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
  readonly glob: (pattern: string, options?: { eager?: boolean }) => Record<string, any>;
}