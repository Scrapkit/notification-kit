import { useEffect, useState } from 'react';
import { PlaceholderPalette } from '../../../components/notification-kit/PlaceholderPalette';
import { TemplatePreview } from '../../../components/notification-kit/TemplatePreview';
import { notificationKit } from '../../../lib/notification-kit/api';
import type { Preview, Template, TemplateVersion } from '../../../lib/notification-kit/types';

interface Props {
    templateKey: string;
}

/**
 * Editor for a single template: markdown body, placeholder helper, live
 * preview, reset to the default shipped in code, and the change history.
 */
export default function TemplateEdit({ templateKey }: Props) {
    const [template, setTemplate] = useState<Template | null>(null);
    const [subject, setSubject] = useState('');
    const [body, setBody] = useState('');
    const [requiresConfirmation, setRequiresConfirmation] = useState(false);
    const [preview, setPreview] = useState<Preview | null>(null);
    const [versions, setVersions] = useState<TemplateVersion[]>([]);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        notificationKit.templates
            .show(templateKey)
            .then((response) => {
                setTemplate(response.data);
                setSubject(response.data.subject ?? response.data.default_subject ?? '');
                setBody(response.data.body ?? response.data.default_body);
                setRequiresConfirmation(response.data.requires_confirmation);
            })
            .catch((caught: unknown) =>
                setError(caught instanceof Error ? caught.message : 'Caricamento non riuscito.'),
            );

        void loadVersions();
    }, [templateKey]);

    // Debounced live preview of the draft currently in the editor.
    useEffect(() => {
        const timer = window.setTimeout(() => {
            notificationKit.templates
                .preview(templateKey, { subject, body })
                .then((response) => setPreview(response.data))
                .catch(() => setPreview(null));
        }, 400);

        return () => window.clearTimeout(timer);
    }, [templateKey, subject, body]);

    async function loadVersions() {
        const response = await notificationKit.templates.versions(templateKey);
        setVersions(response.data);
    }

    async function save() {
        setSaving(true);
        setError(null);

        try {
            const response = await notificationKit.templates.updateContent(templateKey, {
                subject: subject === '' ? null : subject,
                body: body === '' ? null : body,
                requires_confirmation: requiresConfirmation,
            });

            setTemplate(response.data);
            await loadVersions();
        } catch (caught) {
            setError(caught instanceof Error ? caught.message : 'Salvataggio non riuscito.');
        } finally {
            setSaving(false);
        }
    }

    function resetToDefault() {
        if (template === null) {
            return;
        }

        setSubject(template.default_subject ?? '');
        setBody(template.default_body);
    }

    if (template === null) {
        return <p className="p-6 text-sm text-neutral-500">{error ?? 'Caricamento…'}</p>;
    }

    return (
        <div className="grid gap-6 p-6 lg:grid-cols-[2fr_1fr]">
            <div className="flex flex-col gap-4">
                <div>
                    <h1 className="text-xl font-semibold">{template.name}</h1>
                    <p className="text-xs text-neutral-500">{template.key}</p>
                </div>

                {template.type === 'email' && (
                    <label className="flex flex-col gap-1 text-sm">
                        Oggetto
                        <input
                            value={subject}
                            onChange={(event) => setSubject(event.target.value)}
                            className="rounded-md border border-neutral-300 px-3 py-2"
                        />
                    </label>
                )}

                <label className="flex flex-col gap-1 text-sm">
                    Contenuto (Markdown)
                    <textarea
                        id="notification-kit-body"
                        value={body}
                        onChange={(event) => setBody(event.target.value)}
                        rows={16}
                        className="rounded-md border border-neutral-300 px-3 py-2 font-mono text-sm"
                    />
                </label>

                {template.type === 'email' && (
                    <label className="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            checked={requiresConfirmation}
                            onChange={(event) => setRequiresConfirmation(event.target.checked)}
                        />
                        Richiedi conferma manuale prima dell&apos;invio
                    </label>
                )}

                {preview !== null && preview.missing_placeholders.length > 0 && (
                    <p className="text-sm text-amber-700">
                        Segnaposto senza valore: {preview.missing_placeholders.join(', ')}
                    </p>
                )}

                {error !== null && <p className="text-sm text-red-600">{error}</p>}

                <div className="flex gap-2">
                    <button
                        type="button"
                        disabled={saving}
                        onClick={() => void save()}
                        className="rounded-md bg-neutral-900 px-4 py-2 text-sm text-white disabled:opacity-50"
                    >
                        {saving ? 'Salvataggio…' : 'Salva'}
                    </button>
                    <button
                        type="button"
                        onClick={resetToDefault}
                        className="rounded-md border border-neutral-300 px-4 py-2 text-sm"
                    >
                        Ripristina il testo di default
                    </button>
                </div>

                <section>
                    <h2 className="mb-2 text-sm font-semibold">Anteprima</h2>
                    {preview === null ? (
                        <p className="text-sm text-neutral-500">Nessuna anteprima disponibile.</p>
                    ) : (
                        <TemplatePreview html={preview.body_html} />
                    )}
                </section>
            </div>

            <aside className="flex flex-col gap-6">
                <section>
                    <h2 className="mb-2 text-sm font-semibold">Segnaposto</h2>
                    <PlaceholderPalette
                        placeholders={template.placeholders}
                        onInsert={(token) => setBody((current) => `${current}${token}`)}
                    />
                </section>

                <section>
                    <h2 className="mb-2 text-sm font-semibold">Storico modifiche</h2>
                    {versions.length === 0 ? (
                        <p className="text-sm text-neutral-500">Nessuna modifica registrata.</p>
                    ) : (
                        <ul className="flex flex-col gap-2 text-sm">
                            {versions.map((version) => (
                                <li key={version.id} className="rounded-md border border-neutral-200 px-3 py-2">
                                    <span className="block font-medium">{version.subject ?? '(default)'}</span>
                                    <span className="text-xs text-neutral-500">
                                        {version.edited_by ?? 'Sistema'} — {version.created_at}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </aside>
        </div>
    );
}
