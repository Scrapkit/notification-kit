interface Props {
    html: string;
    className?: string;
}

/**
 * Renders email HTML in a sandboxed iframe. The markup is produced by the
 * package renderer and is already escaped, but the sandbox keeps a preview
 * from ever running anything in the admin page.
 */
export function TemplatePreview({ html, className }: Props) {
    return (
        <iframe
            title="Anteprima"
            sandbox=""
            srcDoc={html}
            className={className ?? 'h-96 w-full rounded-md border border-neutral-200 bg-white'}
        />
    );
}
