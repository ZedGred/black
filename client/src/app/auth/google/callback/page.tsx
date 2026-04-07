'use client';

import { useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { AuthUtils } from '@/lib/auth';
import { authService } from '@/services/auth.service';
import Loading from '@/app/loading';

function GoogleCallbackContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const token = searchParams.get('token');
  const error = searchParams.get('error');

  useEffect(() => {
    if (error) {
      router.push('/login?error=google_auth_failed');
      return;
    }

    if (token) {
      AuthUtils.setToken(token);
      
      authService.me()
        .then((res) => {
          AuthUtils.setUser(res.data);
        })
        .catch(() => {
          AuthUtils.removeToken();
          router.push('/login?error=google_auth_failed');
        })
        .finally(() => {
          router.push('/');
        });
    } else {
      router.push('/login?error=no_token');
    }
  }, [token, error, router]);

  return null;
}

export default function GoogleCallback() {
  return (
    <Suspense fallback={<Loading />}>
      <Loading />
      <GoogleCallbackContent />
    </Suspense>
  );
}
