// resources/js/stores/PresentationStore.ts
import { makeAutoObservable, runInAction } from 'mobx';
import { presentationsApi } from '@/api/presentations.api';
import { extractErrorMessage } from '@/api/client';
import type { Presentation } from '@/types';

export class PresentationStore {
    /** Доклады текущего мероприятия */
    items: Presentation[] = [];

    /** ID мероприятия, для которого загружены доклады */
    currentEventId: number | null = null;

    loading = false;
    error: string | null = null;

    constructor() {
        makeAutoObservable(this, {}, { autoBind: true });
    }

    // ============ Computed ============

    /** Общее количество докладов */
    get totalCount(): number {
        return this.items.length;
    }

    /** Сколько я уже оценил */
    get assessedByMe(): number {
        return this.items.filter((p) => p.my_assessment !== null).length;
    }

    /** Сколько осталось оценить */
    get pendingForMe(): number {
        return this.items.filter((p) => p.my_assessment === null).length;
    }

    /** Прогресс моей оценки (0-100) */
    get myProgress(): number {
        if (this.totalCount === 0) return 0;
        return Math.round((this.assessedByMe / this.totalCount) * 100);
    }

    // ============ Actions ============

    setItems(items: Presentation[]) {
        this.items = items;
    }

    setLoading(loading: boolean) {
        this.loading = loading;
    }

    setError(error: string | null) {
        this.error = error;
    }

    /**
     * Загрузить доклады для мероприятия.
     * Если уже загружены для этого же мероприятия — пропускаем.
     */
    async fetchByEvent(eventId: number, force = false): Promise<void> {
        if (!force && this.currentEventId === eventId && this.items.length > 0) {
            return;
        }

        this.loading = true;
        this.error = null;

        try {
            const presentations = await presentationsApi.byEvent(eventId);

            runInAction(() => {
                this.items = presentations;
                this.currentEventId = eventId;
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
     * Обновить один доклад (после сохранения оценки).
     */
    updateOne(updated: Presentation): void {
        const index = this.items.findIndex((p) => p.id === updated.id);
        if (index !== -1) {
            this.items[index] = updated;
        }
    }

    /**
     * Сбросить состояние.
     */
    reset() {
        this.items = [];
        this.currentEventId = null;
        this.loading = false;
        this.error = null;
    }
}