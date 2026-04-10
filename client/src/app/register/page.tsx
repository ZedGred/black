"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import Link from "next/link";
import { useForm } from "@/hooks/useForm";
import { z } from "zod";
import AuthLayout from "@/layouts/auth";
import { toast } from "sonner";

const registerSchema = z.object({
  name: z.string().min(1, "Name is required"),
  email: z.string().email("Invalid email address"),
  password: z.string().min(8, "Password must be at least 8 characters"),
});

type RegisterForm = z.infer<typeof registerSchema>;

export default function Register() {
  const router = useRouter();
  const [showPassword, setShowPassword] = useState(false);

  const { register, handleSubmit, formState: { errors }, setError } = useForm(registerSchema, {
    defaultValues: { name: "", email: "", password: "" },
  });

  const onSubmit = async (data: RegisterForm) => {
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/register/users`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify(data),
      });

      const response = await res.json();

      if (!res.ok) {
        if (response.errors) {
          Object.entries(response.errors).forEach(([field, messages]) => {
            setError(field as keyof RegisterForm, { message: (messages as string[])[0] });
          });
          return;
        }
        toast.error(response.message || "Registration failed");
        return;
      }

      toast.success("Verification code sent to your email!");
      router.push(`/verify-email?email=${encodeURIComponent(data.email)}`);
    } catch (error) {
      toast.error("An unexpected error occurred");
    }
  };

  const getLabelClass = (hasError: boolean, hasValue: boolean, isFocused: boolean) => {
    const base = "pointer-events-none absolute left-3 transition-all duration-200";
    const position = isFocused || hasValue ? "top-1.5 text-xs" : "top-3.5 text-sm";
    const color = hasError ? "text-red-500" : isFocused || hasValue ? "text-neutral-400" : "text-neutral-500";
    return `${base} ${position} ${color}`;
  };

  return (
    <AuthLayout>
      <div className="flex w-2/5 items-center justify-center bg-black text-white">
        <h2 className="text-9xl font-bold">BLACK</h2>
      </div>

      <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
        <form onSubmit={handleSubmit(onSubmit)} className="w-full max-w-md p-6">
          <h1 className="mb-6 text-center text-3xl font-bold text-white">Create an account</h1>

          <div className="mb-6">
            <div className="relative">
              <input
                {...register("name")}
                type="text"
                className={`peer w-full rounded-lg border bg-neutral-800 px-3 pb-2 pt-5 text-sm text-white placeholder-transparent focus:outline-none focus:ring-0 ${errors.name ? "border-red-500 focus:border-red-500" : "border-neutral-600 focus:border-white"}`}
                placeholder="Your name"
              />
              <label className={getLabelClass(!!errors.name, false, false)}>Name</label>
            </div>
            {errors.name && <p className="mt-1 text-xs text-red-500">{errors.name.message}</p>}
          </div>

          <div className="mb-6">
            <div className="relative">
              <input
                {...register("email")}
                type="email"
                className={`peer w-full rounded-lg border bg-neutral-800 px-3 pb-2 pt-5 text-sm text-white placeholder-transparent focus:outline-none focus:ring-0 ${errors.email ? "border-red-500 focus:border-red-500" : "border-neutral-600 focus:border-white"}`}
                placeholder="your@email.com"
              />
              <label className={getLabelClass(!!errors.email, false, false)}>Email</label>
            </div>
            {errors.email && <p className="mt-1 text-xs text-red-500">{errors.email.message}</p>}
          </div>

          <div className="mb-6">
            <div className="relative">
              <input
                {...register("password")}
                type={showPassword ? "text" : "password"}
                className={`peer w-full rounded-lg border bg-neutral-800 px-3 pb-2 pt-5 pr-10 text-sm text-white placeholder-transparent focus:outline-none focus:ring-0 ${errors.password ? "border-red-500 focus:border-red-500" : "border-neutral-600 focus:border-white"}`}
                placeholder="Password"
              />
              <label className={getLabelClass(!!errors.password, false, false)}>Password</label>

              <button
                type="button"
                tabIndex={-1}
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-white"
              >
                {showPassword ? (
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" /><line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                ) : (
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />
                  </svg>
                )}
              </button>
            </div>
            {errors.password && <p className="mt-1 text-xs text-red-500">{errors.password.message}</p>}
          </div>

          <button type="submit" className="mb-2 w-full rounded-3xl bg-white px-4 py-2 font-semibold text-black hover:bg-neutral-200">
            Create Account
          </button>

          <div className="my-4 flex items-center gap-2">
            <div className="h-px flex-1 bg-gray-600"></div>
            <span className="text-xs text-gray-400">or</span>
            <div className="h-px flex-1 bg-gray-600"></div>
          </div>

          <a href={`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/auth/google`} className="mb-6 flex w-full items-center justify-center gap-2 rounded-3xl border border-gray-500 bg-transparent px-4 py-2 font-semibold text-white hover:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            Continue with Google
          </a>

          <div className="text-center text-xs font-medium text-white">
            Already have an account?
            <Link href="/login" className="ml-1 text-blue-300 hover:underline">Login</Link>
          </div>
        </form>
      </div>
    </AuthLayout>
  );
}