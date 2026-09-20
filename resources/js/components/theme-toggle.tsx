// resources/js/components/layout/theme-toggle.tsx
import { Moon, Sun } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTheme } from './layout/theme-provider';

interface ThemeToggleProps {
    className?: string;
}

export function ThemeToggle({ className }: ThemeToggleProps) {
    const { theme, toggleTheme } = useTheme();

    return (
        <Button
            type="button"
            variant="outline"
            size="icon"
            className={className}
            onClick={toggleTheme}
            aria-label={
                theme === 'dark'
                    ? 'Switch to light theme'
                    : 'Switch to dark theme'
            }
        >
            {theme === 'dark' ? <Sun /> : <Moon />}
        </Button>
    );
}