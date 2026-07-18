import React from "react";
import { usePage } from "@inertiajs/react";
import toast from "react-hot-toast";

interface LayoutProps {
  children: React.ReactNode;
}

export default function Layout({ children }: LayoutProps): React.JSX.Element {
  const { collector } = usePage().props as any;

  // Surfaces the flash the server shares after a payment is verified. The
  // Toaster itself is mounted once in app.tsx.
  const { success, error } = collector.flash ?? {};

  React.useEffect(() => {
    if (success) {
      toast.success(success);
    }

    if (error) {
      toast.error(error);
    }
  }, [success, error]);

  return (
    <div className="flex w-full flex-col bg-site">
      <header className="bg-transparent">
        <div className="max-w-5xl mx-auto px-5">
          <div className="flex justify-between h-16">
            <div className="flex">
              <div className="shrink-0 flex items-center">
                <a href={collector.urls.home}>
                  <h2 className="font-bold text-xl text-gray-800 leading-tight">
                    {collector.appName}
                  </h2>
                </a>
              </div>
            </div>
          </div>
        </div>
      </header>
      <main className="flex-1 py-6 pb-10">{children}</main>
    </div>
  );
}
