import type { Placeholder } from '../../lib/notification-kit/types';

interface Props {
    placeholders: Placeholder[];
    onInsert: (token: string) => void;
}

export function PlaceholderPalette({ placeholders, onInsert }: Props) {
    if (placeholders.length === 0) {
        return <p className="text-sm text-neutral-500">Questo contenuto non dichiara segnaposto.</p>;
    }

    return (
        <ul className="flex flex-col gap-2">
            {placeholders.map((placeholder) => (
                <li key={placeholder.key}>
                    <button
                        type="button"
                        onClick={() => onInsert(`{{ ${placeholder.key} }}`)}
                        className="w-full rounded-md border border-neutral-200 px-3 py-2 text-left hover:bg-neutral-50"
                    >
                        <code className="text-sm font-medium">{`{{ ${placeholder.key} }}`}</code>
                        <span className="block text-xs text-neutral-500">{placeholder.description}</span>
                        {placeholder.example !== null && (
                            <span className="block text-xs text-neutral-400">es. {placeholder.example}</span>
                        )}
                    </button>
                </li>
            ))}
        </ul>
    );
}
