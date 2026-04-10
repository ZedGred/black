import { create } from "zustand";

type ThemeMode = "dark" | "light";

interface ThemeState {
  theme: ThemeMode;
  resolvedTheme: ThemeMode;
  setTheme: (theme: ThemeMode) => void;
  toggleTheme: () => void;
}

export const useThemeStore = create<ThemeState>((set) => ({
  theme: "dark",
  resolvedTheme: "dark",
  setTheme: (theme) => set({ theme, resolvedTheme: theme }),
  toggleTheme: () =>
    set((state) => ({
      theme: state.theme === "dark" ? "light" : "dark",
      resolvedTheme: state.theme === "dark" ? "light" : "dark",
    })),
}));