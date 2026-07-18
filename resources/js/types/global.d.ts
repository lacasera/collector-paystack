/// <reference types="vite/client" />

import { AxiosStatic } from 'axios';

declare global {
  interface Window {
    axios: AxiosStatic;
  }
}

interface ImportMeta {
  readonly glob: (pattern: string, options?: { eager?: boolean }) => Record<string, any>;
}