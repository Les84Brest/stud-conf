// resources/js/app.tsx
import '../css/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { StoreProvider } from '@/context/StoreContext';
import { ThemeProvider } from '@/components/layout/theme-provider';
import { AppShell } from '@/components/layout/app-shell';

const rootElement = document.getElementById('app');

if (!rootElement) {
    throw new Error('Root element #app not found');
}

function DemoPage() {
    return (
        <AppShell
            title="Dashboard"
            breadcrumb="Главная / Обзор"
            actions={<button className="text-sm">Действие</button>}
        >
            <div className="rounded-xl border border-border bg-card p-6">
                <h2 className="text-lg font-semibold">Содержимое страницы</h2>
                <p className="text-muted-foreground mt-2">
                    Здесь будет основной контент.
                </p>
            </div>
        </AppShell>
    );
}

ReactDOM.createRoot(rootElement).render(
    <React.StrictMode>
        <BrowserRouter>
            <StoreProvider>
                <ThemeProvider>
                    <DemoPage />
                </ThemeProvider>
            </StoreProvider>
        </BrowserRouter>
    </React.StrictMode>,
);