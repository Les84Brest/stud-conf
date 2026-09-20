// resources/js/pages/expert/SettingsPage.tsx
import { AppShell } from '@/components/layout/app-shell';
import { SettingsPanel } from '@/components/common/settings-panel';

export default function SettingsPage() {
    return (
        <AppShell title="Настройки" breadcrumb="Главная / Настройки">
            <section className="mb-6">
                <h2 className="text-xl font-semibold md:text-2xl">
                    Настройки аккаунта
                </h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    Управляйте профилем, внешним видом и уведомлениями.
                </p>
            </section>

            <SettingsPanel />
        </AppShell>
    );
}