// resources/js/types/models.ts

export type UserRole = 'admin' | 'expert' | 'observer';

export interface User {
    id: number;
    name: string;
    email: string;
    affiliation: string | null;  
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
export interface Author {
    id: number;
    full_name: string;
    email: string | null;
    university: string | null;
    faculty: string | null;
    group_number: string | null;
    phone: string | null;
    degree: string | null;
    position: string | null;
    created_at: string;
    updated_at: string;
}

export interface AssessmentSummary {
    id: number;
    total_score: number;
    criteria_values: Record<string, number>;
    comment: string | null;
    saved_at: string;
}

export interface Presentation {
    id: number;
    event_id: number;
    title: string;
    abstract: string | null;
    file_path: string | null;
    video_link: string | null;
    status: PresentationStatus;
    submitted_at: string | null;
    approved_at: string | null;
    rejection_reason: string | null;

    // Связи и счётчики
    authors?: Author[];
    assessments_count?: number;
    assessments_avg?: number | null;
    my_assessment?: AssessmentSummary | null;

    // Связи (опционально)
    event?: Event;

    created_at: string;
    updated_at: string;
}


// Добавляем тип критерия мероприятия
export interface EventCriteria {
    id: number;
    key: string;
    name: string;
    max_value: number;
    description: string | null;
    sort_order: number;
}

// Обновляем PresentationDetail (расширенная версия Presentation для формы оценки)
export interface PresentationDetail {
    id: number;
    title: string;
    abstract: string | null;
    status: PresentationStatus;
    file_path: string | null;
    video_link: string | null;
    submitted_at: string | null;
    event: {
        id: number;
        title: string;
        max_score: number;
        criteria: EventCriteria[];
    };
    authors: Author[];
    my_assessment: AssessmentSummary | null;
}

// Запрос на сохранение оценки
export interface SaveAssessmentRequest {
    presentation_id: number;
    criteria_values: Record<string, number>;
    comment?: string | null;
}

// Ответ после сохранения
export interface SaveAssessmentResponse {
    message: string;
    data: {
        id: number;
        total_score: number;
        criteria_values: Record<string, number>;
        comment: string | null;
        saved_at: string;
    };
}
