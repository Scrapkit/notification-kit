import { useEffect, useState } from 'react';
import { ConfirmSendModal } from '../../../components/notification-kit/ConfirmSendModal';
import { OutboxStatusBadge } from '../../../components/notification-kit/OutboxStatusBadge';
import { notificationKit } from '../../../lib/notification-kit/api';
import type { OutboxFilters, OutboxMessage } from '../../../lib/notification-kit/types';

/**
 * The approval queue: emails held for manual confirmation, plus the history
 * of what was sent, cancelled or failed.
 */
export default function OutboxIndex() {
    const [filters, setFilters] = useState<OutboxFilters>({ status: 'pending' });
    const [messages, setMessages] = useState<OutboxMessage[]>([]);
    const [reviewing, setReviewing] = useState<OutboxMessage | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        void load();
    }, [filters]);

    async function load() {
        setLoading(true);

        try {
            const response = await notificationKit.outbox.list(filters);
            setMessages(response.data);
            setError(null);
        } catch (caught) {
            setError(caught instanceof Error ? caught.message : 'Caricamento non riuscito.');
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="flex flex-col gap-4 p-6">
            <h1 className="text-xl font-semibold">Coda di approvazione</h1>

            <select
                value={filters.status ?? ''}
                onChange={(event) =>
                    setFilters({ ...filters, status: (event.target.value || undefined) as OutboxFilters['status'] })
                }
                className="w-56 rounded-md border border-neutral-300 px-3 py-2 text-sm"
            >
                <option value="">Tutti gli stati</option>
                <option value="pending">Da confermare</option>
                <option value="approved">Approvate</option>
                <option value="sent">Inviate</option>
                <option value="cancelled">Annullate</option>
                <option value="failed">Fallite</option>
            </select>

            {loading && <p className="text-sm text-neutral-500">Caricamento…</p>}
            {error !== null && <p className="text-sm text-red-600">{error}</p>}
            {!loading && error === null && messages.length === 0 && (
                <p className="text-sm text-neutral-500">Nessun messaggio in questa vista.</p>
            )}

            <ul className="divide-y divide-neutral-200">
                {messages.map((message) => (
                    <li key={message.uuid} className="flex items-center justify-between gap-4 py-3">
                        <div>
                            <p className="font-medium">{message.rendered_subject}</p>
                            <p className="text-xs text-neutral-500">
                                {message.recipients.map((recipient) => recipient.address).join(', ')} —{' '}
                                {message.template_name ?? message.template_key}
                            </p>
                            {message.error !== null && <p className="text-xs text-red-600">{message.error}</p>}
                        </div>
                        <div className="flex items-center gap-3">
                            <OutboxStatusBadge status={message.status} />
                            {message.status === 'pending' && (
                                <button
                                    type="button"
                                    onClick={() => setReviewing(message)}
                                    className="rounded-md border border-neutral-300 px-3 py-1.5 text-sm"
                                >
                                    Rivedi
                                </button>
                            )}
                        </div>
                    </li>
                ))}
            </ul>

            {reviewing !== null && (
                <ConfirmSendModal
                    message={reviewing}
                    onDecided={() => {
                        setReviewing(null);
                        void load();
                    }}
                />
            )}
        </div>
    );
}
