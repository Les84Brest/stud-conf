// resources/js/components/common/settings-panel.tsx
import { useState, useEffect } from "react";
import { useFormik } from "formik";
import * as Yup from "yup";
import { Moon, Save, Sun, KeyRound, Loader2 } from "lucide-react";
import { observer } from "mobx-react-lite";
import { cn } from "@/lib/utils";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";
import { Avatar } from "@/components/ui/avatar";
import { useTheme } from "@/components/layout/theme-provider";
import { useAuth } from "@/hooks/useAuth";

// ============== SettingRow ==============
interface SettingRowProps {
    id: string;
    title: string;
    description: string;
    checked: boolean;
    onChange: (v: boolean) => void;
}

function SettingRow({
    id,
    title,
    description,
    checked,
    onChange,
}: SettingRowProps) {
    return (
        <div className="flex items-center justify-between gap-4 py-3">
            <div className="min-w-0">
                <Label htmlFor={id} className="cursor-pointer">
                    {title}
                </Label>
                <p className="mt-0.5 text-sm text-muted-foreground">
                    {description}
                </p>
            </div>
            <Switch id={id} checked={checked} onCheckedChange={onChange} />
        </div>
    );
}

// ============== Схемы валидации ==============
const profileSchema = Yup.object({
    name: Yup.string().required("Имя обязательно").max(255),
    email: Yup.string()
        .email("Некорректный email")
        .required("Email обязателен")
        .max(255),
    affiliation: Yup.string().max(255).nullable(),
});

const passwordSchema = Yup.object({
    current_password: Yup.string().required("Введите текущий пароль"),
    new_password: Yup.string()
        .min(8, "Минимум 8 символов")
        .required("Введите новый пароль"),
    new_password_confirmation: Yup.string()
        .oneOf([Yup.ref("new_password")], "Пароли не совпадают")
        .required("Подтвердите пароль"),
});

// ============== SettingsPanel ==============
export const SettingsPanel = observer(function SettingsPanel() {
    const { theme, setTheme } = useTheme();
    const auth = useAuth();

    // Уведомления (пока локально — можно потом сохранять через API)
    const [notifNew, setNotifNew] = useState(true);
    const [notifReminder, setNotifReminder] = useState(true);
    const [notifDigest, setNotifDigest] = useState(false);

    // Успех/ошибка
    const [profileSuccess, setProfileSuccess] = useState(false);
    const [passwordSuccess, setPasswordSuccess] = useState(false);

    // ============== Профиль ==============
    const profileFormik = useFormik({
        initialValues: {
            name: auth.user?.name ?? "",
            email: auth.user?.email ?? "",
            affiliation: auth.user?.affiliation ?? "",
        },
        validationSchema: profileSchema,
        enableReinitialize: true,
        onSubmit: async (values) => {
            setProfileSuccess(false);
            const ok = await auth.updateProfile(
                values.name,
                values.email,
                values.affiliation || null,
            );
            if (ok) {
                setProfileSuccess(true);
                setTimeout(() => setProfileSuccess(false), 3000);
            }
        },
    });

    // ============== Пароль ==============
    const passwordFormik = useFormik({
        initialValues: {
            current_password: "",
            new_password: "",
            new_password_confirmation: "",
        },
        validationSchema: passwordSchema,
        onSubmit: async (values, { resetForm }) => {
            setPasswordSuccess(false);
            const ok = await auth.changePassword(
                values.current_password,
                values.new_password,
                values.new_password_confirmation,
            );
            if (ok) {
                setPasswordSuccess(true);
                resetForm();
                setTimeout(() => setPasswordSuccess(false), 3000);
            }
        },
    });

    // ============== Тема ==============
    const themeOptions = [
        { value: "light" as const, label: "Светлая", icon: Sun },
        { value: "dark" as const, label: "Тёмная", icon: Moon },
    ];

    return (
        <div className="flex max-w-3xl flex-col gap-6">
            {/* ============ Профиль ============ */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Профиль</CardTitle>
                    <CardDescription>
                        Ваши данные, отображаемые организаторам.
                    </CardDescription>
                </CardHeader>
                <CardContent className="pt-0">
                    <form
                        onSubmit={profileFormik.handleSubmit}
                        className="flex flex-col gap-5"
                    >
                        {/* Аватар */}
                        <div className="flex items-center gap-4">
                            <Avatar
                                name={auth.user?.name ?? "User"}
                                className="size-14 text-base"
                            />
                            <div>
                                <p className="font-medium">
                                    {auth.user?.name ?? "Гость"}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {auth.user?.affiliation ??
                                        "Без организации"}
                                </p>
                            </div>
                        </div>

                        {/* Поля */}
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="name">Полное имя</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    value={profileFormik.values.name}
                                    onChange={profileFormik.handleChange}
                                    onBlur={profileFormik.handleBlur}
                                    aria-invalid={
                                        profileFormik.touched.name &&
                                        !!profileFormik.errors.name
                                    }
                                />
                                {profileFormik.touched.name &&
                                    profileFormik.errors.name && (
                                        <p className="text-sm text-destructive">
                                            {profileFormik.errors.name}
                                        </p>
                                    )}
                            </div>

                            <div className="flex flex-col gap-2">
                                <Label htmlFor="email-setting">Email</Label>
                                <Input
                                    id="email-setting"
                                    name="email"
                                    type="email"
                                    value={profileFormik.values.email}
                                    onChange={profileFormik.handleChange}
                                    onBlur={profileFormik.handleBlur}
                                    aria-invalid={
                                        profileFormik.touched.email &&
                                        !!profileFormik.errors.email
                                    }
                                />
                                {profileFormik.touched.email &&
                                    profileFormik.errors.email && (
                                        <p className="text-sm text-destructive">
                                            {profileFormik.errors.email}
                                        </p>
                                    )}
                            </div>

                            <div className="flex flex-col gap-2 sm:col-span-2">
                                <Label htmlFor="affiliation">Организация</Label>
                                <Input
                                    id="affiliation"
                                    name="affiliation"
                                    value={profileFormik.values.affiliation}
                                    onChange={profileFormik.handleChange}
                                    onBlur={profileFormik.handleBlur}
                                    placeholder="Университет, факультет..."
                                />
                            </div>
                        </div>

                        {/* Кнопка + сообщения */}
                        <div className="flex items-center gap-3">
                            <Button
                                type="submit"
                                disabled={
                                    profileFormik.isSubmitting || auth.isLoading
                                }
                            >
                                {auth.isLoading ? (
                                    <>
                                        <Loader2 className="size-4 animate-spin" />
                                        Сохранение...
                                    </>
                                ) : (
                                    <>
                                        <Save className="size-4" />
                                        Сохранить
                                    </>
                                )}
                            </Button>

                            {profileSuccess && (
                                <span className="text-sm text-emerald-600 dark:text-emerald-400">
                                    Профиль обновлён
                                </span>
                            )}

                            {auth.error && !passwordSuccess && (
                                <span className="text-sm text-destructive">
                                    {auth.error}
                                </span>
                            )}
                        </div>
                    </form>
                </CardContent>
            </Card>

            {/* ============ Пароль ============ */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Смена пароля</CardTitle>
                    <CardDescription>
                        Минимум 8 символов. После смены все остальные сессии
                        будут завершены.
                    </CardDescription>
                </CardHeader>
                <CardContent className="pt-0">
                    <form
                        onSubmit={passwordFormik.handleSubmit}
                        className="flex flex-col gap-4"
                    >
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="current_password">
                                Текущий пароль
                            </Label>
                            <Input
                                id="current_password"
                                name="current_password"
                                type="password"
                                autoComplete="current-password"
                                value={passwordFormik.values.current_password}
                                onChange={passwordFormik.handleChange}
                                onBlur={passwordFormik.handleBlur}
                            />
                            {passwordFormik.touched.current_password &&
                                passwordFormik.errors.current_password && (
                                    <p className="text-sm text-destructive">
                                        {passwordFormik.errors.current_password}
                                    </p>
                                )}
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="flex flex-col gap-2">
                                <Label htmlFor="new_password">
                                    Новый пароль
                                </Label>
                                <Input
                                    id="new_password"
                                    name="new_password"
                                    type="password"
                                    autoComplete="new-password"
                                    value={passwordFormik.values.new_password}
                                    onChange={passwordFormik.handleChange}
                                    onBlur={passwordFormik.handleBlur}
                                />
                                {passwordFormik.touched.new_password &&
                                    passwordFormik.errors.new_password && (
                                        <p className="text-sm text-destructive">
                                            {passwordFormik.errors.new_password}
                                        </p>
                                    )}
                            </div>

                            <div className="flex flex-col gap-2">
                                <Label htmlFor="new_password_confirmation">
                                    Подтвердите пароль
                                </Label>
                                <Input
                                    id="new_password_confirmation"
                                    name="new_password_confirmation"
                                    type="password"
                                    autoComplete="new-password"
                                    value={
                                        passwordFormik.values
                                            .new_password_confirmation
                                    }
                                    onChange={passwordFormik.handleChange}
                                    onBlur={passwordFormik.handleBlur}
                                />
                                {passwordFormik.touched
                                    .new_password_confirmation &&
                                    passwordFormik.errors
                                        .new_password_confirmation && (
                                        <p className="text-sm text-destructive">
                                            {
                                                passwordFormik.errors
                                                    .new_password_confirmation
                                            }
                                        </p>
                                    )}
                            </div>
                        </div>

                        <div className="flex items-center gap-3">
                            <Button
                                type="submit"
                                variant="secondary"
                                disabled={
                                    passwordFormik.isSubmitting ||
                                    auth.isLoading
                                }
                            >
                                {auth.isLoading ? (
                                    <>
                                        <Loader2 className="size-4 animate-spin" />
                                        Смена...
                                    </>
                                ) : (
                                    <>
                                        <KeyRound className="size-4" />
                                        Сменить пароль
                                    </>
                                )}
                            </Button>

                            {passwordSuccess && (
                                <span className="text-sm text-emerald-600 dark:text-emerald-400">
                                    Пароль успешно изменён
                                </span>
                            )}
                        </div>
                    </form>
                </CardContent>
            </Card>

            {/* ============ Внешний вид ============ */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Внешний вид</CardTitle>
                    <CardDescription>
                        Выберите, как приложение выглядит на этом устройстве.
                    </CardDescription>
                </CardHeader>
                <CardContent className="pt-0">
                    <div className="grid grid-cols-2 gap-3 sm:max-w-xs">
                        {themeOptions.map((opt) => {
                            const Icon = opt.icon;
                            const active = theme === opt.value;
                            return (
                                <button
                                    key={opt.value}
                                    type="button"
                                    onClick={() => setTheme(opt.value)}
                                    aria-pressed={active}
                                    className={cn(
                                        "flex flex-col items-center gap-2 rounded-xl border p-4 text-sm font-medium transition-colors",
                                        active
                                            ? "border-primary bg-primary/5 text-primary"
                                            : "border-border hover:bg-accent",
                                    )}
                                >
                                    <Icon className="size-5" />
                                    {opt.label}
                                </button>
                            );
                        })}
                    </div>
                </CardContent>
            </Card>

            {/* ============ Уведомления ============ */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Уведомления</CardTitle>
                    <CardDescription>
                        Управляйте email-уведомлениями.
                    </CardDescription>
                </CardHeader>
                <CardContent className="divide-y divide-border pt-0">
                    <SettingRow
                        id="notif-new"
                        title="Новые назначения"
                        description="Уведомление при назначении новых докладов."
                        checked={notifNew}
                        onChange={setNotifNew}
                    />
                    <SettingRow
                        id="notif-reminder"
                        title="Напоминания о дедлайнах"
                        description="Напоминания перед сроками оценки."
                        checked={notifReminder}
                        onChange={setNotifReminder}
                    />
                    <SettingRow
                        id="notif-digest"
                        title="Еженедельный отчёт"
                        description="Сводка вашей активности за неделю."
                        checked={notifDigest}
                        onChange={setNotifDigest}
                    />
                </CardContent>
            </Card>
        </div>
    );
});
