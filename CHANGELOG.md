# Changelog

Tutte le modifiche degne di nota a `scrapkit/notification-kit`.

Il formato segue [Keep a Changelog](https://keepachangelog.com/it/1.1.0/) e il
versionamento è [semantico](https://semver.org/lang/it/).

## [0.1.1] - 2026-07-22

### Fixed

- I nomi degli indici su `template_versions` e `outbox_messages` superavano i
  64 caratteri ammessi da MySQL e MariaDB, facendo fallire `migrate`
  sull'applicazione host. Ora sono espliciti, e un test verifica il limite
  (la suite gira su SQLite, che non lo applica).

## [0.1.0] - 2026-07-22

### Added

- Contratto `Manageable` e comando `notification-kit:sync` per portare in
  database le definizioni dichiarate nel codice, senza mai sovrascrivere le
  modifiche fatte dagli utenti.
- Contenuti in Markdown con segnaposto risolti da un renderer sicuro
  (`html_input: escape`, link non sicuri disabilitati, valori HTML-escaped).
- Segnaposto utilizzabili come destinazione di un link
  (`[Testo]({{ action.url }})`), risolti prima del parsing Markdown perché il
  link venga generato davvero.
- Archiviazione reversibile e storico immutabile di ogni modifica.
- Coda di approvazione per le email che richiedono conferma manuale: snapshot
  renderizzato al momento della richiesta, invio in coda dopo l'approvazione,
  macchina a stati con transizioni guardate.
- Guardia contro l'invio diretto via `Mail::send()` di una mailable che
  richiede conferma.
- API JSON versionata (`notification-kit/api/v1`) con autorizzazione stile
  Horizon: gate d'ingresso `viewNotificationKit` (deny-by-default) e abilità
  fini opzionali.
- Stub React/Inertia/TypeScript pubblicabili: elenco contenuti, editor con
  anteprima live e palette segnaposto, coda di approvazione e
  `ConfirmSendModal`.
