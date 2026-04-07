"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import LandingLayout from "@/layouts/landing";
import { articleService } from "@/services/article.service";
import { Article } from "@/types/article";
import { format } from "date-fns";

export default function ArticlesPage() {
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);

  useEffect(() => {
    fetchArticles();
  }, [page]);

  const fetchArticles = async () => {
    try {
      setLoading(true);
      const response = await articleService.getArticles({
        page,
        per_page: 10,
        status: "published",
      });
      
      if (page === 1) {
        setArticles(response.data);
      } else {
        setArticles((prev) => [...prev, ...response.data]);
      }
      
      setHasMore(response.meta?.current_page < response.meta?.last_page);
    } catch (error) {
      console.error("Failed to fetch articles:", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <LandingLayout>
      <div className="pt-24 pb-12 px-4 max-w-6xl mx-auto">
        <div className="flex items-center justify-between mb-8">
          <h1 className="text-4xl font-bold text-white">Articles</h1>
          <Link href="/articles/create" className="px-4 py-2 bg-white text-black rounded-lg hover:bg-gray-200">
            Write Article
          </Link>
        </div>

        {loading && articles.length === 0 ? (
          <div className="text-center text-gray-400 py-12">Loading...</div>
        ) : articles.length === 0 ? (
          <div className="text-center text-gray-400 py-12">
            <p className="text-xl">No articles found</p>
            <Link href="/articles/create" className="text-blue-400 hover:underline mt-2 inline-block">
              Be the first to write an article!
            </Link>
          </div>
        ) : (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {articles.map((article) => (
              <Link
                key={article.id}
                href={`/articles/${article.slug || article.id}`}
                className="bg-gray-900 rounded-lg overflow-hidden hover:bg-gray-800 transition border border-gray-800"
              >
                {article.thumbnail && (
                  <div className="h-48 overflow-hidden">
                    <img
                      src={article.thumbnail}
                      alt={article.title}
                      className="w-full h-full object-cover"
                    />
                  </div>
                )}
                <div className="p-4">
                  {article.categories && article.categories.length > 0 && (
                    <span className="text-xs text-blue-400">
                      {article.categories[0].name}
                    </span>
                  )}
                  <h2 className="text-xl font-semibold text-white mt-2 line-clamp-2">
                    {article.title}
                  </h2>
                  {article.excerpt && (
                    <p className="text-gray-400 text-sm mt-2 line-clamp-2">
                      {article.excerpt}
                    </p>
                  )}
                  <div className="flex items-center justify-between mt-4 text-sm text-gray-500">
                    <div className="flex items-center gap-2">
                      <div className="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center">
                        {article.user?.avatar ? (
                          <img src={article.user.avatar} alt="" className="w-full h-full rounded-full" />
                        ) : (
                          <span className="text-xs">{article.user?.name?.[0]}</span>
                        )}
                      </div>
                      <span>{article.user?.name}</span>
                    </div>
                    <span>
                      {article.published_at
                        ? format(new Date(article.published_at), "MMM d, yyyy")
                        : ""}
                    </span>
                  </div>
                </div>
              </Link>
            ))}
          </div>
        )}

        {hasMore && (
          <div className="text-center mt-8">
            <button
              onClick={() => setPage((p) => p + 1)}
              disabled={loading}
              className="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50"
            >
              {loading ? "Loading..." : "Load More"}
            </button>
          </div>
        )}
      </div>
    </LandingLayout>
  );
}
