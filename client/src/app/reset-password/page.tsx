"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { useSearchParams, useRouter } from "next/navigation";
import { http } from "@/lib/http";
import { KeyRound, ArrowLeft, Eye, EyeOff, CheckCircle, Loader2, AlertCircle } from "lucide-react";
import { toast } from "sonner";
import { z } from "zod";
import { useForm } from "@/hooks/useForm";

const resetPasswordSchema = z.object({
  password: z.string().min(8, "Password must be at least 8 characters"),
  confirm: z.string().min(1, "Confirm password is required"),
}).refine((data) => data.password === data.confirm, {
  message: "Passwords do not match",
  path: ["confirm"],
});

type ResetPasswordForm = z.infer<typeof resetPasswordSchema>;

export default function ResetPasswordPage() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const token = searchParams.get("token");

  const [showPassword, setShowPassword] = useState(false);
  const [focusedField, setFocusedField] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

  const { register, handleSubmit, formState: { errors }, setError, watch } = useForm(resetPasswordSchema, {
    defaultValues: { password: "", confirm: "" },
  });

  const watchedValues = watch();

  useEffect(() => {
    if (!token) {
      toast.error("Invalid or missing reset token");
      router.replace("/forgot-password");
    }
  }, [token, router]);

  const onSubmit = async (data: ResetPasswordForm) => {
    if (!token) return;
    try {
      setLoading(true);
      await http.post("/reset-password", { token, password: data.password, password_confirmation: data.confirm });
      setSuccess(true);
      toast.success("Password reset successfully!");
      setTimeout(() => router.push("/login"), 2000);
    } catch (err) {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const responseData = (err as any)?.response?.data;
      if (responseData?.errors) {
        Object.entries(responseData.errors).forEach(([field, messages]) => {
          setError(field as keyof ResetPasswordForm, { message: (messages as string[])[0] });
        });
      } else {
        toast.error(responseData?.message || "Failed to reset password. The link may have expired.");
      }
    } finally {
      setLoading(false);
    }
  };

  const getLabelClass = (fieldName: string, hasError: boolean) => {
    const isFocused = focusedField === fieldName;
    const hasValue = !!watchedValues[fieldName as keyof typeof watchedValues];
    const base = "pointer-events-none absolute left-3 transition-all duration-200";
    const position = isFocused || hasValue ? "top-1.5 text-xs" : "top-3.5 text-sm";
    const color = hasError ? "text-red-500" : isFocused || hasValue ? "text-gray-400" : "text-gray-500";
    return `${base} ${position} ${color}`;
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

            <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
              <div>
                <div className="relative">
                  <input
                    {...register("password")}
                    type={showPassword ? "text" : "password"}
                    className={`peer w-full rounded-lg border bg-gray-900 px-3 pb-2 pt-5 text-sm text-white placeholder-transparent focus:outline-none focus:ring-0 ${
                      errors.password ? "border-red-500 focus:border-red-500" : "border-gray-700 focus:border-gray-500"
                    }`}
                    placeholder="New password"
                    onFocus={() => setFocusedField("password")}
                    onBlur={() => setFocusedField(null)}
                  />
                  <label className={getLabelClass("password", !!errors.password)}>New Password</label>
                  <button
                    type="button"
                    onClick={() => setShowPassword(!showPassword)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white"
                  >
                    {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                  </button>
                </div>
                {errors.password && <p className="mt-1 text-xs text-red-500">{errors.password.message}</p>}
              </div>

              <div>
                <div className="relative">
                  <input
                    {...register("confirm")}
                    type={showPassword ? "text" : "password"}
                    className={`peer w-full rounded-lg border bg-gray-900 px-3 pb-2 pt-5 text-sm text-white placeholder-transparent focus:outline-none focus:ring-0 ${
                      errors.confirm ? "border-red-500 focus:border-red-500" : "border-gray-700 focus:border-gray-500"
                    }`}
                    placeholder="Confirm password"
                    onFocus={() => setFocusedField("confirm")}
                    onBlur={() => setFocusedField(null)}
                  />
                  <label className={getLabelClass("confirm", !!errors.confirm)}>Confirm Password</label>
                </div>
                {errors.confirm && <p className="mt-1 text-xs text-red-500">{errors.confirm.message}</p>}
              </div>

              <button
                type="submit"
                disabled={loading}
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
