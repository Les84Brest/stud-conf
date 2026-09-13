// resources/js/stores/AuthStore.ts
import { makeAutoObservable, runInAction } from 'mobx';
import type { User } from '@/types';

export class AuthStore {
    user: User | null = null;
    token: string | null = localStorage.getItem('auth_token');

    constructor() {
        makeAutoObservable(this, {}, { autoBind: true });
    }

    get isAuthenticated(): boolean {
        return !!this.token;
    }

    get isAdmin(): boolean {
        return this.user?.role === 'admin';
    }

    setUser(user: User | null) {
        this.user = user;
    }

    setToken(token: string | null) {
        this.token = token;
        if (token) {
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('auth_token');
        }
    }

    async logout(): Promise<void> {
        runInAction(() => {
            this.user = null;
            this.token = null;
            localStorage.removeItem('auth_token');
        });
    }
}