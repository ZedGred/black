import { ReactNode } from 'react';
import { Toaster } from 'react-hot-toast';


export default function AuthLayout({ children }: { children: ReactNode }) {
  return (
    <div className="flex min-h-screen flex-row">
      {children}
      <Toaster position="top-right" reverseOrder={false} />
    </div> 
  );
}
