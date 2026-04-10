// src/app/layout.tsx
import '@/styles/globals.scss';
import { ReactNode } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { Providers } from './providers';

export const metadata = {
  title: 'Black',
  description: 'article platform for everyone',
};

type Props = { children: ReactNode };

export default function RootLayout({ children }: Props) {
  return (
    <Providers>
      <html lang="en" className="dark">
        <body className="bg-black text-white">
          {children}
          <Toaster position="top-center" />
        </body>
      </html>
    </Providers>
  );
}
