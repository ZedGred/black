import { useForm as useRHF, UseFormProps, FieldValues } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";

type FormOptions<T extends FieldValues> = Omit<UseFormProps<T>, "resolver">;

export function useForm<T extends FieldValues>(schema: z.ZodType<T>, options?: FormOptions<T>) {
  return useRHF<T>({
    resolver: zodResolver(schema),
    ...options,
  });
}

export { z };