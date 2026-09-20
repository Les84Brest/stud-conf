// resources/js/types/models.ts

export type UserRole = 'admin' | 'expert' | 'observer';

export interface User {
    id: number;
    name: string;
    email: string;
    role: UserRole;
    is_active: boolean;
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
}

export type EventType = 'section' | 'olympiad' | 'round_table' | 'master_class';

export interface Event {
    id: number;
    conference_id: number;
    title: string;
    slug: string;
    type: EventType;
    description: string | null;
    max_score: number;
    room: string | null;
    start_time: string;
    end_time: string;
    sort_order: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}