// resources/js/stores/RootStore.ts
import { AuthStore } from './AuthStore';
import { EventStore } from './EventStore';
import { PresentationStore } from './PresentationStore';
import { AssessmentStore } from './AssessmentStore';

export class RootStore {
    auth: AuthStore;
    events: EventStore;
    presentations: PresentationStore;
    assessment: AssessmentStore;

    constructor() {
        this.auth = new AuthStore(this);
        this.events = new EventStore();
        this.presentations = new PresentationStore();
        this.assessment = new AssessmentStore();
    }
}

export const rootStore = new RootStore();