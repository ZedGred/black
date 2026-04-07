"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { AuthUtils } from "@/lib/auth";
import { authService } from "@/services/auth.service";
import { notificationService } from "@/services/notification.service";
import { Bell, Menu, X, User, LogOut, Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

type Props = {
  sidebarExpanded?: boolean;
  onToggleSidebar?: () => void;
}

export default function Navbar({ sidebarExpanded = false, onToggleSidebar }: Props) {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [user, setUser] = useState<{ name: string; avatar?: string; username: string } | null>(null);
  const [unreadCount, setUnreadCount] = useState(0);
  const router = useRouter();

  useEffect(() => {
    const token = AuthUtils.getToken();
    const userData = AuthUtils.getUser();
    
    if (token && userData) {
      setIsLoggedIn(true);
      setUser(userData);
      fetchUnreadCount();
    }
  }, []);

  const fetchUnreadCount = async () => {
    try {
      const response = await notificationService.getUnreadCount();
      setUnreadCount(response.data);
    } catch (error) {
      console.error("Failed to fetch unread count:", error);
    }
  };

  const handleLogout = async () => {
    try {
      await authService.logout();
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      AuthUtils.removeToken();
      setIsLoggedIn(false);
      setUser(null);
      router.push("/login");
    }
  };

  return (
    <nav className="fixed w-full p-[var(--spacing-gr-md)] bg-black/90 text-white flex justify-between items-center border-b border-gray-800 backdrop-blur-md z-50">
      <div className="flex items-center gap-3" style={{ padding: '0 var(--spacing-gr-md)' }}>
        <button 
          onClick={onToggleSidebar}
          className="flex flex-col gap-1.5 p-2 hover:bg-gray-800 rounded-lg transition-colors"
        >
          <span className="w-5 h-0.5 bg-white rounded-full"></span>
          <span className="w-5 h-0.5 bg-white rounded-full"></span>
          <span className="w-5 h-0.5 bg-white rounded-full"></span>
        </button>
        <Link href="/" className="text-2xl font-bold">Black</Link>
      </div>

      {/* Desktop Navigation */}
      <div className="hidden md:flex items-center gap-[var(--spacing-gr-md)]">
        <Link href="/articles" className="text-sm hover:text-gray-300 transition">
          Articles
        </Link>
        
        {isLoggedIn ? (
          <>
            <Link href="/articles/create" className="text-sm hover:text-gray-300 transition flex items-center gap-1">
              <Plus size={16} /> Write
            </Link>
            
            {/* Notifications */}
            <Link href="/notifications" className="relative">
              <Bell size={20} />
              {unreadCount > 0 && (
                <span className="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                  {unreadCount > 9 ? "9+" : unreadCount}
                </span>
              )}
            </Link>

            {/* User Menu */}
            <div className="relative group">
              <button className="flex items-center space-x-2">
                <div className="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center overflow-hidden">
                  {user?.avatar ? (
                    <img src={user.avatar} alt={user.name} className="w-full h-full object-cover" />
                  ) : (
                    <User size={16} />
                  )}
                </div>
              </button>
              
              <div className="absolute right-0 mt-2 w-48 bg-gray-900 border border-gray-800 rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                <div className="p-3 border-b border-gray-800">
                  <p className="font-medium">{user?.name}</p>
                  <p className="text-xs text-gray-400">@{user?.username}</p>
                </div>
                <div className="p-1">
                  <Link href={`/users/${user?.username}`} className="block px-3 py-2 text-sm hover:bg-gray-800 rounded">
                    Profile
                  </Link>
                  <Link href="/account" className="block px-3 py-2 text-sm hover:bg-gray-800 rounded">
                    Account
                  </Link>
                  <button onClick={handleLogout} className="w-full text-left px-3 py-2 text-sm hover:bg-gray-800 rounded text-red-400">
                    Logout
                  </button>
                </div>
              </div>
            </div>
          </>
          ) : (
          <div className="flex gap-[var(--spacing-gr-md)]">
            <Link href="/login" className="px-[var(--spacing-gr-md)] py-2 rounded text-sm hover:text-gray-300">
              Sign in
            </Link>
            <Link href="/register" className="px-[var(--spacing-gr-md)] py-2 bg-white rounded-3xl text-black text-sm hover:bg-gray-200">
              Get Started
            </Link>
          </div>
        )}
      </div>

      {/* Mobile Menu Button */}
      <button className="md:hidden" onClick={() => setIsMenuOpen(!isMenuOpen)}>
        {isMenuOpen ? <X size={24} /> : <Menu size={24} />}
      </button>

      {/* Mobile Navigation */}
      {isMenuOpen && (
        <div className="absolute top-full left-0 right-0 bg-black border-b border-gray-800 p-[var(--spacing-gr-md)] md:hidden">
          <div className="flex flex-col gap-[var(--spacing-gr-md)]">
            <Link href="/articles" className="text-sm">
              Articles
            </Link>
            
            {isLoggedIn ? (
              <>
                <Link href="/articles/create" className="text-sm">
                  Write
                </Link>
                <Link href="/notifications" className="text-sm">
                  Notifications {unreadCount > 0 && `(${unreadCount})`}
                </Link>
                <Link href={`/users/${user?.username}`} className="text-sm">
                  Profile
                </Link>
                <Link href="/account" className="text-sm">
                  Account
                </Link>
                <button onClick={handleLogout} className="text-sm text-red-400">
                  Logout
                </button>
              </>
            ) : (
              <div className="space-y-2">
                <Link href="/login" className="block text-sm">
                  Sign in
                </Link>
                <Link href="/register" className="block text-sm bg-white text-black px-4 py-2 rounded-3xl text-center">
                  Get Started
                </Link>
              </div>
            )}
          </div>
        </div>
      )}
    </nav>
  );
}
