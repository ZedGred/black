"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import LandingLayout from "@/layouts/landing";
import { AuthUtils } from "@/lib/auth";
import { articleService } from "@/services/article.service";
import { notificationService } from "@/services/notification.service";
import { AuthUser } from "@/types/user";
import { Article } from "@/types/article";
import { format } from "date-fns";

export default function Home() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [user, setUser] = useState<AuthUser | null>(null);
  const [articles, setArticles] = useState<Article[]>([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(true);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
    const token = AuthUtils.getToken();
    const userData = AuthUtils.getUser();
    
    if (token && userData) {
      setIsLoggedIn(true);
      setUser(userData);
      fetchDashboardData();
    }
    setLoading(false);
  }, []);

  const fetchDashboardData = async () => {
    try {
      const [articlesRes, notificationsRes] = await Promise.all([
        articleService.getArticles({ per_page: 5 }),
        notificationService.getUnreadCount()
      ]);
      
      setArticles(articlesRes.data);
      setUnreadCount(notificationsRes);
    } catch (error) {
      console.error("Failed to fetch dashboard data:", error);
    }
  };

  if (loading || !mounted) {
    return (
      <LandingLayout>
        <div className="flex min-h-screen items-center justify-center">
          <div className="text-white">Loading...</div>
        </div>
      </LandingLayout>
    );
  }

  if (isLoggedIn) {
    return (
      <LandingLayout>
        <div className="pt-24 pb-12 px-4 max-w-2xl mx-auto flex flex-col gap-8">
          <div className="border-b border-gray-800 pb-0">
            <div className="flex gap-8 text-sm font-medium">
              <span className="text-white border-b-2 border-white pb-3 cursor-default">For you</span>
              <span className="text-gray-500 hover:text-gray-300 pb-3 cursor-pointer transition-colors">Following</span>
            </div>
          </div>
          
          <div className="space-y-10">
            {articles.length === 0 ? (
              <p className="text-gray-400 py-10 text-center flex flex-col items-center">
                <span className="text-4xl mb-4">✍️</span>
                <span>No articles yet. Be the first to write one!</span>
              </p>
            ) : (
              articles.map((article) => (
                <article key={article.id} className="border-b border-gray-800 pb-10 last:border-0 group">
                  <div className="flex items-center gap-2 mb-3">
                     <div className="w-6 h-6 rounded-full bg-gray-800 overflow-hidden flex items-center justify-center">
                       {typeof (article.user as any)?.avatar === 'string' && (article.user as any)?.avatar ? (
                         <img src={(article.user as any).avatar} alt={article.user?.name} className="w-full h-full object-cover" />
                       ) : (
                         <span className="text-[10px] text-white font-bold">{article.user?.name?.charAt(0).toUpperCase() || 'U'}</span>
                       )}
                     </div>
                     <span className="text-sm text-gray-300 font-medium">{article.user?.name}</span>
                     <span className="text-gray-500 text-xs">•</span>
                     <span className="text-sm text-gray-500">{article.published_at ? format(new Date(article.published_at), 'MMM d') : 'Recent'}</span>
                  </div>
                  <Link href={`/articles/${article.slug || article.id}`} className="block cursor-pointer">
                    <h2 className="text-xl md:text-2xl font-bold text-white mb-2 group-hover:text-gray-200 transition-colors leading-tight">
                      {article.title}
                    </h2>
                    <p className="text-gray-400 line-clamp-2 text-sm md:text-base leading-relaxed mb-4">
                      {/* Short placeholder excerpt for a more professional feed look */}
                      Immerse yourself in this story to explore its full depth. Click to read the full experience...
                    </p>
                  </Link>
                  <div className="flex justify-between items-center mt-2">
                    <div className="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                      <span className="bg-gray-900 border border-gray-800 px-3 py-1 rounded-full text-gray-300">Story</span>
                      <span>4 min read</span>
                    </div>
                  </div>
                </article>
              ))
            )}
          </div>
        </div>
      </LandingLayout>
    );
  }

  return (
    <LandingLayout>
      <div className="relative flex flex-col justify-center px-6 md:px-16 lg:px-24 bg-black w-full overflow-hidden" style={{ minHeight: 'calc(100vh - 80px)' }}>
        
        {/* Right Side Aesthetic: Cosmic Universe Semi-Circle */}
        <div className="absolute right-0 top-0 bottom-0 pointer-events-none flex items-center justify-end overflow-hidden w-full">
          {/* Outer Atmospheric Glow */}
          <div className="absolute top-1/2 right-0 translate-x-[40%] -translate-y-1/2 w-[100vh] h-[100vh] rounded-full bg-white/[0.015] blur-[80px]"></div>
          
          {/* Core Cosmic Body (Half visible on right) */}
          <div className="absolute top-1/2 right-0 translate-x-[60%] -translate-y-1/2 w-[90vh] h-[90vh] rounded-full bg-black border-l border-white/30 border-t border-t-white/10 border-b border-b-white/10 shadow-[0_0_120px_rgba(255,255,255,0.08),inset_40px_0_100px_rgba(255,255,255,0.05)] opacity-90 backdrop-blur-sm">
            {/* Inner Texture highlights */}
            <div className="absolute inset-0 rounded-full bg-[radial-gradient(ellipse_at_left,_var(--tw-gradient-stops))] from-white/10 via-white/[0.02] to-transparent opacity-50 mix-blend-screen blur-[2px]"></div>
          </div>
          
          {/* Orbital Ring / Stellar Halo */}
          <div className="absolute top-1/2 right-0 translate-x-[50%] -translate-y-1/2 w-[95vh] h-[95vh] rounded-full border border-white/5 opacity-50 border-dashed animate-[spin_200s_linear_infinite]"></div>
          
          {/* Deep Core Glow */}
          <div className="absolute top-1/2 right-0 translate-x-[75%] -translate-y-1/2 w-[60vh] h-[60vh] rounded-full bg-white/[0.04] blur-[120px]"></div>
        </div>

        {/* Scattered Fireflies spanning across the screen */}
        <div className="absolute inset-0 pointer-events-none overflow-hidden">
          {/* Right side cluster */}
          <div className="absolute top-[35%] right-[45vw] w-1.5 h-1.5 rounded-full bg-white/80 blur-[0.5px] shadow-[0_0_12px_rgba(255,255,255,1)] animate-ping" style={{ animationDuration: '3.5s' }}></div>
          <div className="absolute top-[18%] right-[25vw] w-1 h-1 rounded-full bg-white/60 shadow-[0_0_8px_rgba(255,255,255,0.8)] animate-pulse" style={{ animationDuration: '4s' }}></div>
          <div className="absolute top-[55%] right-[65vw] w-2 h-2 rounded-full bg-white/40 blur-[1px] shadow-[0_0_20px_rgba(255,255,255,0.6)] animate-pulse" style={{ animationDuration: '5.5s' }}></div>
          <div className="absolute top-[80%] right-[35vw] w-1.5 h-1.5 rounded-full bg-white/90 shadow-[0_0_10px_rgba(255,255,255,1)] animate-ping" style={{ animationDuration: '4.8s' }}></div>
          <div className="absolute top-[10%] right-[40vw] w-2.5 h-2.5 rounded-full bg-white/30 blur-[2px] shadow-[0_0_15px_rgba(255,255,255,0.5)] animate-pulse" style={{ animationDuration: '6.5s' }}></div>
          <div className="absolute top-[65%] right-[15vw] w-1 h-1 rounded-full bg-white/70 shadow-[0_0_8px_rgba(255,255,255,0.7)] animate-pulse" style={{ animationDuration: '3.2s' }}></div>
          <div className="absolute top-[85%] right-[55vw] w-2 h-2 rounded-full bg-white/50 blur-[1px] shadow-[0_0_12px_rgba(255,255,255,0.8)] animate-ping" style={{ animationDuration: '5.2s' }}></div>
          <div className="absolute top-[25%] right-[10vw] w-1.5 h-1.5 rounded-full bg-white/80 shadow-[0_0_10px_rgba(255,255,255,0.9)] animate-pulse" style={{ animationDuration: '4.5s' }}></div>

          {/* Left side cluster */}
          <div className="absolute top-[45%] left-[25vw] w-2 h-2 rounded-full bg-white/30 blur-[2px] shadow-[0_0_15px_rgba(255,255,255,0.5)] animate-pulse" style={{ animationDuration: '6s' }}></div>
          <div className="absolute top-[70%] left-[10vw] w-1 h-1 rounded-full bg-white/70 shadow-[0_0_5px_rgba(255,255,255,0.8)] animate-ping" style={{ animationDuration: '5s' }}></div>
          <div className="absolute top-[15%] left-[20vw] w-1.5 h-1.5 rounded-full bg-white/50 blur-[1px] shadow-[0_0_10px_rgba(255,255,255,0.6)] animate-pulse" style={{ animationDuration: '4.2s' }}></div>
          <div className="absolute top-[85%] left-[30vw] w-2.5 h-2.5 rounded-full bg-white/20 blur-[3px] shadow-[0_0_20px_rgba(255,255,255,0.3)] animate-ping" style={{ animationDuration: '7s' }}></div>
          <div className="absolute top-[30%] left-[5vw] w-1 h-1 rounded-full bg-white/90 shadow-[0_0_8px_rgba(255,255,255,1)] animate-pulse" style={{ animationDuration: '3.8s' }}></div>
          <div className="absolute top-[60%] left-[35vw] w-1.5 h-1.5 rounded-full bg-white/60 blur-[0.5px] shadow-[0_0_12px_rgba(255,255,255,0.7)] animate-ping" style={{ animationDuration: '5.8s' }}></div>
        </div>

        {/* Main Content (62% visual dominance approx) */}
        <div className="max-w-[85vw] md:max-w-[65vw] relative z-10 w-full mb-[5vh] lg:mb-[12vh]">
          {/* Eyebrow Label */}
          <div className="flex items-center gap-3 mb-6 md:mb-8 ml-2 md:ml-4">
            <div className="w-8 h-[1px] bg-white/40"></div>
            <span className="text-white/40 text-[10px] md:text-xs uppercase tracking-[0.4em] font-medium">A Premium Writing Platform</span>
          </div>

          <h1 className="text-[5.5rem] sm:text-[8rem] md:text-[10rem] lg:text-[12rem] xl:text-[13rem] font-black leading-[0.85] tracking-tighter text-white mb-6 md:mb-8 transition-all drop-shadow-2xl">
            BLACK
          </h1>
          
          <div className="pl-4 md:pl-6 border-l-[1.5px] border-white/20 ml-2 md:ml-4">
            <p className="text-base sm:text-lg md:text-xl xl:text-2xl text-gray-300 font-light leading-relaxed max-w-sm md:max-w-md xl:max-w-xl transition-all">
              Where ideas take shape. Write, share, and discover stories that matter in a place designed for deep reading and meaningful writing.
            </p>
            
            {/* Elegant Minimal CTA */}
            <div className="mt-10 md:mt-12">
               <Link href="/register" className="group inline-flex items-center gap-4 text-white hover:text-gray-300 transition-colors">
                  <span className="text-xs md:text-sm uppercase tracking-[0.2em] font-medium">Explore the Void</span>
                  <span className="w-8 h-[1px] bg-white group-hover:w-16 transition-all duration-300"></span>
               </Link>
            </div>
          </div>
        </div>

        {/* Scroll Indicator */}
        <div className="absolute bottom-0 left-[10vw] md:left-[15vw] flex flex-col items-center gap-3 overflow-hidden h-32 md:h-40 z-10 opacity-60">
          <span className="text-white/30 text-[9px] uppercase tracking-[0.4em] font-light rotate-180" style={{ writingMode: 'vertical-rl' }}>Scroll</span>
          <div className="w-[1px] h-full bg-gradient-to-b from-white/30 to-transparent"></div>
        </div>

      </div>
    </LandingLayout>
  );
}
