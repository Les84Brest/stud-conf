// resources/js/stores/RootStore.ts
import { AuthStore } from './AuthStore';
import { EventStore } from './EventStore';

export class RootStore {
    auth: AuthStore;
    events: EventStore;

    constructor() {
        this.auth = new AuthStore();
        this.events = new EventStore();
    }
}

export const rootStore = new RootStore();