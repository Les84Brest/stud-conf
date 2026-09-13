// resources/js/stores/EventStore.ts
import { makeAutoObservable } from 'mobx';
import type { Event } from '@/types';

export class EventStore {
    items: Event[] = [];
    loading = false;
    error: string | null = null;

    constructor() {
        makeAutoObservable(this, {}, { autoBind: true });
    }

    setItems(items: Event[]) {
        this.items = items;
    }

    setLoading(loading: boolean) {
        this.loading = loading;
    }

    setError(error: string | null) {
        this.error = error;
    }
}