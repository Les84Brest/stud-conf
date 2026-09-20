// resources/js/stores/AssessmentStore.ts
import { makeAutoObservable, runInAction } from 'mobx';
import { assessmentsApi } from '@/api/assessments.api';
import { extractErrorMessage } from '@/api/client';
import type {
    PresentationDetail,
    SaveAssessmentRequest,
} from '@/types';

export class AssessmentStore {
    /** Текущий доклад (детально) */
    current: PresentationDetail | null = null;

    /** Значения критериев (в процессе редактирования) */
    draftValues: Record<string, number> = {};

    /** Комментарий (в процессе редактирования) */
    draftComment = '';

    loading = false;
    saving = false;
    error: string | null = null;
    savedAt: string | null = null;

    constructor() {
        makeAutoObservable(this, {}, { autoBind: true });
    }

    // ============ Computed ============

    /** Сумма текущего драфта */
    get draftTotal(): number {
        return Object.values(this.draftValues).reduce(
            (sum, v) => sum + (Number.isFinite(v) ? v : 0),
            0,
        );
    }

    /** Максимально возможный балл */
    get maxScore(): number {
        if (!this.current) return 0;
        return this.current.event.criteria.reduce(
            (sum, c) => sum + c.max_value,
            0,
        );
    }

    /** Процент от максимума */
    get draftPercent(): number {
        if (this.maxScore === 0) return 0;
        return Math.round((this.draftTotal / this.maxScore) * 100);
    }

    /** Оценён ли уже этот доклад */
    get isAssessed(): boolean {
        return this.current?.my_assessment !== null;
    }

    /** Есть ли изменения относительно сохранённой оценки */
    get hasChanges(): boolean {
        if (!this.current) return false;
        const saved = this.current.my_assessment;
        if (!saved) return this.draftTotal > 0 || this.draftComment.trim() !== '';

        const savedValues = saved.criteria_values;
        const draftKeys = Object.keys(this.draftValues);
        const savedKeys = Object.keys(savedValues);

        if (draftKeys.length !== savedKeys.length) return true;

        for (const key of draftKeys) {
            if (this.draftValues[key] !== savedValues[key]) return true;
        }

        return (saved.comment ?? '') !== this.draftComment.trim();
    }

    // ============ Actions ============

    setValue(key: string, value: number): void {
        this.draftValues = { ...this.draftValues, [key]: value };
    }

    setComment(comment: string): void {
        this.draftComment = comment;
    }

    /**
     * Загрузить доклад с критериями и моей оценкой.
     */
    async fetchPresentation(id: number): Promise<void> {
        this.loading = true;
        this.error = null;

        try {
            const detail = await assessmentsApi.getPresentation(id);

            runInAction(() => {
                this.current = detail;

                // Инициализация драфта: берём сохранённые значения или нули
                const initial: Record<string, number> = {};
                for (const criterion of detail.event.criteria) {
                    initial[criterion.key] =
                        detail.my_assessment?.criteria_values[criterion.key] ?? 0;
                }

                this.draftValues = initial;
                this.draftComment = detail.my_assessment?.comment ?? '';
                this.savedAt = detail.my_assessment?.saved_at ?? null;
                this.loading = false;
            });
        } catch (error) {
            runInAction(() => {
                this.error = extractErrorMessage(error);
                this.loading = false;
            });
        }
    }

    /**
     * Сохранить оценку.
     */
    async save(): Promise<boolean> {
        if (!this.current) return false;

        this.saving = true;
        this.error = null;

        try {
            const payload: SaveAssessmentRequest = {
                presentation_id: this.current.id,
                criteria_values: this.draftValues,
                comment: this.draftComment.trim() || null,
            };

            const response = await assessmentsApi.save(payload);

            runInAction(() => {
                // Обновляем мою оценку в current
                if (this.current) {
                    this.current = {
                        ...this.current,
                        my_assessment: {
                            id: response.data.id,
                            total_score: response.data.total_score,
                            criteria_values: response.data.criteria_values,
                            comment: response.data.comment,
                            saved_at: response.data.saved_at,
                        },
                    };
                }
                this.savedAt = response.data.saved_at;
                this.saving = false;
            });

            return true;
        } catch (error) {
            runInAction(() => {
                this.error = extractErrorMessage(error);
                this.saving = false;
            });
            return false;
        }
    }

    /**
     * Сбросить состояние.
     */
    reset(): void {
        this.current = null;
        this.draftValues = {};
        this.draftComment = '';
        this.loading = false;
        this.saving = false;
        this.error = null;
        this.savedAt = null;
    }
}