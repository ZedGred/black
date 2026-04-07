"use client";

import { useState, FormEvent, Suspense, useEffect } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import AuthLayout from "@/layouts/auth";
import { toast } from 'sonner';
import { AuthUtils } from '@/lib/auth';
import {
  InputOTP,
  InputOTPGroup,
  InputOTPSlot,
} from "@/components/ui/input-otp";

function VerifyEmailForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const email = searchParams.get("email") || "";

  const [token, setToken] = useState("");
  const [loading, setLoading] = useState<boolean>(false);
  const [timeLeft, setTimeLeft] = useState<number>(60);
  const [isResending, setIsResending] = useState<boolean>(false);

  useEffect(() => {
    if (!email) {
      toast.error("No email specified for verification.");
      router.push("/register");
    }
  }, [email, router]);

  // Countdown timer for 60 seconds
  useEffect(() => {
    if (timeLeft <= 0) return;
    const intervalId = setInterval(() => {
      setTimeLeft((prev) => prev - 1);
    }, 1000);
    return () => clearInterval(intervalId);
  }, [timeLeft]);

  async function handleSubmit(e?: FormEvent<HTMLFormElement>) {
    if (e) e.preventDefault();
    if (token.length !== 6) {
      toast.error("Please enter the full 6-digit code.");
      return;
    }

    setLoading(true);

    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/register/verify`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify({ email, token }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data?.message ?? "Verification failed");
      }

      AuthUtils.setToken(data.token?.access_token || data.data?.token?.access_token);
      AuthUtils.setUser(data.user || data.data?.user);

      toast.success("Account successfully verified!");
      router.push("/");

    } catch (err: any) {
      toast.error(err.message);
    } finally {
      setLoading(false);
    }
  }

  async function handleResend() {
    setIsResending(true);
    try {
      const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3001'}/api/v1/register/resend`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify({ email }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data?.message ?? "Failed to resend code");
      }

      toast.success("A new verification code has been sent!");
      setTimeLeft(60); // Reset the 60 second timer
      setToken(""); // Clear the incorrect token
    } catch (err: any) {
      toast.error(err.message);
    } finally {
      setIsResending(false);
    }
  }

  return (
    <div className="flex w-3/5 items-center justify-center bg-neutral-950 shadow-lg">
      <form onSubmit={handleSubmit} className="w-full max-w-md p-6 text-center">
        <h1 className="mb-2 text-3xl font-bold text-white">
          Verify your account
        </h1>
        <p className="text-gray-400 mb-8 text-sm">
          We have sent a 6-digit verification code to <span className="font-semibold text-white">{email}</span>.
        </p>

        <div className="flex justify-center mb-10 gap-x-3">
          <InputOTP maxLength={6} value={token} onChange={setToken} className="gap-2">
            <InputOTPGroup>
              <InputOTPSlot index={0} className="w-12 h-14 text-2xl text-white rounded-md border" />
            </InputOTPGroup>
            <InputOTPGroup>
              <InputOTPSlot index={1} className="w-12 h-14 text-2xl text-white rounded-md border" />
            </InputOTPGroup>
            <InputOTPGroup>
              <InputOTPSlot index={2} className="w-12 h-14 text-2xl text-white rounded-md border" />
            </InputOTPGroup>
            <InputOTPGroup>
              <InputOTPSlot index={3} className="w-12 h-14 text-2xl text-white rounded-md border" />
            </InputOTPGroup>
            <InputOTPGroup>
              <InputOTPSlot index={4} className="w-12 h-14 text-2xl text-white rounded-md border" />
            </InputOTPGroup>
            <InputOTPGroup>
              <InputOTPSlot index={5} className="w-12 h-14 text-2xl text-white rounded-md border" />
            </InputOTPGroup>
          </InputOTP>
        </div>

        <button 
          type="submit"
          disabled={loading || token.length !== 6}
          className="mb-4 w-full rounded-3xl bg-white px-4 py-3 font-semibold text-black transition hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-50"
        >
          {loading ? "Verifying..." : "Verify Code"}
        </button>

        <div className="text-sm font-medium text-gray-400 mt-4">
          {timeLeft > 0 ? (
            <p>Resend code in <span className="text-white">{timeLeft}s</span></p>
          ) : (
            <button 
              type="button" 
              onClick={handleResend}
              disabled={isResending}
              className="text-blue-400 hover:text-blue-300 transition-colors focus:outline-none"
            >
              {isResending ? "Sending..." : "Resend Code"}
            </button>
          )}
        </div>
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
