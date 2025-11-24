import "@/styles/globals.scss";
import type { AppProps } from "next/app";

export default function MyApp({
  Component,
  pageProps,
}: AppProps): React.JSX.Element {
  return <Component {...pageProps} />;
}
