// resources/js/api/client.ts
import { AxiosError, type AxiosRequestConfig } from 'axios';
import apiClient from '@/bootstrap';
import type { ValidationErrorResponse } from '@/types';

export class ApiClient {
    static async get<T>(
        url: string,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        const response = await apiClient.get<T>(url, config);
        return response.data;
    }

    static async post<T>(
        url: string,
        data?: unknown,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        const response = await apiClient.post<T>(url, data, config);
        return response.data;
    }

    static async put<T>(
        url: string,
        data?: unknown,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        const response = await apiClient.put<T>(url, data, config);
        return response.data;
    }

    static async patch<T>(
        url: string,
        data?: unknown,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        const response = await apiClient.patch<T>(url, data, config);
        return response.data;
    }

    static async delete<T>(
        url: string,
        config?: AxiosRequestConfig,
    ): Promise<T> {
        const response = await apiClient.delete<T>(url, config);
        return response.data;
    }
}

/**
 * Извлекает читаемое сообщение об ошибке из ответа API
 */
export function extractErrorMessage(error: unknown): string {
    if (error instanceof AxiosError) {
        const data = error.response?.data as
            | ValidationErrorResponse
            | undefined;

        if (data?.errors) {
            const firstError = Object.values(data.errors)[0];
            if (Array.isArray(firstError) && firstError.length > 0) {
                return firstError[0] ?? data.message;
            }
        }

        if (data?.message) {
            return data.message;
        }

        if (error.code === 'ECONNABORTED') {
            return 'Превышено время ожидания запроса';
        }

        if (!error.response) {
            return 'Ошибка сети. Проверьте подключение.';
        }
    }

    if (error instanceof Error) {
        return error.message;
    }

    return 'Произошла неизвестная ошибка';
}