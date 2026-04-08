import { ReactNode, useEffect, useState } from 'react';
import Navbar from '@/components/navbar';
import Footer from '@/components/footer';
import Sidebar from '@/components/sidebar';
import { AuthUtils } from '@/lib/auth';

type Props = { children: ReactNode }

export default function LandingLayout({ children }: Props) {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [mounted, setMounted] = useState(false);
  const [sidebarExpanded, setSidebarExpanded] = useState(false);

  useEffect(() => {
    const token = AuthUtils.getToken();
    setIsLoggedIn(!!token);
    setMounted(true);
  }, []);

  if (!mounted) return null;

  return (
    <div className="min-h-screen flex flex-col bg-black">
      <Navbar sidebarExpanded={sidebarExpanded} onToggleSidebar={() => setSidebarExpanded(!sidebarExpanded)} />
      {isLoggedIn ? (
        <div className="flex">
          <Sidebar isExpanded={sidebarExpanded} onToggle={() => setSidebarExpanded(!sidebarExpanded)} />
          <main className={`flex-1 ml-[72px] min-h-screen transition-all duration-300 pt-16 ${sidebarExpanded ? 'lg:ml-64' : ''}`}>
            {children}
          </main>
        </div>
      ) : (
        <>
          <div className="pt-16">
            {children}
          </div>
          <Footer />
        </>
      )}
    </div>
  );
}
