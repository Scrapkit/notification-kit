import { useState } from 'react';
import { notificationKit } from '../../lib/notification-kit/api';
import type { OutboxMessage } from '../../lib/notification-kit/types';
import { TemplatePreview } from './TemplatePreview';

interface Props {
    message: OutboxMessage;
    onDecided: (outcome: 'approved' | 'cancelled') => void;
}

/**
 * Shown when a send needs manual confirmation. Mount it with the outbox
 * message your controller returned for a SendDispatch that needs confirmation.
 */
export function ConfirmSendModal({ message, onDecided }: Props) {
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState<string | null>(null);

    async function decide(outcome: 'approved' | 'cancelled') {
        setBusy(true);
        setError(null);

        try {
            if (outcome === 'approved') {
                await notificationKit.outbox.approve(message.uuid);
            } else {
                await notificationKit.outbox.cancel(message.uuid);
            }

            onDecided(outcome);
        } catch (caught) {
            setError(caught instanceof Error ? caught.message : 'Operazione non riuscita.');
            setBusy(false);
        }
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div className="flex max-h-full w-full max-w-2xl flex-col gap-4 overflow-y-auto rounded-lg bg-white p-6">
                <div>
                    <h2 className="text-lg font-semibold">Confermi l&apos;invio?</h2>
                    <p className="text-sm text-neutral-500">
                        Questa email richiede una conferma manuale prima di partire.
                    </p>
                </div>

                <dl className="grid gap-2 text-sm">
                    <div>
                        <dt className="font-medium text-neutral-500">Destinatari</dt>
                        <dd>
                            {message.recipients.map((recipient) => (
                                <span key={`${recipient.type}-${recipient.address}`} className="block">
                                    <span className="uppercase text-neutral-400">{recipient.type}</span>{' '}
                                    {recipient.name ?? recipient.address}
                                    {recipient.name !== null && ` <${recipient.address}>`}
                                </span>
                            ))}
                        </dd>
                    </div>
                    <div>
                        <dt className="font-medium text-neutral-500">Oggetto</dt>
                        <dd>{message.rendered_subject}</dd>
                    </div>
                </dl>

                <TemplatePreview html={message.rendered_body_html} className="h-64 w-full rounded-md border" />

                {error !== null && <p className="text-sm text-red-600">{error}</p>}

                <div className="flex justify-end gap-2">
                    <button
                        type="button"
                        disabled={busy}
                        onClick={() => void decide('cancelled')}
                        className="rounded-md border border-neutral-300 px-4 py-2 text-sm disabled:opacity-50"
                    >
                        Annulla invio
                    </button>
                    <button
                        type="button"
                        disabled={busy}
                        onClick={() => void decide('approved')}
                        className="rounded-md bg-neutral-900 px-4 py-2 text-sm text-white disabled:opacity-50"
                    >
                        {busy ? 'Invio…' : 'Conferma e invia'}
                    </button>
                </div>
            </div>
        </div>
    );
}
