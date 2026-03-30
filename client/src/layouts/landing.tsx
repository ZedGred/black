import { ReactNode } from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';

type Props = { children: ReactNode }

export default function LandingLayout({ children }: Props) {
  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />
        {children}
      <Footer />
    </div>
  );
}
