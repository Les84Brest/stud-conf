// resources/js/pages/auth/LoginPage.tsx
import { useFormik } from 'formik';
import * as Yup from 'yup';
import { useNavigate, useLocation } from 'react-router-dom';
import { observer } from 'mobx-react-lite';
import { useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { BrandLogo } from '@/components/layout/brand-logo';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { LoginRequest } from '@/types';

const validationSchema = Yup.object({
    email: Yup.string()
        .email('Некорректный email')
        .required('Email обязателен'),
    password: Yup.string()
        .min(6, 'Минимум 6 символов')
        .required('Пароль обязателен'),
});

interface LocationState {
    from?: { pathname: string };
}

const LoginPage = observer(() => {
    const auth = useAuth();
    const navigate = useNavigate();
    const location = useLocation();

    const from = (location.state as LocationState)?.from?.pathname ?? '/dashboard';

    // Если уже авторизован — редирект
    useEffect(() => {
        if (auth.isAuthenticated) {
            navigate(from, { replace: true });
        }
    }, [auth.isAuthenticated, from, navigate]);

    const formik = useFormik<LoginRequest>({
        initialValues: {
            email: '',
            password: '',
        },
        validationSchema,
        onSubmit: async (values) => {
            const success = await auth.login(values);
            if (success) {
                navigate(from, { replace: true });
            }
        },
    });

    return (
        <div className="min-h-svh flex items-center justify-center bg-background p-4">
            <Card className="w-full max-w-md">
                <CardHeader className="text-center">
                    <div className="flex justify-center mb-4">
                        <BrandLogo />
                    </div>
                    <CardTitle className="text-2xl">Вход в систему</CardTitle>
                    <CardDescription>
                        Введите свои учетные данные для входа
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form
                        onSubmit={formik.handleSubmit}
                        className="space-y-4"
                        noValidate
                    >
                        {/* Общая ошибка */}
                        {auth.error && (
                            <div className="rounded-lg bg-destructive/10 border border-destructive/20 p-3 text-sm text-destructive">
                                {auth.error}
                            </div>
                        )}

                        {/* Email */}
                        <div className="space-y-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                autoComplete="email"
                                placeholder="user@example.com"
                                value={formik.values.email}
                                onChange={formik.handleChange}
                                onBlur={formik.handleBlur}
                                aria-invalid={
                                    formik.touched.email &&
                                    !!formik.errors.email
                                }
                            />
                            {formik.touched.email && formik.errors.email && (
                                <p className="text-sm text-destructive">
                                    {formik.errors.email}
                                </p>
                            )}
                        </div>

                        {/* Пароль */}
                        <div className="space-y-2">
                            <Label htmlFor="password">Пароль</Label>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                autoComplete="current-password"
                                placeholder="••••••••"
                                value={formik.values.password}
                                onChange={formik.handleChange}
                                onBlur={formik.handleBlur}
                                aria-invalid={
                                    formik.touched.password &&
                                    !!formik.errors.password
                                }
                            />
                            {formik.touched.password &&
                                formik.errors.password && (
                                    <p className="text-sm text-destructive">
                                        {formik.errors.password}
                                    </p>
                                )}
                        </div>

                        {/* Кнопка входа */}
                        <Button
                            type="submit"
                            className="w-full"
                            disabled={auth.isLoading}
                        >
                            {auth.isLoading ? 'Вход...' : 'Войти'}
                        </Button>
                    </form>

                    {/* Подсказка для разработки */}
                    {import.meta.env.DEV && (
                        <div className="mt-6 rounded-lg bg-muted p-3 text-xs text-muted-foreground">
                            <p className="font-semibold mb-1">
                                Тестовые данные:
                            </p>
                            <p>admin@conference.ru / admin123</p>
                            <p>expert1@conference.ru / expert123</p>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
});

export default LoginPage;