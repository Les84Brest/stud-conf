// resources/js/stores/EventStore.ts
import { makeAutoObservable, runInAction } from 'mobx';
import { eventsApi } from '@/api/events.api';
import { extractErrorMessage } from '@/api/client';
import type { Event } from '@/types';

export class EventStore {
    items: Event[] = [];
    loading = false;
    error: string | null = null;

    constructor() {
        makeAutoObservable(this, {}, { autoBind: true });
    }

    // ============ Computed ============

    get totalCount(): number {
        return this.items.length;
    }

    get totalPresentations(): number {
        return this.items.reduce(
            (sum, e) => sum + (e.presentations_count ?? 0),
            0,
        );
    }

    get completedAssessments(): number {
        return this.items.reduce(
            (sum, e) => sum + (e.assessments_count ?? 0),
            0,
        );
    }

    get pendingAssessments(): number {
        return Math.max(
            0,
            this.totalPresentations - this.completedAssessments,
        );
    }

    // ============ Actions ============

    setItems(items: Event[]) {
        this.items = items;
    }

    setLoading(loading: boolean) {
        this.loading = loading;
    }

    setError(error: string | null) {
        this.error = error;
    }

    /**
     * Загрузить мероприятия текущего пользователя.
     */
    async fetchMyEvents(): Promise<void> {
        this.loading = true;
        this.error = null;

        try {
            const events = await eventsApi.myEvents();

            runInAction(() => {
                this.items = events;
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
     * Сбросить состояние (при logout).
     */
    reset() {
        this.items = [];
        this.loading = false;
        this.error = null;
    }
}