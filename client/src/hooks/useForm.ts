import { useForm as useRHF, UseFormProps } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers";
import { z } from "zod";

type FormOptions<T> = Omit<UseFormProps<T>, "resolver">;

export function useForm<T>(schema: z.ZodType<T>, options?: FormOptions<T>) {
  return useRHF<T>({
    resolver: zodResolver(schema),
    ...options,
  });
}

export { z };