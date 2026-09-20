// resources/js/api/presentations.api.ts
import { ApiClient } from './client';
import type { Presentation } from '@/types';

interface PresentationsResponse {
    data: Presentation[];
}

interface PresentationResponse {
    data: Presentation;
}

export const presentationsApi = {
    /**
     * Получить доклады мероприятия.
     */
    byEvent: async (eventId: number): Promise<Presentation[]> => {
        const response = await ApiClient.get<PresentationsResponse>(
            `/events/${eventId}/presentations`,
        );
        return response.data;
    },

    /**
     * Получить детальную информацию о докладе.
     */
    show: async (id: number): Promise<Presentation> => {
        const response = await ApiClient.get<PresentationResponse>(
            `/presentations/${id}`,
        );
        return response.data;
    },
};