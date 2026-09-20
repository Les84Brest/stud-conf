// resources/js/stores/AuthStore.ts
import { makeAutoObservable, runInAction } from 'mobx';
import { authApi } from '@/api/auth.api';
import { extractErrorMessage } from '@/api/client';
import type { LoginRequest, User } from '@/types';

const TOKEN_KEY = 'auth_token';

export type LoadingState = 'idle' | 'loading' | 'success' | 'error';

export class AuthStore {
    user: User | null = null;
    token: string | null = localStorage.getItem(TOKEN_KEY);
    loadingState: LoadingState = 'idle';
    error: string | null = null;
    initialized = false;

    constructor() {
        makeAutoObservable(this, {}, { autoBind: true });
    }

    // ============ Геттеры ============
    get isAuthenticated(): boolean {
        return !!this.token && !!this.user;
    }

    get isAdmin(): boolean {
        return this.user?.role === 'admin';
    }

    get isExpert(): boolean {
        return this.user?.role === 'expert';
    }

    get isObserver(): boolean {
        return this.user?.role === 'observer';
    }

    get isLoading(): boolean {
        return this.loadingState === 'loading';
    }

    // ============ Действия ============

    /**
     * Вход в систему
     */
    async login(credentials: LoginRequest): Promise<boolean> {
        this.loadingState = 'loading';
        this.error = null;

        try {
            const response = await authApi.login(credentials);

            runInAction(() => {
                this.user = response.user;
                this.token = response.token;
                this.loadingState = 'success';
                this.initialized = true;
                localStorage.setItem(TOKEN_KEY, response.token);
            });

            return true;
        } catch (error) {
            runInAction(() => {
                this.error = extractErrorMessage(error);
                this.loadingState = 'error';
            });
            return false;
        }
    }

    /**
     * Загрузить текущего пользователя (при старте приложения)
     */
    async fetchUser(): Promise<boolean> {
        if (!this.token) {
            this.initialized = true;
            return false;
        }

        this.loadingState = 'loading';

        try {
            const user = await authApi.me();

            runInAction(() => {
                this.user = user;
                this.loadingState = 'success';
                this.initialized = true;
            });

            return true;
        } catch (error) {
            runInAction(() => {
                this.user = null;
                this.token = null;
                this.loadingState = 'error';
                this.initialized = true;
                localStorage.removeItem(TOKEN_KEY);
            });
            return false;
        }
    }

    /**
     * Выход из системы
     */
    async logout(): Promise<void> {
        try {
            if (this.token) {
                await authApi.logout();
            }
        } catch {
            // Игнорируем ошибки при выходе
        } finally {
            runInAction(() => {
                this.user = null;
                this.token = null;
                this.loadingState = 'idle';
                this.error = null;
                localStorage.removeItem(TOKEN_KEY);
            });
        }
    }

    /**
     * Сбросить ошибку
     */
    clearError(): void {
        this.error = null;
    }

    /**
     * Установить пользователя вручную (например, после регистрации)
     */
    setUser(user: User): void {
        this.user = user;
    }
}