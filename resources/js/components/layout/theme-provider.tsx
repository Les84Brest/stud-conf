// resources/js/components/layout/theme-provider.tsx
import {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useState,
    type ReactNode,
} from 'react';

type Theme = 'light' | 'dark';

interface ThemeContextValue {
    theme: Theme;
    toggleTheme: () => void;
    setTheme: (theme: Theme) => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);

interface ThemeProviderProps {
    children: ReactNode;
}

export function ThemeProvider({ children }: ThemeProviderProps) {
    const [theme, setThemeState] = useState<Theme>('light');

    useEffect(() => {
        const stored = window.localStorage.getItem('theme') as Theme | null;
        const initial: Theme =
            stored ??
            (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        setThemeState(initial);
    }, []);

    const apply = useCallback((next: Theme) => {
        const root = document.documentElement;
        root.classList.toggle('dark', next === 'dark');
        window.localStorage.setItem('theme', next);
    }, []);

    const setTheme = useCallback(
        (next: Theme) => {
            setThemeState(next);
            apply(next);
        },
        [apply],
    );

    const toggleTheme = useCallback(() => {
        setTheme(theme === 'dark' ? 'light' : 'dark');
    }, [theme, setTheme]);

    return (
        <ThemeContext.Provider value={{ theme, toggleTheme, setTheme }}>
            {children}
        </ThemeContext.Provider>
    );
}

export function useTheme() {
    const ctx = useContext(ThemeContext);
    if (!ctx) throw new Error('useTheme must be used within ThemeProvider');
    return ctx;
}