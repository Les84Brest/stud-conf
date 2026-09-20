// resources/js/stores/AuthStore.ts
import { makeAutoObservable, runInAction } from 'mobx';
import { authApi } from '@/api/auth.api';
import { extractErrorMessage } from '@/api/client';
import type { LoginRequest, User } from '@/types';
import type { RootStore } from './RootStore';

const TOKEN_KEY = 'auth_token';

export type LoadingState = 'idle' | 'loading' | 'success' | 'error';

export class AuthStore {
    user: User | null = null;
    token: string | null = localStorage.getItem(TOKEN_KEY);
    loadingState: LoadingState = 'idle';
    error: string | null = null;
    initialized = false;

    private rootStore: RootStore;

    constructor(rootStore: RootStore) {
        this.rootStore = rootStore;
        makeAutoObservable(this, { rootStore: false }, { autoBind: true });
    }

    get isAuthenticated(): boolean {
        return !!this.user && !!this.token;
    }

    get isAdmin(): boolean {
        return this.user?.role === 'admin';
    }

    get isExpert(): boolean {
        return this.user?.role === 'expert';
    }

    get isLoading(): boolean {
        return this.loadingState === 'loading';
    }

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
        } catch {
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

    async logout(): Promise<void> {
        try {
            if (this.token) {
                await authApi.logout();
            }
        } catch {
            // Игнорируем ошибки
        } finally {
            runInAction(() => {
                this.user = null;
                this.token = null;
                this.loadingState = 'idle';
                this.error = null;
                localStorage.removeItem(TOKEN_KEY);
            });

            // Очищаем связанные сторы
            this.rootStore.events.reset();
            this.rootStore.presentations.reset();
            this.rootStore.assessment.reset();
        }
    }

    clearError(): void {
        this.error = null;
    }

    setUser(user: User): void {
        this.user = user;
    }
}