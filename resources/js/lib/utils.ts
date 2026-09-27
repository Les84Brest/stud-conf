// resources/js/lib/utils.ts
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Получить имя для приветствия из ФИО.
 *
 * Формат ФИО: "Фамилия Имя Отчество" (или "Фамилия Имя").
 * Возвращает:
 * - "Имя Отчество" — если отчество есть
 * - "Имя" — если только фамилия и имя
 * - "Фамилия" — если одно слово
 * - "коллега" — если пусто
 */
export function getGreetingName(fullName: string | null | undefined): string {
    if (!fullName) return 'коллега';

    const parts = fullName.split(' ').filter(Boolean);

    switch (parts.length) {
        case 1:
            return parts[0] ?? 'коллега';
        case 2:
            return parts[1] ?? parts[0] ?? 'коллега';
        case 3:
            console.log('%chere', 'padding: 5px; background: DarkGreen; color: MediumSpringGreen;', parts);
            return `${parts[1]} ${parts[2]}`;
        default:
            return parts.slice(-2).join(' ');
    }
}