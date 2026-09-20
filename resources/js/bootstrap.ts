// resources/js/bootstrap.ts
import axios, {
    type AxiosError,
    type InternalAxiosRequestConfig,
} from 'axios';

const TOKEN_KEY = 'auth_token';

export const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
    withCredentials: true,
    timeout: 30000,
});

// ============ REQUEST INTERCEPTOR ============
apiClient.interceptors.request.use(
    (config: InternalAxiosRequestConfig) => {
        const token = localStorage.getItem(TOKEN_KEY);
        if (token && config.headers) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error: AxiosError) => Promise.reject(error),
);

// ============ RESPONSE INTERCEPTOR ============
apiClient.interceptors.response.use(
    (response) => response,
    (error: AxiosError) => {
        // 401 Unauthorized — токен истёк или невалидный
        if (error.response?.status === 401) {
            localStorage.removeItem(TOKEN_KEY);

            // Редирект на логин, если не на странице логина
            const isLoginPage = window.location.pathname === '/login';
            if (!isLoginPage) {
                window.location.href = '/login';
            }
        }

        // 403 Forbidden — нет прав
        if (error.response?.status === 403) {
            console.error('Доступ запрещен:', error.response.data);
        }

        // 500+ — ошибки сервера
        if ((error.response?.status ?? 0) >= 500) {
            console.error('Ошибка сервера:', error.response?.data);
        }

        return Promise.reject(error);
    },
);

export default apiClient;