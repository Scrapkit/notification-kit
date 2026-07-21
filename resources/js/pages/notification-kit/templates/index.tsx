import { useEffect, useState } from 'react';
import { notificationKit } from '../../../lib/notification-kit/api';
import type { Template, TemplateFilters } from '../../../lib/notification-kit/types';

/**
 * Lists every managed email and notification. Published stub: wire it into
 * your own Inertia route and layout.
 */
export default function TemplatesIndex() {
    const [filters, setFilters] = useState<TemplateFilters>({});
    const [templates, setTemplates] = useState<Template[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let active = true;
        setLoading(true);

        notificationKit.templates
            .list(filters)
            .then((response) => {
                if (active) {
                    setTemplates(response.data);
                    setError(null);
                }
            })
            .catch((caught: unknown) => {
                if (active) {
                    setError(caught instanceof Error ? caught.message : 'Caricamento non riuscito.');
                }
            })
            .finally(() => {
                if (active) {
                    setLoading(false);
                }
            });

        return () => {
            active = false;
        };
    }, [filters]);

    return (
        <div className="flex flex-col gap-4 p-6">
            <h1 className="text-xl font-semibold">Email e notifiche</h1>

            <div className="flex flex-wrap gap-2">
                <input
                    type="search"
                    placeholder="Cerca per chiave o nome"
                    value={filters.search ?? ''}
                    onChange={(event) => setFilters({ ...filters, search: event.target.value || undefined })}
                    className="rounded-md border border-neutral-300 px-3 py-2 text-sm"
                />
                <select
                    value={filters.type ?? ''}
                    onChange={(event) =>
                        setFilters({ ...filters, type: (event.target.value || undefined) as TemplateFilters['type'] })
                    }
                    className="rounded-md border border-neutral-300 px-3 py-2 text-sm"
                >
                    <option value="">Tutti i tipi</option>
                    <option value="email">Email</option>
                    <option value="notification">Notifiche</option>
                </select>
                <select
                    value={filters.archived ?? ''}
                    onChange={(event) =>
                        setFilters({
                            ...filters,
                            archived: (event.target.value || undefined) as TemplateFilters['archived'],
                        })
                    }
                    className="rounded-md border border-neutral-300 px-3 py-2 text-sm"
                >
                    <option value="">Solo attivi</option>
                    <option value="with">Inclusi archiviati</option>
                    <option value="only">Solo archiviati</option>
                </select>
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={filters.requires_confirmation === true}
                        onChange={(event) =>
                            setFilters({ ...filters, requires_confirmation: event.target.checked || undefined })
                        }
                    />
                    Solo con conferma
                </label>
            </div>

            {loading && <p className="text-sm text-neutral-500">Caricamento…</p>}
            {error !== null && <p className="text-sm text-red-600">{error}</p>}
            {!loading && error === null && templates.length === 0 && (
                <p className="text-sm text-neutral-500">Nessun contenuto trovato.</p>
            )}

            <ul className="divide-y divide-neutral-200">
                {templates.map((template) => (
                    <li key={template.key} className="flex items-center justify-between gap-4 py-3">
                        <div>
                            <a href={`/notification-kit/templates/${template.key}`} className="font-medium">
                                {template.name}
                            </a>
                            <p className="text-xs text-neutral-500">{template.key}</p>
                        </div>
                        <div className="flex items-center gap-2 text-xs">
                            <span className="rounded-full bg-neutral-100 px-2 py-0.5">
                                {template.type === 'email' ? 'Email' : 'Notifica'}
                            </span>
                            {template.requires_confirmation && (
                                <span className="rounded-full bg-amber-100 px-2 py-0.5 text-amber-800">Conferma</span>
                            )}
                            {template.is_customized && (
                                <span className="rounded-full bg-blue-100 px-2 py-0.5 text-blue-800">
                                    Personalizzato
                                </span>
                            )}
                            {template.archived_at !== null && (
                                <span className="rounded-full bg-neutral-200 px-2 py-0.5">Archiviato</span>
                            )}
                        </div>
                    </li>
                ))}
            </ul>
        </div>
    );
}
