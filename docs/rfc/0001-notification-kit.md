---
number: 0001
title: Content-managed emails and notifications with a confirmation outbox
author: Vincenzo Scozzari
status: Draft
created: 2026-07-21
decided:
---

# RFC 0001 — Content-managed emails and notifications with a confirmation outbox

<!--
status: Draft | Review | Accepted | Rejected | Superseded by NNNN
decided: the date the status became Accepted or Rejected; empty until then.
The process is described in engineering-kit docs/rfc-guidelines.md.
-->

## Problem

Ogni modifica a oggetto o corpo di un'email o di una notifica applicativa è oggi
una modifica al codice sorgente: branch, PR, deploy. Gli utenti autorizzati non
hanno alcuna visibilità sulle comunicazioni configurate nel sistema, nessuno
storico delle modifiche, nessun modo di dismettere una comunicazione senza
cancellarla, e nessun meccanismo per richiedere una conferma umana prima
dell'invio di email delicate (l'invio è sempre automatico). Il bisogno è
trasversale ai progetti Scrapkit, quindi replicarlo in-app significherebbe
duplicare dominio, schema e UI.

## Proposal

Un package standalone `scrapkit/notification-kit` (questo repository):

- **Contratto esplicito**: le Mailable/Notification dell'host implementano
  `Manageable::template(): TemplateDefinition` (chiave, tipo email|notification,
  default di oggetto/corpo in Markdown, placeholder documentati, sample data,
  flag conferma). Registrazione esplicita in config, upsert via
  `notification-kit:sync`.
- **Contenuti in DB con override nullable**: `subject`/`body` NULL = default dal
  codice; il sync non tocca mai le colonne dell'utente; ogni modifica scrive una
  riga immutabile di storico; archiviazione = `archived_at` (mai hard-delete).
- **Rendering sicuro**: Markdown (CommonMark `html_input: escape`,
  `allow_unsafe_links: false`) + placeholder `{{ user.name }}` HTML-escaped.
  Mai Blade compilato dal DB (RCE).
- **Conferma invio = outbox di approvazione**: l'host invia via
  `NotificationKit::to(...)->send($mailable)`; se il template richiede conferma
  viene salvato uno snapshot renderizzato in stato `pending` (modale immediata
  se c'è un utente, coda di approvazione altrimenti); l'approvazione invia lo
  snapshot via job in coda. State machine `pending→approved→sent|failed`,
  `pending→cancelled`.
- **API JSON versionata** (`notification-kit/api/v1`) + stub React/Inertia/TS
  pubblicabili che l'host possiede dopo il publish. Autorizzazione stile
  Horizon: gate `viewNotificationKit` (deny-by-default) + abilità fini
  `notification-kit.<ability>` con fallback.

## Alternatives considered

- **Do nothing** — ogni copy-change resta un deploy e la conferma manuale resta
  impossibile; il bisogno è già presente in più progetti.
- **Intercettazione globale di `Mail`** (listener su `MessageSending`) —
  catturerebbe anche password reset e mail di package terzi; un flag errato
  tratterrebbe tutta la posta dell'app. Scelto l'invio esplicito via package:
  greppabile, testabile, adozione incrementale.
- **View Blade pubblicate invece del DB** — ogni modifica resterebbe un task da
  sviluppatore con deploy; niente storico né archiviazione. Il DB è il
  requisito.
- **Re-render all'approvazione invece dello snapshot** — l'approvatore
  approverebbe contenuto potenzialmente diverso da quello mostrato e i model
  potrebbero essere mutati/cancellati nel frattempo. Lo snapshot è la prova di
  audit.
- **Blade da DB per condizionali/loop** — esecuzione PHP arbitraria da parte di
  chi edita i template. Escluso per sicurezza; il json `metadata` copre la
  configurazione strutturata.

## Consequences

- Più facile: modifiche di copy senza deploy, storico e audit completi,
  approvazione umana per email delicate, stessa soluzione riusabile nei
  progetti Scrapkit.
- Più difficile/costi accettati: i contenuti escono dal VCS (mitigato da
  storico immutabile + default nel codice); le mailable confirmable non
  supportano allegati dinamici in v1 (modello a snapshot); le notifiche non
  sono confirmable (solo content-managed); l'outbox conserva PII (destinatari +
  corpo renderizzato) a scopo audit.
- Migrazione: per ogni mailable gestita l'host passa a `ManagedMailable` +
  invio via `NotificationKit::to()->send()`; niente cambia per le mail non
  gestite.
- Rollback: rimuovere il package e tornare alle Mailable native; le tabelle
  restano come archivio.

## Open questions

1. Quali **due progetti** consumano il package *oggi*? (Gate di
   architecture-guidelines "when to create a package" — da registrare qui prima
   dell'accettazione.)
2. Template archiviato ma ancora triggerato dal codice: invio con warning
   (proposta) o blocco?
3. Retention/pruning dei messaggi outbox inviati (`notification-kit:prune`) —
   v1 o dopo?
