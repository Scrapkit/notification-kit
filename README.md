# Notification Kit

Gestisci le email e le notifiche di un'applicazione Laravel senza toccare il
codice sorgente: contenuti editabili da chi è autorizzato, storico completo
delle modifiche, archiviazione reversibile e una coda di approvazione per le
email che richiedono una conferma manuale prima di partire.

- **Visualizzazione** — un elenco di tutte le email e notifiche gestite, con
  filtri per tipo, stato di archiviazione e conferma richiesta, più la ricerca.
- **Modifica** — oggetto, corpo (Markdown con segnaposto) e metadati si
  cambiano dalla UI; nessun deploy.
- **Archiviazione** — un contenuto archiviato sparisce dall'elenco principale
  ma resta nel database con tutto il suo storico. Non esiste una cancellazione.
- **Conferma invio** — le email marcate come "richiede conferma" non partono:
  diventano un messaggio in attesa nella coda di approvazione, con anteprima e
  riepilogo destinatari.

Il core è headless (API JSON versionata); le pagine React/Inertia sono stub che
pubblichi e poi possiedi.

## Requisiti

PHP 8.3+, Laravel 11, 12 o 13.

## Installazione

```bash
composer require scrapkit/notification-kit

php artisan vendor:publish --tag=notification-kit-config
php artisan vendor:publish --tag=notification-kit-migrations
php artisan migrate
```

Per le pagine React:

```bash
php artisan vendor:publish --tag=notification-kit-stubs
```

## Rendere gestibile una email

Estendi `ManagedMailable` e dichiara la definizione del template:

```php
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\PlaceholderDefinition;
use Scrapkit\NotificationKit\Domain\Templates\DataTransferObjects\TemplateDefinition;
use Scrapkit\NotificationKit\Domain\Templates\Enums\TemplateType;
use Scrapkit\NotificationKit\Mail\ManagedMailable;

final class InvoicePaidMail extends ManagedMailable
{
    public function __construct(private readonly Invoice $invoice) {}

    public static function template(): TemplateDefinition
    {
        return new TemplateDefinition(
            key: 'invoices.paid',
            type: TemplateType::Email,
            name: 'Fattura pagata',
            description: 'Inviata quando una fattura risulta pagata.',
            defaultSubject: 'La fattura {{ invoice.number }} è pagata',
            defaultBody: "Ciao {{ user.name }},\n\nLa fattura **{{ invoice.number }}** è stata pagata.",
            placeholders: [
                new PlaceholderDefinition('user.name', 'Nome del destinatario', 'Ada Lovelace'),
                new PlaceholderDefinition('invoice.number', 'Numero fattura', 'INV-2041'),
            ],
            sampleData: [
                'user' => ['name' => 'Ada Lovelace'],
                'invoice' => ['number' => 'INV-2041'],
            ],
            requiresConfirmation: true,
        );
    }

    public function templateData(): array
    {
        return [
            'user' => ['name' => $this->invoice->customer->name],
            'invoice' => ['number' => $this->invoice->number],
        ];
    }
}
```

Registra la classe e sincronizza:

```php
// config/notification-kit.php
'manageables' => [
    App\Mail\InvoicePaidMail::class,
],
```

```bash
php artisan notification-kit:sync
```

Il sync crea le righe mancanti e aggiorna solo le colonne di proprietà del
codice (nome, descrizione, testi di default, segnaposto, dati di esempio).
**Non tocca mai** oggetto, corpo, metadati, flag di conferma e archiviazione:
quelli appartengono a chi li modifica dalla UI. Le chiavi rimaste in database
ma non più dichiarate vengono segnalate, mai cancellate.

## Inviare

```php
use Scrapkit\NotificationKit\Facades\NotificationKit;

$dispatch = NotificationKit::to($invoice->customer)->send(new InvoicePaidMail($invoice));

if ($dispatch->needsConfirmation()) {
    return back()->with('pendingMessage', OutboxMessageResource::make($dispatch->message));
}
```

- **Senza conferma** → l'email parte subito con il contenuto corrente.
- **Con conferma** → viene salvato uno *snapshot* renderizzato in stato
  `pending`, e non parte niente. Se c'è un utente davanti, monta
  `ConfirmSendModal` con il messaggio restituito: conferma o annulla subito.
  Se l'invio arriva da un job o dallo scheduler, il messaggio resta nella coda
  di approvazione della UI. All'approvazione un job in coda invia **lo
  snapshot**, non un nuovo rendering: chi approva invia esattamente ciò che ha
  visto, anche se nel frattempo il template è cambiato.

Inviare una mailable confirmable direttamente con `Mail::send()` lancia
`ConfirmationRequiredException`: il gate di conferma non si aggira per errore.

### Notifiche

Le notifiche sono gestite nei contenuti ma non passano dalla conferma:

```php
final class InvoicePaidNotification extends Notification implements Manageable
{
    use HasManagedContent;

    public function toDatabase(object $notifiable): array
    {
        $content = $this->renderManaged(['invoice' => ['number' => $this->number]]);

        return ['title' => $content->subject, 'body' => $content->bodyHtml];
    }
}
```

## Autorizzazioni

Il package non definisce nessun gate: finché non lo fai tu, l'API risponde 403
a chiunque.

```php
Gate::define('viewNotificationKit', fn (User $user): bool => $user->isAdmin());
```

Se ti serve separare chi scrive da chi approva, definisci le abilità
specifiche; quelle che non definisci ricadono sul gate d'ingresso.

```php
Gate::define('notification-kit.approve', fn (User $user): bool => $user->isManager());
// notification-kit.view | notification-kit.update-content | notification-kit.archive
```

## API

Prefisso di default `notification-kit/api/v1` (configurabile).

| Metodo | Path | Scopo |
| --- | --- | --- |
| GET | `/templates` | Elenco; filtri `type`, `archived` (`only`/`with`), `requires_confirmation`, `search` |
| GET | `/templates/{key}` | Dettaglio con default, segnaposto e dati di esempio |
| PUT | `/templates/{key}/content` | Modifica oggetto/corpo/metadati/conferma (`null` = torna al default) |
| POST | `/templates/{key}/archive` · `/unarchive` | Archiviazione reversibile |
| GET | `/templates/{key}/versions` | Storico immutabile |
| POST | `/templates/{key}/preview` | Rendering di contenuto salvato o bozza |
| GET | `/outbox` · `/outbox/{uuid}` | Coda di approvazione e dettaglio snapshot |
| POST | `/outbox/{uuid}/approve` · `/cancel` | Decisione (409 se il messaggio non è più in attesa) |

## Eventi

`TemplateContentUpdated`, `TemplateArchived`, `TemplateUnarchived`,
`MessagePendingConfirmation`, `MessageApproved`, `MessageCancelled`,
`MessageSent`, `MessageFailed`.

`MessagePendingConfirmation` è il punto giusto per avvisare gli approvatori di
un invio partito da un job.

## Sicurezza dei contenuti

I testi in database sono Markdown, mai Blade: nessuno può eseguire PHP
scrivendo in un template. Il rendering usa CommonMark con `html_input: escape`
e link non sicuri disabilitati, e i valori dei segnaposto sono HTML-escaped.
L'anteprima gira dentro un iframe in sandbox.

## Limiti noti (v1)

- Le mailable con conferma non supportano allegati dinamici (modello a
  snapshot).
- Le notifiche non sono soggette a conferma.
- Un contenuto archiviato ma ancora richiamato dal codice viene comunque
  inviato, con un warning nei log: archiviare non deve rompere le email
  transazionali in silenzio.
- Nessun supporto multi-lingua: una chiave, un contenuto.
- I messaggi in outbox restano per sempre a fini di audit; se il tuo contesto
  lo richiede, pianifica una tua policy di retention.

## Sviluppo

```bash
composer test      # Pest, in parallelo
composer analyse   # PHPStan livello 7
composer format    # Pint
```

Le decisioni architetturali sono in [docs/rfc/0001-notification-kit.md](docs/rfc/0001-notification-kit.md).

## Licenza

MIT.
