// resources/js/components/layout/app-shell.tsx
import { useEffect, useState, type ReactNode } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import {
    CalendarDays,
    LayoutDashboard,
    LogOut,
    Menu,
    Settings,
    X,
} from 'lucide-react';
import { observer } from 'mobx-react-lite';
import { cn } from '@/lib/utils';
import { useStore } from '@/context/StoreContext';
import { BrandLogo } from './brand-logo';
import { ThemeToggle } from '../theme-toggle';
import { Avatar } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';

interface NavItem {
    href: string;
    label: string;
    icon: typeof LayoutDashboard;
}

const primaryNav: NavItem[] = [
    { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { href: '/settings', label: 'Settings', icon: Settings },
];

function useIsActive() {
    const { pathname } = useLocation();
    return (href: string) =>
        pathname === href ||
        (href !== '/dashboard' && pathname.startsWith(href));
}

// ============== Sidebar ==============
interface SidebarContentProps {
    onNavigate?: () => void;
}

const SidebarContent = observer(function SidebarContent({
    onNavigate,
}: SidebarContentProps) {
    const isActive = useIsActive();
    const { pathname } = useLocation();
    const { auth, events } = useStore();
    const navigate = useNavigate();

    const handleLogout = async () => {
        await auth.logout();
        navigate('/login');
    };

    return (
        <div className="flex h-full flex-col bg-sidebar text-sidebar-foreground">
            {/* Логотип */}
            <div className="flex h-16 items-center px-5">
                <Link
                    to="/dashboard"
                    onClick={onNavigate}
                    aria-label="Reviewa home"
                >
                    <BrandLogo tone="onDark" />
                </Link>
            </div>

            {/* Навигация */}
            <nav
                className="flex-1 overflow-y-auto px-3 py-4"
                aria-label="Main"
            >
                <ul className="flex flex-col gap-1">
                    {primaryNav.map((item) => {
                        const Icon = item.icon;
                        const active = isActive(item.href);
                        return (
                            <li key={item.href}>
                                <Link
                                    to={item.href}
                                    onClick={onNavigate}
                                    aria-current={active ? 'page' : undefined}
                                    className={cn(
                                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                                        active
                                            ? 'bg-sidebar-primary text-sidebar-primary-foreground'
                                            : 'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                                    )}
                                >
                                    <Icon className="size-5 shrink-0" />
                                    {item.label}
                                </Link>
                            </li>
                        );
                    })}
                </ul>

                {/* Мои мероприятия */}
                <p className="mt-6 mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/50">
                    Мои мероприятия
                </p>
                <ul className="flex flex-col gap-1">
                    {events.items.map((event) => {
                        const href = `/events/${event.id}`;
                        const active = pathname.startsWith(href);
                        return (
                            <li key={event.id}>
                                <Link
                                    to={href}
                                    onClick={onNavigate}
                                    aria-current={active ? 'page' : undefined}
                                    className={cn(
                                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors',
                                        active
                                            ? 'bg-sidebar-accent text-sidebar-accent-foreground'
                                            : 'text-sidebar-foreground/75 hover:bg-sidebar-accent/60 hover:text-sidebar-accent-foreground',
                                    )}
                                >
                                    <CalendarDays className="size-4 shrink-0 opacity-70" />
                                    <span className="truncate">{event.title}</span>
                                </Link>
                            </li>
                        );
                    })}
                </ul>
            </nav>

            {/* Профиль */}
            <div className="border-t border-sidebar-border p-3">
                <div className="flex items-center gap-3 rounded-lg px-2 py-2">
                    <Avatar
                        name={auth.user?.name ?? 'User'}
                        className="bg-sidebar-primary"
                    />
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-medium text-sidebar-foreground">
                            {auth.user?.name ?? 'Гость'}
                        </p>
                        <p className="truncate text-xs text-sidebar-foreground/60">
                            {auth.user?.role ?? ''}
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={handleLogout}
                        aria-label="Sign out"
                        className="grid size-8 place-items-center rounded-md text-sidebar-foreground/70 transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                    >
                        <LogOut className="size-4" />
                    </button>
                </div>
            </div>
        </div>
    );
});

// ============== AppShell ==============
interface AppShellProps {
    title: string;
    breadcrumb?: ReactNode;
    actions?: ReactNode;
    children: ReactNode;
}

export function AppShell({
    title,
    breadcrumb,
    actions,
    children,
}: AppShellProps) {
    const [drawerOpen, setDrawerOpen] = useState(false);
    const { pathname } = useLocation();
    const isActive = useIsActive();
    const { auth } = useStore();
    const navigate = useNavigate();

    useEffect(() => {
        setDrawerOpen(false);
    }, [pathname]);

    const handleLogout = async () => {
        await auth.logout();
        navigate('/login');
    };

    return (
        <div className="min-h-svh bg-background">
            {/* Sidebar (desktop) */}
            <aside className="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-sidebar-border lg:block">
                <SidebarContent />
            </aside>

            {/* Drawer (tablet) */}
            {drawerOpen && (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <div
                        className="absolute inset-0 bg-black/50"
                        onClick={() => setDrawerOpen(false)}
                        aria-hidden="true"
                    />
                    <div className="absolute inset-y-0 left-0 w-72 max-w-[80%] shadow-xl">
                        <SidebarContent
                            onNavigate={() => setDrawerOpen(false)}
                        />
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setDrawerOpen(false)}
                            aria-label="Close navigation"
                            className="absolute right-2 top-3 text-sidebar-foreground hover:bg-sidebar-accent"
                        >
                            <X />
                        </Button>
                    </div>
                </div>
            )}

            <div className="lg:pl-64">
                {/* Top bar */}
                <header className="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-border bg-background/85 px-4 backdrop-blur md:px-6">
                    <Button
                        variant="outline"
                        size="icon"
                        className="hidden md:inline-flex lg:hidden"
                        onClick={() => setDrawerOpen(true)}
                        aria-label="Open navigation"
                    >
                        <Menu />
                    </Button>

                    <div className="min-w-0 flex-1">
                        {breadcrumb && (
                            <div className="mb-0.5 hidden text-xs text-muted-foreground sm:block">
                                {breadcrumb}
                            </div>
                        )}
                        <h1 className="truncate text-base font-semibold leading-tight md:text-lg">
                            {title}
                        </h1>
                    </div>

                    <div className="flex items-center gap-2">
                        {actions}
                        <ThemeToggle />
                    </div>
                </header>

                <main className="mx-auto w-full max-w-[1400px] px-4 pb-24 pt-6 md:px-6 md:pb-10">
                    {children}
                </main>
            </div>

            {/* Bottom nav (mobile) */}
            <nav
                className="fixed inset-x-0 bottom-0 z-40 flex items-stretch border-t border-border bg-card/95 backdrop-blur md:hidden"
                aria-label="Primary"
            >
                {primaryNav.map((item) => {
                    const Icon = item.icon;
                    const active = isActive(item.href);
                    return (
                        <Link
                            key={item.href}
                            to={item.href}
                            aria-current={active ? 'page' : undefined}
                            className={cn(
                                'flex flex-1 flex-col items-center gap-1 py-2.5 text-xs font-medium transition-colors',
                                active ? 'text-primary' : 'text-muted-foreground',
                            )}
                        >
                            <Icon className="size-5" />
                            {item.label}
                        </Link>
                    );
                })}
                <button
                    type="button"
                    onClick={handleLogout}
                    className="flex flex-1 flex-col items-center gap-1 py-2.5 text-xs font-medium text-muted-foreground transition-colors"
                >
                    <LogOut className="size-5" />
                    Sign out
                </button>
            </nav>
        </div>
    );
}