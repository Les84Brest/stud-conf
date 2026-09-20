// resources/js/app.tsx
import '../css/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { StoreProvider } from '@/context/StoreContext';
import { ThemeProvider } from '@/components/layout/theme-provider';
import App from './App';

const rootElement = document.getElementById('app');

if (!rootElement) {
    throw new Error('Root element #app not found');
}

ReactDOM.createRoot(rootElement).render(
    <React.StrictMode>
        <BrowserRouter>
            <StoreProvider>
                <ThemeProvider>
                    <App />
                </ThemeProvider>
            </StoreProvider>
        </BrowserRouter>
    </React.StrictMode>,
);