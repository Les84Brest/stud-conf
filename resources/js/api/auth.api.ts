// resources/js/api/auth.api.ts
import { ApiClient } from './client';
import type {
    LoginRequest,
    LoginResponse,
    User,
} from '@/types';

export interface ChangePasswordRequest {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
}

export const authApi = {
    /**
     * Вход в систему
     */
    login: (data: LoginRequest) =>
        ApiClient.post<LoginResponse>('/login', data),

    /**
     * Выход из системы
     */
    logout: () =>
        ApiClient.post<{ message: string }>('/logout'),

    /**
     * Получить текущего пользователя
     */
    me: () => ApiClient.get<User>('/me'),

    /**
     * Проверить статус авторизации
     */
    check: () =>
        ApiClient.get<{ authenticated: boolean; user: User | null }>('/check'),

    /**
     * Сменить пароль
     */
    changePassword: (data: ChangePasswordRequest) =>
        ApiClient.post<{ message: string }>('/change-password', data),
};