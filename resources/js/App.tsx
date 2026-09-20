// resources/js/App.tsx
import { useEffect } from "react";
import { Routes, Route, Navigate } from "react-router-dom";
import { observer } from "mobx-react-lite";
import { useAuth } from "@/hooks/useAuth";
import { ProtectedRoute } from "@/components/common/ProtectedRoute";
import EventPage from "@/pages/expert/EventPage";
import LoginPage from "@/pages/auth/LoginPage";
import AssessmentPage from "@/pages/expert/AssessmentPage";
import DashboardPage from "@/pages/expert/DashboardPage";
import SettingsPage from '@/pages/expert/SettingsPage';

const App = observer(function App() {
    const auth = useAuth();

    // Загружаем пользователя при старте
    useEffect(() => {
        if (!auth.initialized) {
            void auth.fetchUser();
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
                        <DashboardPage />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/events/:eventId"
                element={
                    <ProtectedRoute>
                        <EventPage />
                    </ProtectedRoute>
                }
            />

            <Route
                path="/events/:eventId/presentations/:presentationId"
                element={
                    <ProtectedRoute>
                        <AssessmentPage />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/settings"
                element={
                    <ProtectedRoute>
                        <SettingsPage />
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
