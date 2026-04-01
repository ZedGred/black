"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import LandingLayout from "@/layouts/landing";
import { AuthUtils } from "@/lib/auth";
import { articleService } from "@/services/article.service";
import { notificationService } from "@/services/notification.service";
import { format } from "date-fns";

export default function Home() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [user, setUser] = useState<any>(null);
  const [articles, setArticles] = useState<any[]>([]);
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
        <div className="pt-24 pb-12 px-4 max-w-6xl mx-auto">
          <div className="mb-8">
            <h1 className="text-3xl font-bold text-white">
              Welcome back, {user?.name}!
            </h1>
            <p className="text-gray-400">Here's what's happening today.</p>
          </div>

          <div className="grid gap-6 md:grid-cols-3 mb-8">
            <Link href="/articles" className="bg-gray-900 p-6 rounded-lg border border-gray-800 hover:bg-gray-800">
              <div className="text-3xl font-bold text-white">{articles.length}</div>
              <div className="text-gray-400">Recent Articles</div>
            </Link>
            <Link href="/notifications" className="bg-gray-900 p-6 rounded-lg border border-gray-800 hover:bg-gray-800">
              <div className="text-3xl font-bold text-white">{unreadCount}</div>
              <div className="text-gray-400">Unread Notifications</div>
            </Link>
            <Link href="/articles/create" className="bg-gray-900 p-6 rounded-lg border border-gray-800 hover:bg-gray-800">
              <div className="text-3xl font-bold text-white">+</div>
              <div className="text-gray-400">Write Article</div>
            </Link>
          </div>

          <div className="bg-gray-900 rounded-lg border border-gray-800 p-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-bold text-white">Latest Articles</h2>
              <Link href="/articles" className="text-blue-400 hover:underline text-sm">
                View all
              </Link>
            </div>
            
            {articles.length === 0 ? (
              <p className="text-gray-400">No articles yet. Be the first to write one!</p>
            ) : (
              <div className="space-y-4">
                {articles.map((article) => (
                  <Link
                    key={article.id}
                    href={`/articles/${article.slug || article.id}`}
                    className="block p-4 bg-gray-800 rounded-lg hover:bg-gray-700"
                  >
                    <h3 className="text-white font-medium">{article.title}</h3>
                    <div className="flex items-center gap-4 mt-2 text-sm text-gray-400">
                      <span>By {article.author?.name}</span>
                      <span>{article.published_at ? format(new Date(article.published_at), 'MMM d, yyyy') : ''}</span>
                    </div>
                  </Link>
                ))}
              </div>
            )}
          </div>
        </div>
      </LandingLayout>
    );
  }

  return (
    <LandingLayout>
      <div className="flex min-h-screen items-center justify-center">
        <div className="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 h-64 w-full flex items-center justify-center">
          <h1 className="text-9xl font-bold text-black/50 bg-clip-text transition duration-300 hover:from-red-700 hover:animate-growShrink">
            GREETINGS !
          </h1>
        </div>
      </div>
    </LandingLayout>
  );
}
