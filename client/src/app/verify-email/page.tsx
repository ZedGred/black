"use client";

import { useState, FormEvent, Suspense } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import AuthLayout from "@/layouts/auth";
import toast from 'react-hot-toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { AuthUtils } from '@/lib/auth';

function VerifyEmailForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const token = searchParams.get("token") || "";

  const [form, setForm] = useState({
    password: "",
    password_confirmation: "",
  });

  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<null | string>(null);

  async function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setLoading(true);
    setError(null);

    if (form.password !== form.password_confirmation) {
      setError("Passwords do not match");
      setLoading(false);
      return;
    }

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/register/verify`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify({
          token,
          password: form.password,
          password_confirmation: form.password_confirmation
        }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data?.message ?? "Verification failed");
      }

      AuthUtils.setToken(data.token?.access_token || data.data?.token?.access_token);
      AuthUtils.setUser(data.user || data.data?.user);

      toast.success("Account successfully verified and created!");
      router.push("/");

    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
      <form onSubmit={handleSubmit} className="w-full max-w-md p-6">
        <h1 className="mb-2 text-center text-3xl font-bold text-white">
          Secure your account
        </h1>
        <p className="text-gray-400 text-center mb-6 text-sm">Please set a strong password to complete your registration.</p>

        {error && <div className="mb-4 rounded bg-red-100 p-2 text-sm text-red-700">{error}</div>}

        <div className="mb-4">
          <Label htmlFor="password" className="mb-1 block text-sm font-medium text-white">
            Password
          </Label>
          <Input
            id="password"
            name="password"
            type="password"
            placeholder="********"
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
            required
            minLength={6}
          />
        </div>

        <div className="mb-6">
          <Label htmlFor="password_confirmation" className="mb-1 block text-sm font-medium text-white">
            Confirm Password
          </Label>
          <Input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            placeholder="********"
            className="w-full rounded-lg border border-gray-300 px-3 py-2 text-black focus:outline-none focus:ring-2 focus:ring-blue-500"
            value={form.password_confirmation}
            onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
            required
            minLength={6}
          />
        </div>
        
        <Button 
          type="submit"
          disabled={loading || !token}
          className="mb-4 w-full rounded-3xl bg-white px-4 py-2 font-semibold text-black transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-50"
        >
          {loading ? "Verifying..." : "Complete Registration"}
        </Button>

        {!token && (
          <div className="rounded bg-yellow-100 p-2 text-sm text-yellow-800 text-center mb-4">
            Warning: Invalid or missing token.
          </div>
        )}
      </form>
    </div>
  );
}

export default function VerifyEmail() {
  return (
    <AuthLayout>
      {/* Right Side - Banner */}
      <div className="flex w-2/5 items-center justify-center bg-black text-white">
        <h2 className="text-9xl font-bold">BLACK</h2>
      </div>
      
      <Suspense fallback={<div className="flex w-3/5 items-center justify-center bg-neutral-950 text-white">Loading...</div>}>
        <VerifyEmailForm />
      </Suspense>
    </AuthLayout>
  );
}
