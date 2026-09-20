// resources/js/stores/AssessmentStore.ts
import { makeAutoObservable, runInAction } from "mobx";
import { assessmentsApi } from "@/api/assessments.api";
import { extractErrorMessage } from "@/api/client";
import type { PresentationDetail, SaveAssessmentRequest } from "@/types";

type SaveStatus = "idle" | "saving" | "saved" | "error";

const AUTO_SAVE_DELAY = 1500; // мс

export class AssessmentStore {
    current: PresentationDetail | null = null;
    draftValues: Record<string, number | undefined> = {};
    draftComment = "";

    loading = false;
    saveStatus: SaveStatus = "idle";
    error: string | null = null;
    savedAt: string | null = null;

    private autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

    constructor() {
        makeAutoObservable(
            this,
            {
                autoSaveTimer: false, // не наблюдаем за таймером
            },
            { autoBind: true },
        );
    }

    // ============ Computed ============
    get draftTotal(): number {
        
        return Object.values(this.draftValues).reduce(
            (sum, v) =>
                sum + (typeof v === "number" && Number.isFinite(v) ? v : 0),
            0,
        );
    }
    get maxScore(): number {
        if (!this.current) return 0;
        return this.current.event.criteria.reduce(
            (sum, c) => sum + c.max_value,
            0,
        );
    }

    get draftPercent(): number {
        if (this.maxScore === 0) return 0;
        return Math.round((this.draftTotal / this.maxScore) * 100);
    }

    get isAssessed(): boolean {
        return this.current?.my_assessment !== null;
    }

    get hasChanges(): boolean {
        if (!this.current) return false;
        const saved = this.current.my_assessment;
        if (!saved) {
            return this.draftTotal > 0 || this.draftComment.trim() !== "";
        }

        const savedValues = saved.criteria_values;
        const draftKeys = Object.keys(this.draftValues);
        const savedKeys = Object.keys(savedValues);

        if (draftKeys.length !== savedKeys.length) return true;

        for (const key of draftKeys) {
            if (this.draftValues[key] !== savedValues[key]) return true;
        }

        return (saved.comment ?? "") !== this.draftComment.trim();
    }

    get isSaving(): boolean {
        return this.saveStatus === "saving";
    }

    get isSaved(): boolean {
        return this.saveStatus === "saved";
    }

    // ============ Auto-save ============

    private scheduleAutoSave(): void {
        if (this.autoSaveTimer) {
            clearTimeout(this.autoSaveTimer);
        }

        this.autoSaveTimer = setTimeout(() => {
            void this.save(true);
        }, AUTO_SAVE_DELAY);
    }

    private cancelAutoSave(): void {
        if (this.autoSaveTimer) {
            clearTimeout(this.autoSaveTimer);
            this.autoSaveTimer = null;
        }
    }

    // ============ Actions ============

    setValue(key: string, value: number): void {
        this.draftValues = { ...this.draftValues, [key]: value };
        this.scheduleAutoSave();
    }

    setComment(comment: string): void {
        this.draftComment = comment;
        this.scheduleAutoSave();
    }

    async fetchPresentation(id: number): Promise<void> {
        this.loading = true;
        this.error = null;

        try {
            const detail = await assessmentsApi.getPresentation(id);

            runInAction(() => {
                this.current = detail;

                const initial: Record<string, number> = {};
                for (const criterion of detail.event.criteria) {
                    const savedValue =
                        detail.my_assessment?.criteria_values[criterion.key];
                    if (savedValue !== undefined) {
                        initial[criterion.key] = savedValue;
                    }
                }

                this.draftValues = initial;
                this.draftComment = detail.my_assessment?.comment ?? "";
                this.savedAt = detail.my_assessment?.saved_at ?? null;
                this.saveStatus = detail.my_assessment ? "saved" : "idle";
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
     * @param isAutoSave — true, если вызвано автосохранением
     */
    async save(isAutoSave = false): Promise<boolean> {
        if (!this.current) return false;
        if (!this.hasChanges && !isAutoSave) return false;

        this.cancelAutoSave();
        this.saveStatus = "saving";
        this.error = null;

        try {
            const payload: SaveAssessmentRequest = {
                presentation_id: this.current.id,
                criteria_values: this.draftValues,
                comment: this.draftComment.trim() || null,
            };

            const response = await assessmentsApi.save(payload);

            runInAction(() => {
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
                this.saveStatus = "saved";
            });

            return true;
        } catch (error) {
            runInAction(() => {
                this.error = extractErrorMessage(error);
                this.saveStatus = "error";
            });
            return false;
        }
    }

    reset(): void {
        this.cancelAutoSave();
        this.current = null;
        this.draftValues = {};
        this.draftComment = "";
        this.loading = false;
        this.saveStatus = "idle";
        this.error = null;
        this.savedAt = null;
    }
}
