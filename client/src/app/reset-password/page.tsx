"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { useSearchParams, useRouter } from "next/navigation";
import { http } from "@/lib/http";
import { KeyRound, ArrowLeft, Eye, EyeOff, CheckCircle, Loader2, AlertCircle } from "lucide-react";
import toast from "react-hot-toast";

export default function ResetPasswordPage() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const token = searchParams.get("token");

  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

  useEffect(() => {
    if (!token) {
      toast.error("Invalid or missing reset token");
    }
  }, [token]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (password !== confirm) {
      toast.error("Passwords do not match");
      return;
    }
    if (password.length < 8) {
      toast.error("Password must be at least 8 characters");
      return;
    }
    try {
      setLoading(true);
      await http.post("/reset-password", { token, password, password_confirmation: confirm });
      setSuccess(true);
      setTimeout(() => router.push("/login"), 3000);
    } catch (err: any) {
      toast.error(err?.response?.data?.message || "Failed to reset password. The link may have expired.");
    } finally {
      setLoading(false);
    }
  };

  if (!token) {
    return (
      <div className="min-h-screen bg-black flex items-center justify-center px-4">
        <div className="text-center">
          <div className="w-16 h-16 rounded-full bg-red-900/30 border border-red-700 flex items-center justify-center mx-auto mb-6">
            <AlertCircle className="text-red-400" size={32} />
          </div>
          <h1 className="text-2xl font-bold text-white mb-3">Invalid Reset Link</h1>
          <p className="text-gray-400 mb-6">This password reset link is invalid or has expired.</p>
          <Link href="/forgot-password" className="px-5 py-2.5 bg-white text-black rounded-full font-medium hover:bg-gray-100 text-sm">
            Request New Link
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-black flex items-center justify-center px-4">
      <div className="w-full max-w-md">
        {success ? (
          <div className="text-center">
            <div className="w-16 h-16 rounded-full bg-green-900/30 border border-green-700 flex items-center justify-center mx-auto mb-6">
              <CheckCircle className="text-green-400" size={32} />
            </div>
            <h1 className="text-2xl font-bold text-white mb-3">Password Reset!</h1>
            <p className="text-gray-400 mb-6">Your password has been reset successfully. Redirecting to login...</p>
            <Link href="/login" className="text-gray-400 hover:text-white text-sm underline">
              Go to login now
            </Link>
          </div>
        ) : (
          <>
            <Link href="/login" className="flex items-center gap-2 text-gray-400 hover:text-white mb-8 text-sm transition-colors w-fit">
              <ArrowLeft size={16} /> Back to login
            </Link>

            <div className="mb-8">
              <div className="w-12 h-12 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center mb-6">
                <KeyRound className="text-white" size={24} />
              </div>
              <h1 className="text-3xl font-bold text-white mb-2">Set new password</h1>
              <p className="text-gray-400">Your new password must be at least 8 characters.</p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-400 mb-2">New Password</label>
                <div className="relative">
                  <input
                    type={showPassword ? "text" : "password"}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Enter new password"
                    required
                    minLength={8}
                    className="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 pr-12 focus:outline-none focus:border-gray-500 transition-colors placeholder-gray-600"
                  />
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white"
                  >
                    {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                <input
                  type={showPassword ? "text" : "password"}
                  value={confirm}
                  onChange={(e) => setConfirm(e.target.value)}
                  placeholder="Confirm new password"
                  required
                  className={`w-full bg-gray-900 border text-white rounded-xl px-4 py-3 focus:outline-none transition-colors placeholder-gray-600 ${
                    confirm && password !== confirm ? "border-red-700 focus:border-red-600" : "border-gray-700 focus:border-gray-500"
                  }`}
                />
                {confirm && password !== confirm && (
                  <p className="text-red-400 text-xs mt-1">Passwords do not match</p>
                )}
              </div>

              <button
                type="submit"
                disabled={loading || !password || !confirm || password !== confirm}
                className="w-full flex items-center justify-center gap-2 py-3 bg-white text-black rounded-xl font-semibold hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {loading ? (
                  <><Loader2 size={18} className="animate-spin" /> Resetting...</>
                ) : (
                  "Reset Password"
                )}
              </button>
            </form>
          </>
        )}
      </div>
    </div>
  );
}
