// resources/js/pages/NotFoundPage.tsx
import { Link } from 'react-router-dom';
import { Home, Search } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function NotFoundPage() {
    return (
        <div className="min-h-svh flex items-center justify-center bg-background p-4">
            <div className="text-center max-w-md">
                <p className="text-6xl font-bold text-primary">404</p>
                <h1 className="mt-4 text-2xl font-semibold">
                    Страница не найдена
                </h1>
                <p className="mt-2 text-muted-foreground">
                    Возможно, она была удалена или вы перешли по неверной
                    ссылке.
                </p>
                <div className="mt-6 flex justify-center gap-3">
                    <Button asChild>
                        <Link to="/dashboard">
                            <Home className="size-4" />
                            На главную
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    );
}