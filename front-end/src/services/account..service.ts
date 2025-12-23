// services/account.service.ts
import { AxiosError } from "axios";
import { http } from "@/lib/http";
import type { AccountData, AccountResponse, ApiError } from "@/model/account";

export const createAccount = async (
  payload: AccountData
): Promise<AccountResponse> => {
  try {
    const { data } = await http.post<AccountResponse>(
      "/account-db/create",
      payload
    );

    return data;
  } catch (err) {
    if (err instanceof AxiosError) {
      const apiError = err.response?.data as ApiError | undefined;

      throw new Error(apiError?.message ?? "Failed to create account");
    }

    throw new Error("Unexpected error occurred");
  }
};

