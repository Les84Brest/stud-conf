// resources/js/components/common/ProtectedRoute.tsx
import { Navigate, useLocation } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useAuth } from '@/hooks/useAuth';
import type { ReactNode } from 'react';

interface ProtectedRouteProps {
    children: ReactNode;
    requiredRole?: 'admin' | 'expert' | 'observer';
}

export const ProtectedRoute = observer(function ProtectedRoute({
    children,
    requiredRole,
}: ProtectedRouteProps) {
    const auth = useAuth();
    const location = useLocation();

    if (!auth.isAuthenticated) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    if (requiredRole && auth.user?.role !== requiredRole) {
        return (
            <div className="min-h-svh flex items-center justify-center p-4">
                <div className="text-center">
                    <h1 className="text-2xl font-bold mb-2">
                        Доступ запрещён
                    </h1>
                    <p className="text-muted-foreground">
                        У вас нет прав для просмотра этой страницы
                    </p>
                </div>
            </div>
        );
    }

    return <>{children}</>;
});