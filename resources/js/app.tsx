import React, { FormEvent, useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import '../css/app.css';
import type { Incident, IncidentFilters, IncidentSeverity, IncidentStatus } from './types';

const statusOrder: IncidentStatus[] = ['investigating', 'identified', 'monitoring', 'resolved'];
const statusLabels: Record<IncidentStatus, string> = {
    investigating: 'Investigating',
    identified: 'Identified',
    monitoring: 'Monitoring',
    resolved: 'Resolved',
};
const severityLabels: Record<IncidentSeverity, string> = {
    sev1: 'SEV-1',
    sev2: 'SEV-2',
    sev3: 'SEV-3',
    sev4: 'SEV-4',
};
const nextStatuses: Record<IncidentStatus, IncidentStatus[]> = {
    investigating: ['identified', 'resolved'],
    identified: ['monitoring', 'resolved'],
    monitoring: ['identified', 'resolved'],
    resolved: [],
};

function formatTime(value: string): string {
    return new Intl.DateTimeFormat('en-US', { hour: '2-digit', minute: '2-digit' }).format(new Date(value));
}

function formatAge(value: string): string {
    const minutes = Math.max(0, Math.round((Date.now() - new Date(value).getTime()) / 60000));
    if (minutes < 60) return minutes + 'm ago';
    const hours = Math.floor(minutes / 60);
    return hours + 'h ' + (minutes % 60) + 'm ago';
}

function App() {
    const [incidents, setIncidents] = useState<Incident[]>([]);
    const [selectedId, setSelectedId] = useState<number | null>(null);
    const [filters, setFilters] = useState<IncidentFilters>({ status: 'all', severity: 'all' });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [showCreate, setShowCreate] = useState(false);
    const [saving, setSaving] = useState(false);
    const [action, setAction] = useState('');
    const [form, setForm] = useState({ title: '', service: 'Checkout API', severity: 'sev2', owner: 'Maya Chen', summary: '' });

    async function loadIncidents() {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (filters.status !== 'all') params.set('status', filters.status);
            if (filters.severity !== 'all') params.set('severity', filters.severity);
            const response = await fetch('/api/v1/incidents?' + params.toString());
            if (!response.ok) throw new Error('Unable to load incidents');
            const payload = await response.json();
            const data = payload.data as Incident[];
            setIncidents(data);
            setSelectedId((current) => data.some((incident) => incident.id === current) ? current : (data[0]?.id ?? null));
            setError('');
        } catch (loadError) {
            setError(loadError instanceof Error ? loadError.message : 'Unable to load incidents');
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => { void loadIncidents(); }, [filters]);

    const selected = incidents.find((incident) => incident.id === selectedId) ?? incidents[0];
    const activeCount = incidents.filter((incident) => incident.status !== 'resolved').length;
    const breachedCount = incidents.filter((incident) => incident.sla.state === 'breached' && incident.status !== 'resolved').length;
    const healthyCount = incidents.filter((incident) => incident.status === 'resolved').length;

    const grouped = useMemo(() => statusOrder.map((status) => ({
        status,
        incidents: incidents.filter((incident) => incident.status === status),
    })).filter((group) => group.incidents.length > 0), [incidents]);

    async function createIncident(event: FormEvent) {
        event.preventDefault();
        setSaving(true);
        try {
            const response = await fetch('/api/v1/incidents', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(form),
            });
            if (!response.ok) throw new Error('Unable to create incident');
            const payload = await response.json();
            setShowCreate(false);
            setForm({ title: '', service: 'Checkout API', severity: 'sev2', owner: 'Maya Chen', summary: '' });
            await loadIncidents();
            setSelectedId(payload.data.id);
            setError('');
        } catch (saveError) {
            setError(saveError instanceof Error ? saveError.message : 'Unable to create incident');
        } finally {
            setSaving(false);
        }
    }

    async function transition(status: IncidentStatus) {
        if (!selected) return;
        setAction(status);
        try {
            const response = await fetch('/api/v1/incidents/' + selected.id + '/status', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ status }),
            });
            if (!response.ok) throw new Error('Transition could not be applied');
            await loadIncidents();
            setError('');
        } catch (transitionError) {
            setError(transitionError instanceof Error ? transitionError.message : 'Transition could not be applied');
        } finally {
            setAction('');
        }
    }

    return (
        <div className="app-shell">
            <aside className="sidebar">
                <div className="brand-lockup">
                    <div className="brand-mark" aria-hidden="true"><span /><span /><span /></div>
                    <div><div className="brand-name">IncidentFlow</div><div className="brand-caption">command center</div></div>
                </div>
                <div className="workspace-label">Workspace</div>
                <button className="nav-item active"><span className="nav-glyph">◈</span> Incident room <span className="nav-count">{activeCount}</span></button>
                <button className="nav-item" onClick={() => setFilters({ status: 'resolved', severity: 'all' })}><span className="nav-glyph">✓</span> Resolved archive</button>
                <div className="sidebar-rule" />
                <div className="workspace-label">Signal</div>
                <div className="signal-card"><span className="signal-dot" /> All systems operational</div>
                <div className="sidebar-footer"><div className="avatar">GB</div><div><strong>Gabriela Bomfim</strong><small>Incident commander</small></div></div>
            </aside>

            <main className="main-content">
                <header className="topbar">
                    <div><div className="eyebrow">OPERATIONS / INCIDENT ROOM</div><h1>Live incident board</h1><p className="lede">Turn noisy events into a calm, accountable response.</p></div>
                    <div className="header-actions"><span className="live-pill"><span className="live-dot" /> Live sync</span><button className="primary-button" onClick={() => setShowCreate(true)}><span>+</span> Declare incident</button></div>
                </header>

                <section className="metrics-grid" aria-label="Incident metrics">
                    <div className="metric-card"><span className="metric-label">Open incidents</span><strong>{activeCount.toString().padStart(2, '0')}</strong><small>Across all services</small></div>
                    <div className="metric-card risk"><span className="metric-label">SLA breached</span><strong>{breachedCount.toString().padStart(2, '0')}</strong><small>Needs an owner now</small></div>
                    <div className="metric-card healthy"><span className="metric-label">Resolved today</span><strong>{healthyCount.toString().padStart(2, '0')}</strong><small>Learning captured</small></div>
                    <div className="metric-card status-card"><span className="metric-label">Response posture</span><strong><span className="posture-dot" /> Stable</strong><small>Last checked just now</small></div>
                </section>

                <div className="board-toolbar"><div className="filter-group"><span className="toolbar-label">Show</span><select value={filters.status} onChange={(event) => setFilters({ ...filters, status: event.target.value })}><option value="all">All statuses</option>{statusOrder.map((status) => <option key={status} value={status}>{statusLabels[status]}</option>)}</select><select value={filters.severity} onChange={(event) => setFilters({ ...filters, severity: event.target.value })}><option value="all">All severities</option>{(['sev1', 'sev2', 'sev3', 'sev4'] as IncidentSeverity[]).map((severity) => <option key={severity} value={severity}>{severityLabels[severity]}</option>)}</select></div><span className="board-meta">{incidents.length} records · refreshed just now</span></div>

                {error && <div className="error-banner">{error}</div>}
                <div className="board-layout">
                    <section className="incident-board">
                        {loading ? <div className="empty-state">Loading incident board…</div> : grouped.length === 0 ? <div className="empty-state">No incidents match these filters.</div> : grouped.map((group) => <div className="status-lane" key={group.status}><div className="lane-heading"><span className={'lane-indicator ' + group.status} /><h2>{statusLabels[group.status]}</h2><span className="lane-count">{group.incidents.length}</span></div><div className="incident-list">{group.incidents.map((incident) => <button className={'incident-card ' + (selected?.id === incident.id ? 'selected' : '')} key={incident.id} onClick={() => setSelectedId(incident.id)}><div className="card-topline"><span className={'severity-tag ' + incident.severity}>{severityLabels[incident.severity]}</span><span className={'sla-tag ' + incident.sla.state}>{incident.sla.label}</span></div><strong>{incident.title}</strong><p>{incident.service}</p><div className="card-footer"><span>{incident.reference}</span><span>{formatAge(incident.created_at)}</span></div></button>)}</div></div>)}
                    </section>

                    <aside className="detail-panel">{selected ? <><div className="detail-header"><div><span className={'severity-tag ' + selected.severity}>{selected.severity_label}</span><span className="detail-ref">{selected.reference}</span></div><button className="icon-button" aria-label="Close detail" onClick={() => setSelectedId(null)}>×</button></div><h2>{selected.title}</h2><p className="detail-summary">{selected.summary}</p><div className="detail-facts"><div><span>Service</span><strong>{selected.service}</strong></div><div><span>Owner</span><strong>{selected.owner}</strong></div><div><span>Declared</span><strong>{formatTime(selected.created_at)}</strong></div></div><div className="sla-panel"><div className="sla-heading"><div><span className="section-kicker">Response clock</span><strong>{selected.sla.label}</strong></div><span className={'sla-score ' + selected.sla.state}>{selected.sla.percentage}%</span></div><div className="progress-track"><span style={{ width: selected.sla.percentage + '%' }} /></div><div className="sla-detail"><span>{selected.sla.elapsed_minutes}m elapsed</span><span>{selected.sla.target_minutes}m target</span></div></div><div className="transition-section"><div className="section-kicker">Move incident</div><div className="transition-buttons">{nextStatuses[selected.status].map((status) => <button key={status} className={status === 'resolved' ? 'resolve-button' : ''} disabled={action !== ''} onClick={() => void transition(status)}>{action === status ? 'Updating…' : statusLabels[status]}</button>)}</div>{nextStatuses[selected.status].length === 0 && <p className="muted-note">This incident is closed. Timeline remains available for learning.</p>}</div><div className="timeline-section"><div className="section-kicker">Timeline</div><div className="timeline">{selected.timeline.map((event) => <div className="timeline-item" key={event.id}><span className="timeline-node" /><div><strong>{event.message}</strong><small>{formatTime(event.created_at)} · {event.type}</small></div></div>)}</div></div></> : <div className="empty-state detail-empty">Select an incident to inspect its response timeline.</div>}</aside>
                </div>
            </main>

            {showCreate && <div className="modal-backdrop" onMouseDown={(event) => { if (event.target === event.currentTarget) setShowCreate(false); }}><form className="create-modal" onSubmit={createIncident}><div className="modal-heading"><div><span className="eyebrow">NEW EVENT</span><h2>Declare an incident</h2></div><button type="button" className="icon-button" onClick={() => setShowCreate(false)}>×</button></div><label>Title<input required value={form.title} onChange={(event) => setForm({ ...form, title: event.target.value })} placeholder="e.g. Elevated checkout latency" /></label><div className="form-row"><label>Service<input required value={form.service} onChange={(event) => setForm({ ...form, service: event.target.value })} /></label><label>Severity<select value={form.severity} onChange={(event) => setForm({ ...form, severity: event.target.value })}>{(['sev1', 'sev2', 'sev3', 'sev4'] as IncidentSeverity[]).map((severity) => <option key={severity} value={severity}>{severityLabels[severity]}</option>)}</select></label></div><div className="form-row"><label>Owner<input required value={form.owner} onChange={(event) => setForm({ ...form, owner: event.target.value })} /></label><label>Summary<input required value={form.summary} onChange={(event) => setForm({ ...form, summary: event.target.value })} /></label></div><div className="modal-actions"><button type="button" className="secondary-button" onClick={() => setShowCreate(false)}>Cancel</button><button type="submit" className="primary-button" disabled={saving}>{saving ? 'Declaring…' : 'Declare incident'}</button></div></form></div>}
        </div>
    );
}

createRoot(document.getElementById('app')!).render(<App />);
