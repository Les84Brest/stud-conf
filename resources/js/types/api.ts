// resources/js/types/api.ts
import type { User } from './models';

/**
 * Стандартный ответ API
 */
export interface ApiResponse<T> {
    data: T;
    message?: string;
}

/**
 * Ответ с пагинацией
 */
export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}

/**
 * Запрос на вход
 */
export interface LoginRequest {
    email: string;
    password: string;
}

/**
 * Ответ после успешного входа
 */
export interface LoginResponse {
    message: string;
    user: User;
    token: string;
    token_type: string;
}

/**
 * Ошибка валидации (422)
 */
export interface ValidationErrorResponse {
    message: string;
    errors: Record<string, string[]>;
}

/**
 * Общая ошибка API
 */
export interface ApiErrorResponse {
    message: string;
}