// resources/js/App.tsx
import { useEffect } from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useAuth } from '@/hooks/useAuth';
import { ProtectedRoute } from '@/components/common/ProtectedRoute';
import LoginPage from '@/pages/auth/LoginPage';

// Временный дашборд (заменим позже)
function DashboardStub() {
    const auth = useAuth();
    return (
        <div className="p-8">
            <h1 className="text-2xl font-bold">
                Добро пожаловать, {auth.user?.name}!
            </h1>
            <p className="text-muted-foreground mt-2">
                Роль: {auth.user?.role}
            </p>
        </div>
    );
}

const App = observer(() => {
    const auth = useAuth();

    // Загружаем пользователя при старте
    useEffect(() => {
        if (!auth.initialized) {
            auth.fetchUser();
        }
    }, [auth]);

    // Пока грузится — показываем загрузку
    if (!auth.initialized && auth.token) {
        return (
            <div className="min-h-svh flex items-center justify-center">
                <p className="text-muted-foreground">Загрузка...</p>
            </div>
        );
    }

    return (
        <Routes>
            {/* Публичные */}
            <Route path="/login" element={<LoginPage />} />

            {/* Защищённые */}
            <Route
                path="/dashboard"
                element={
                    <ProtectedRoute>
                        <DashboardStub />
                    </ProtectedRoute>
                }
            />

            {/* Редиректы */}
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
    );
});

export default App;