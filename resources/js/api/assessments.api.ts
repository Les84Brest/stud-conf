// resources/js/api/assessments.api.ts
import { ApiClient } from './client';
import type {
    PresentationDetail,
    SaveAssessmentRequest,
    SaveAssessmentResponse,
} from '@/types';

interface PresentationDetailResponse {
    data: PresentationDetail;
}

export const assessmentsApi = {
    /**
     * Получить доклад с критериями мероприятия и моей оценкой.
     */
    getPresentation: async (id: number): Promise<PresentationDetail> => {
        const response = await ApiClient.get<PresentationDetailResponse>(
            `/presentations/${id}`,
        );
        return response.data;
    },

    /**
     * Сохранить (создать или обновить) оценку.
     */
    save: async (
        data: SaveAssessmentRequest,
    ): Promise<SaveAssessmentResponse> => {
        return ApiClient.post<SaveAssessmentResponse>('/assessments', data);
    },
};