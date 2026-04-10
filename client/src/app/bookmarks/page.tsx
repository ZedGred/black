"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import LandingLayout from "@/layouts/landing";
import { articleService } from "@/services/article.service";
import { AuthUtils } from "@/lib/auth";
import { format } from "date-fns";
import { Bookmark, BookmarkX, Loader2, BookOpen } from "lucide-react";
import toast from "react-hot-toast";

export default function BookmarksPage() {
  const [articles, setArticles] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [removing, setRemoving] = useState<string | null>(null);

  useEffect(() => {
    fetchBookmarks();
  }, []);

  const fetchBookmarks = async () => {
    try {
      const res = await articleService.getBookmarks();
      setArticles(res.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleRemoveBookmark = async (id: string, e: React.MouseEvent) => {
    e.preventDefault();
    try {
      setRemoving(id);
      await articleService.unbookmarkArticle(id);
      setArticles((prev) => prev.filter((a) => a.id !== id));
      toast.success("Removed from bookmarks");
    } catch (err) {
      toast.error("Failed to remove");
    } finally {
      setRemoving(null);
    }
  };

  return (
    <LandingLayout>
      <div className="pt-24 pb-16 px-4 max-w-3xl mx-auto">
        <div className="flex items-center gap-3 mb-8">
          <Bookmark className="text-white" size={28} />
          <div>
            <h1 className="text-3xl font-bold text-white">Bookmarks</h1>
            <p className="text-gray-400 text-sm mt-0.5">{articles.length} saved stories</p>
          </div>
        </div>

        {loading ? (
          <div className="flex justify-center py-20">
            <Loader2 className="animate-spin text-gray-400" size={32} />
          </div>
        ) : articles.length === 0 ? (
          <div className="text-center py-20">
            <BookOpen size={56} className="mx-auto mb-4 text-gray-700" />
            <p className="text-xl text-gray-400 mb-2">No bookmarks yet</p>
            <p className="text-gray-500 mb-6">Save stories to read them later</p>
            <Link
              href="/"
              className="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-black rounded-full font-medium hover:bg-gray-100 text-sm"
            >
              Explore Stories
            </Link>
          </div>
        ) : (
          <div className="space-y-1">
            {articles.map((article) => (
              <Link
                key={article.id}
                href={`/articles/${article.slug || article.id}`}
                className="group flex gap-4 py-5 border-b border-gray-800/50 hover:bg-gray-900/30 px-3 rounded-xl transition-colors"
              >
                {article.thumbnail && (
                  <div className="flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden bg-gray-800">
                    <img
                      src={article.thumbnail}
                      alt={article.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                  </div>
                )}
                <div className="flex-1 min-w-0">
                  <div className="flex items-start justify-between gap-2">
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2 mb-1">
                        <div className="w-5 h-5 rounded-full bg-gray-700 overflow-hidden flex-shrink-0">
                          {article.user?.avatar ? (
                            <img src={article.user.avatar} alt="" className="w-full h-full object-cover" />
                          ) : (
                            <span className="flex items-center justify-center h-full text-xs text-white">
                              {article.user?.name?.[0]}
                            </span>
                          )}
                        </div>
                        <span className="text-xs text-gray-500">{article.user?.name}</span>
                      </div>
                      <h3 className="font-semibold text-white line-clamp-2 mb-1 group-hover:text-gray-200">
                        {article.title}
                      </h3>
                      {article.excerpt && (
                        <p className="text-gray-400 text-sm line-clamp-1">{article.excerpt}</p>
                      )}
                      <div className="flex items-center gap-3 mt-2 text-xs text-gray-500">
                        <span>{article.likes_count || 0} likes</span>
                        {article.published_at && (
                          <span>{format(new Date(article.published_at), "MMM d")}</span>
                        )}
                      </div>
                    </div>
                    <button
                      onClick={(e) => handleRemoveBookmark(article.id, e)}
                      disabled={removing === article.id}
                      className="flex-shrink-0 p-2 text-yellow-500 hover:text-gray-400 hover:bg-gray-800 rounded-full transition-colors mt-1"
                      title="Remove bookmark"
                    >
                      {removing === article.id ? (
                        <Loader2 size={16} className="animate-spin" />
                      ) : (
                        <BookmarkX size={16} />
                      )}
                    </button>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>
    </LandingLayout>
  );
}
