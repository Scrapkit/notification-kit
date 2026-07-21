import type { OutboxStatus } from '../../lib/notification-kit/types';

const LABELS: Record<OutboxStatus, string> = {
    pending: 'Da confermare',
    approved: 'Approvata',
    cancelled: 'Annullata',
    sent: 'Inviata',
    failed: 'Fallita',
};

const STYLES: Record<OutboxStatus, string> = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-neutral-100 text-neutral-700',
    sent: 'bg-emerald-100 text-emerald-800',
    failed: 'bg-red-100 text-red-800',
};

export function OutboxStatusBadge({ status }: { status: OutboxStatus }) {
    return (
        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${STYLES[status]}`}>
            {LABELS[status]}
        </span>
    );
}
