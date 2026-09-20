// resources/js/api/profile.api.ts
import { ApiClient } from './client';
import type { User } from '@/types';

export interface UpdateProfileRequest {
    name: string;
    email: string;
    affiliation?: string | null;
}

export interface ChangePasswordRequest {
    current_password: string;
    new_password: string;
    new_password_confirmation: string;
}

interface UpdateProfileResponse {
    message: string;
    user: User;
}

interface ChangePasswordResponse {
    message: string;
}

export const profileApi = {
    /**
     * Обновить профиль (имя, email).
     */
    update: (data: UpdateProfileRequest) =>
        ApiClient.patch<UpdateProfileResponse>('/profile', data),

    /**
     * Сменить пароль.
     */
    changePassword: (data: ChangePasswordRequest) =>
        ApiClient.post<ChangePasswordResponse>('/change-password', data),
};