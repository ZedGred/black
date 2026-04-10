"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import LandingLayout from "@/layouts/landing";
import { articleService, categoryService } from "@/services/article.service";
import { Category } from "@/types/article";
import { Button } from "@/components/ui/button";
import TiptapEditor from "@/components/editor/TiptapEditor";

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
      <div className="pt-24 pb-12 px-4 max-w-4xl mx-auto font-serif">
        <form onSubmit={handleSubmit} className="flex flex-col gap-6">
          <div className="mb-4">
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Title"
              className="w-full bg-transparent text-white font-bold text-5xl md:text-6xl placeholder-gray-600 focus:outline-none py-2"
              required
            />
          </div>

          <div className="-mt-8">
            <TiptapEditor
              content={content}
              onChange={setContent}
            />
          </div>

          {/* Settings Section underneath Editor */}
          <div className="mt-16 pt-8 border-t border-gray-800 font-sans">
            <h3 className="text-xl font-bold text-white mb-6">Publish Settings</h3>
            
            <div className="grid md:grid-cols-2 gap-8">
              <div className="space-y-6">
                <div>
                  <label className="block text-sm font-medium text-gray-400 mb-2">Excerpt (Optional)</label>
                  <textarea
                    value={excerpt}
                    onChange={(e) => setExcerpt(e.target.value)}
                    placeholder="Provide a brief summary of your story..."
                    className="w-full bg-gray-900/50 text-white rounded-lg p-3 border border-gray-800 focus:outline-none focus:border-gray-600"
                    rows={3}
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-400 mb-2">Categories</label>
                  <div className="flex flex-wrap gap-2">
                    {fetchingCategories ? (
                      <p className="text-gray-500 text-sm">Loading categories...</p>
                    ) : categories.length === 0 ? (
                      <p className="text-gray-500 text-sm">No categories available</p>
                    ) : (
                      categories.map((category) => (
                        <button
                          key={category.id}
                          type="button"
                          onClick={() => toggleCategory(category.id)}
                          className={`px-3 py-1.5 rounded-full text-xs font-medium transition-colors ${
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
              </div>

              <div className="space-y-6">
                <div>
                  <label className="block text-sm font-medium text-gray-400 mb-2">Thumbnail Cover URL (Optional)</label>
                  <input
                    type="url"
                    value={thumbnail}
                    onChange={(e) => setThumbnail(e.target.value)}
                    placeholder="https://example.com/image.jpg"
                    className="w-full bg-gray-900/50 text-white rounded-lg p-3 border border-gray-800 focus:outline-none focus:border-gray-600"
                  />
                  {thumbnail && (
                    <div className="mt-3 w-full h-40 rounded-lg overflow-hidden bg-gray-800 border border-gray-700">
                      <img src={thumbnail} alt="Thumbnail preview" className="w-full h-full object-cover" />
                    </div>
                  )}
                </div>

                <div className="pt-4 border-t border-gray-800">
                  <label className="flex items-center gap-3 text-gray-300 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={status === "published"}
                      onChange={(e) => setStatus(e.target.checked ? "published" : "draft")}
                      className="w-4 h-4 cursor-pointer accent-white"
                    />
                    <span className="text-sm font-medium">Publish immediately</span>
                  </label>
                </div>
              </div>
            </div>

            <div className="flex justify-end gap-4 mt-10">
              <Button
                type="button"
                variant="outline"
                onClick={() => router.back()}
                className="px-6 rounded-full border-gray-700 text-gray-300 hover:text-white"
              >
                Cancel
              </Button>
              <Button type="submit" disabled={loading} className="px-8 rounded-full bg-green-600 hover:bg-green-500 text-white font-medium">
                {loading
                  ? "Saving..."
                  : status === "published"
                  ? "Publish Story"
                  : "Save Draft"}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </LandingLayout>
  );
}
