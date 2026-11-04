export type IncidentStatus = 'investigating' | 'identified' | 'monitoring' | 'resolved';
export type IncidentSeverity = 'sev1' | 'sev2' | 'sev3' | 'sev4';

export type TimelineEvent = {
    id: number;
    type: string;
    message: string;
    created_at: string;
};

export type Incident = {
    id: number;
    reference: string;
    title: string;
    service: string;
    severity: IncidentSeverity;
    severity_label: string;
    status: IncidentStatus;
    status_label: string;
    summary: string;
    owner: string;
    created_at: string;
    resolved_at: string | null;
    sla: {
        state: 'within_target' | 'at_risk' | 'breached';
        elapsed_minutes: number;
        target_minutes: number;
        percentage: number;
        label: string;
    };
    timeline: TimelineEvent[];
};

export type IncidentFilters = {
    status: string;
    severity: string;
};
