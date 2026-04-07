'use client';

import { useRouter } from 'next/navigation';
import { FormEvent, useState } from 'react';
import Link from 'next/link';
import { AuthUtils } from '@/lib/auth';
import { authService } from '@/services/auth.service';
import { toast } from 'sonner';
import AuthLayout from '@/layouts/auth';

type User = {
  email: string;
  password: string;
};

export default function Login() {
  const [form, setForm] = useState<User>({
    email: '',
    password: '',
  });

  const router = useRouter();
  const [loading, setLoading] = useState<boolean>(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  
  const [showPassword, setShowPassword] = useState<boolean>(false);
  const [emailFocused, setEmailFocused] = useState<boolean>(false);
  const [passwordFocused, setPasswordFocused] = useState<boolean>(false);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setFieldErrors({});

    try {
      const response = await authService.login(form);

      if (!response.success) {
        throw new Error(response.message);
      }

      AuthUtils.setToken(response.data.token.access_token);
      AuthUtils.setUser(response.data.user);

      toast.success('Login successful!');
      router.push('/');
    } catch (err: any) {
      if (err.response?.data?.errors) {
        setFieldErrors(err.response.data.errors);
        if (err.response.data.message) {
          toast.error(err.response.data.message);
        }
      } else {
        const errorMessage = err.response?.data?.message || err.message || 'An unexpected error occurred';
        toast.error(errorMessage);
      }
    } finally {
      setLoading(false);
    }
  }

  const getLabelClass = (isFocused: boolean, hasValue: boolean, hasError: boolean) => {
    const base = "pointer-events-none absolute left-3 transition-all duration-200";
    const position = isFocused || hasValue ? "top-1.5 text-xs" : "top-3.5 text-sm";
    const color = hasError ? "text-destructive" : (isFocused || hasValue ? "text-neutral-400" : "text-neutral-500");
    return `${base} ${position} ${color}`;
  };

  return (
    <AuthLayout>
      {/* Left Side - Banner / Info */}
      <div className="flex w-2/5 items-center justify-center bg-black text-white">
        <h2 className="text-9xl font-bold">BLACK</h2>
      </div>

      {/* Right Side - Login Form */}
      <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
        <form onSubmit={handleSubmit} className="w-full max-w-md p-6">
          <h1 className="mb-6 text-center text-3xl font-bold text-white">Sign in to Black</h1>

          {/* Email Field with Floating Label */}
          <div className="mb-6">
            <div className="relative">
              <input
                id="email"
                name="email"
                type="email"
                className={`peer w-full rounded-lg border bg-neutral-800 px-3 pb-2 pt-5 text-sm text-white placeholder-transparent focus:outline-none focus:ring-0 ${fieldErrors.email ? 'border-destructive focus:border-destructive' : 'border-neutral-600 focus:border-white'}`}
                placeholder="your@email.com"
                value={form.email}
                onChange={(e) => setForm({ ...form, [e.target.name]: e.target.value })}
                onFocus={() => setEmailFocused(true)}
                onBlur={() => setEmailFocused(false)}
                required
              />
              <label
                htmlFor="email"
                className={getLabelClass(emailFocused, !!form.email, !!fieldErrors.email)}
              >
                Email
              </label>
            </div>
            {fieldErrors.email && (
              <p className="mt-1 text-xs text-destructive">{fieldErrors.email[0]}</p>
            )}
          </div>

          {/* Password Field with Floating Label + Show/Hide */}
          <div className="mb-6">
            <div className="relative">
              <input
                id="password"
                name="password"
                type={showPassword ? 'text' : 'password'}
                className={`peer w-full rounded-lg border bg-neutral-800 px-3 pb-2 pt-5 pr-10 text-sm text-white placeholder-transparent focus:outline-none focus:ring-0 ${fieldErrors.password ? 'border-destructive focus:border-destructive' : 'border-neutral-600 focus:border-white'}`}
                placeholder="Password"
                value={form.password}
                onChange={(e) => setForm({ ...form, [e.target.name]: e.target.value })}
                onFocus={() => setPasswordFocused(true)}
                onBlur={() => setPasswordFocused(false)}
                required
              />
              <label
                htmlFor="password"
                className={getLabelClass(passwordFocused, !!form.password, !!fieldErrors.password)}
              >
                Password
              </label>

              {/* Show / Hide toggle */}
              <button
                type="button"
                tabIndex={-1}
                onClick={() => setShowPassword((prev) => !prev)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-white focus:outline-none"
                aria-label={showPassword ? 'Hide password' : 'Show password'}
              >
                {showPassword ? (
                  /* Eye-off icon */
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                  </svg>
                ) : (
                  /* Eye icon */
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                  </svg>
                )}
              </button>
            </div>
            
            {fieldErrors.password && (
              <p className="mt-1 text-xs text-destructive">{fieldErrors.password[0]}</p>
            )}

            <Link
              href="/reset-password"
              className="mt-2 inline-block text-xs font-medium text-blue-300 hover:underline"
            >
              Forgot Your Password?
            </Link>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="mb-2 w-full rounded-3xl bg-white px-4 py-2 font-semibold text-black transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-50"
          >
            {loading ? 'Logging in...' : 'Log In'}
          </button>

          <div className="my-4 flex items-center gap-2">
            <div className="h-px flex-1 bg-gray-600"></div>
            <span className="text-xs text-gray-400">or</span>
            <div className="h-px flex-1 bg-gray-600"></div>
          </div>

          <a
            href={`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/auth/google`}
            className="mb-6 flex w-full items-center justify-center gap-2 rounded-3xl border border-gray-500 bg-transparent px-4 py-2 font-semibold text-white transition hover:bg-gray-800"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
            </svg>
            Continue with Google
          </a>

          <div className="text-center text-xs font-medium text-white">
            Need an account?
            <Link href="/register" className="ml-1 text-blue-300 hover:underline">
              Register
            </Link>
          </div>
        </form>
      </div>
    </AuthLayout>
  );
}
