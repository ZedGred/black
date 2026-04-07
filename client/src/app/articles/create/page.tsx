"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import LandingLayout from "@/layouts/landing";
import { articleService, categoryService } from "@/services/article.service";
import { Category } from "@/types/article";
import { Button } from "@/components/ui/button";

export default function CreateArticlePage() {
  const router = useRouter();
  const [title, setTitle] = useState("");
  const [content, setContent] = useState("");
  const [excerpt, setExcerpt] = useState("");
  const [thumbnail, setThumbnail] = useState("");
  const [selectedCategories, setSelectedCategories] = useState<string[]>([]);
  const [status, setStatus] = useState<"draft" | "published">("draft");
  const [categories, setCategories] = useState<Category[]>([]);
  const [loading, setLoading] = useState(false);
  const [fetchingCategories, setFetchingCategories] = useState(true);

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      const response = await categoryService.getCategories();
      setCategories(response.data);
    } catch (error) {
      console.error("Failed to fetch categories:", error);
    } finally {
      setFetchingCategories(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim() || !content.trim()) return;

    try {
      setLoading(true);
      const response = await articleService.createArticle({
        title,
        content,
        excerpt,
        thumbnail,
        category_ids: selectedCategories,
        status,
      });

      if (response.success) {
        router.push(`/articles/${response.data.slug || response.data.id}`);
      }
    } catch (error) {
      console.error("Failed to create article:", error);
    } finally {
      setLoading(false);
    }
  };

  const toggleCategory = (categoryId: string) => {
    setSelectedCategories((prev) =>
      prev.includes(categoryId)
        ? prev.filter((id) => id !== categoryId)
        : [...prev, categoryId]
    );
  };

  return (
    <LandingLayout>
      <div className="pt-24 pb-12 px-4 max-w-4xl mx-auto">
        <h1 className="text-3xl font-bold text-white mb-8">Write Article</h1>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label className="block text-sm font-medium text-gray-300 mb-2">Title</label>
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Enter article title..."
              className="w-full bg-gray-900 text-white rounded-lg p-3 border border-gray-800 focus:outline-none focus:border-gray-600"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-300 mb-2">Excerpt</label>
            <textarea
              value={excerpt}
              onChange={(e) => setExcerpt(e.target.value)}
              placeholder="Short description..."
              className="w-full bg-gray-900 text-white rounded-lg p-3 border border-gray-800 focus:outline-none focus:border-gray-600"
              rows={2}
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-300 mb-2">Thumbnail URL</label>
            <input
              type="url"
              value={thumbnail}
              onChange={(e) => setThumbnail(e.target.value)}
              placeholder="https://example.com/image.jpg"
              className="w-full bg-gray-900 text-white rounded-lg p-3 border border-gray-800 focus:outline-none focus:border-gray-600"
            />
            {thumbnail && (
              <div className="mt-2 w-full h-40 rounded-lg overflow-hidden bg-gray-800">
                <img src={thumbnail} alt="Thumbnail preview" className="w-full h-full object-cover" />
              </div>
            )}
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-300 mb-2">Categories</label>
            <div className="flex flex-wrap gap-2">
              {fetchingCategories ? (
                <p className="text-gray-500">Loading categories...</p>
              ) : categories.length === 0 ? (
                <p className="text-gray-500">No categories available</p>
              ) : (
                categories.map((category) => (
                  <button
                    key={category.id}
                    type="button"
                    onClick={() => toggleCategory(category.id)}
                    className={`px-3 py-1 rounded-full text-sm ${
                      selectedCategories.includes(category.id)
                        ? "bg-white text-black"
                        : "bg-gray-800 text-gray-300 hover:bg-gray-700"
                    }`}
                  >
                    {category.name}
                  </button>
                ))
              )}
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-300 mb-2">Content</label>
            <textarea
              value={content}
              onChange={(e) => setContent(e.target.value)}
              placeholder="Write your article content here..."
              className="w-full bg-gray-900 text-white rounded-lg p-3 border border-gray-800 focus:outline-none focus:border-gray-600 min-h-[300px]"
              required
            />
          </div>

          <div className="flex items-center gap-4">
            <label className="flex items-center gap-2 text-gray-300">
              <input
                type="checkbox"
                checked={status === "published"}
                onChange={(e) => setStatus(e.target.checked ? "published" : "draft")}
                className="w-4 h-4"
              />
              Publish immediately
            </label>
          </div>

          <div className="flex gap-4">
            <Button type="submit" disabled={loading} className="px-6">
              {loading
                ? "Saving..."
                : status === "published"
                ? "Publish"
                : "Save Draft"}
            </Button>
            <Button
              type="button"
              variant="outline"
              onClick={() => router.back()}
              className="px-6"
            >
              Cancel
            </Button>
          </div>
        </form>
      </div>
    </LandingLayout>
  );
}
