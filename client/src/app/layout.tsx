// src/app/layout.tsx
import '@/styles/globals.scss';
import { ReactNode } from 'react';

export const metadata = {
  title: 'Black',
  description: 'article platform for everyone',
};

type Props = { children: ReactNode }

export default function RootLayout({ children }: Props) {
  return (
    <html lang="en">
      <body>
        {children}
      </body>
    </html>
  );
}

