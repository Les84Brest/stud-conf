// resources/js/api/events.api.ts
import { ApiClient } from './client';
import type { Event } from '@/types';

interface EventsResponse {
    data: Event[];
}

interface EventResponse {
    data: Event;
}

export const eventsApi = {
    /**
     * Получить мероприятия текущего пользователя.
     * - Админ: все активные мероприятия.
     * - Эксперт: только те, где он в комиссии.
     */
    myEvents: async (): Promise<Event[]> => {
        const response = await ApiClient.get<EventsResponse>('/events');

        return response.data;
    },

    /**
     * Получить детальную информацию о мероприятии.
     */
    show: async (id: number): Promise<Event> => {
        const response = await ApiClient.get<EventResponse>(`/events/${id}`);
        
        return response.data;
    },
};