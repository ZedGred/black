import { ReactNode, useEffect, useState } from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';
import { AuthUtils } from '@/lib/auth';

type Props = { children: ReactNode }

export default function LandingLayout({ children }: Props) {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    const token = AuthUtils.getToken();
    setIsLoggedIn(!!token);
    setMounted(true);
  }, []);

  if (!mounted) return null;

  return (
    <div className="min-h-screen flex flex-col bg-black">
      <Navbar />
      {children}
      {!isLoggedIn && <Footer />}
    </div>
  );
}
