// resources/js/hooks/useAuth.ts
import { useStore } from '@/context/StoreContext';

/**
 * Удобный хук для доступа к AuthStore
 */
export function useAuth() {
    const { auth } = useStore();
    return auth;
}