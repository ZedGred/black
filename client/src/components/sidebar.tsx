"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Home, Bookmark, User, BookOpen, ChevronRight, ChevronLeft } from "lucide-react";
import { cn } from "@/lib/utils";

export default function Sidebar() {
  const [isExpanded, setIsExpanded] = useState(false);
  const pathname = usePathname();

  const links = [
    { href: "/", label: "Home", icon: Home },
    { href: "/bookmarks", label: "Bookmarks", icon: Bookmark },
    { href: "/account", label: "Profile", icon: User },
    { href: "/articles", label: "Stories", icon: BookOpen },
  ];

  return (
    <>
      {/* Sidebar Container */}
      <div 
        className={cn(
          "fixed top-0 left-0 h-screen bg-black border-r border-neutral-900 transition-all duration-300 z-40 flex flex-col pt-24",
          isExpanded ? "w-64" : "w-[72px]"
        )}
      >
        <div className="flex flex-col gap-2 px-3">
          {links.map((link) => {
            const Icon = link.icon;
            const isActive = pathname === link.href;
            return (
              <Link
                key={link.href}
                href={link.href}
                className={cn(
                  "flex items-center gap-4 p-3 rounded-lg transition-colors group",
                  isActive ? "bg-neutral-900" : "hover:bg-neutral-900/50"
                )}
              >
                <div className="flex items-center justify-center min-w-[24px]">
                  <Icon className={cn("w-6 h-6", isActive ? "text-white" : "text-neutral-400 group-hover:text-neutral-200")} />
                </div>
                {isExpanded && (
                  <span className={cn(
                    "text-sm font-medium whitespace-nowrap",
                    isActive ? "text-white" : "text-neutral-400 group-hover:text-neutral-200"
                  )}>
                    {link.label}
                  </span>
                )}
              </Link>
            )
          })}
        </div>
      </div>

      {/* Toggle Button docked on the LEFT side of the sidebar */}
      <button
        onClick={() => setIsExpanded(!isExpanded)}
        className={cn(
          "fixed top-[100px] z-50 flex items-center justify-center w-6 h-12 bg-black border-r-0 border-neutral-800 rounded-r-none rounded-l-md hover:bg-neutral-900 cursor-pointer transition-all duration-300 text-white",
          isExpanded ? "left-[256px]" : "left-[72px]"
        )}
      >
        {isExpanded ? <ChevronLeft size={16} /> : <ChevronRight size={16} />}
      </button>
    </>
  );
}
