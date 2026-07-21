# Changelog

Tutte le modifiche degne di nota a `scrapkit/notification-kit`.

Il formato segue [Keep a Changelog](https://keepachangelog.com/it/1.1.0/) e il
versionamento è [semantico](https://semver.org/lang/it/).

## [Unreleased]

### Added

- Contratto `Manageable` e comando `notification-kit:sync` per portare in
  database le definizioni dichiarate nel codice, senza mai sovrascrivere le
  modifiche fatte dagli utenti.
- Contenuti in Markdown con segnaposto risolti da un renderer sicuro
  (`html_input: escape`, link non sicuri disabilitati, valori HTML-escaped).
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
