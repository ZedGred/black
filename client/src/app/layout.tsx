// src/app/layout.tsx
import '@/styles/globals.scss';
import { ReactNode } from 'react';
import { Toaster } from '@/components/ui/sonner';

export const metadata = {
  title: 'Black',
  description: 'article platform for everyone',
};

type Props = { children: ReactNode };

export default function RootLayout({ children }: Props) {
  return (
    <html lang="en">
      <body>
        {children}
        <Toaster position="top-center" />
      </body>
    </html>
  );
}
