import React from "react";
import { Link, usePage } from "@inertiajs/react";

interface LayoutProps {
  children: React.ReactNode;
}

export default function Layout({ children }: LayoutProps): React.JSX.Element {
  const { collector } = usePage().props as any;

  return (
    <div className="flex w-full flex-col bg-site">
      <header className="bg-transparent">
        <div className="max-w-5xl mx-auto px-5">
          <div className="flex justify-between h-16">
            <div className="flex">
              <div className="shrink-0 flex items-center">
                <Link href="/">
                  <h2 className="font-bold text-xl text-gray-800 leading-tight">
                    {collector.appName}
                  </h2>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </header>
      <main className="flex-1 py-6 pb-10">{children}</main>
    </div>
  );
}
