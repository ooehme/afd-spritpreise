# Testprotokoll

Stand: 19. August 2026

## Umgebung

- PHP 8.3.32
- Node.js 24.15.0
- npm 11.12.1
- Zielkompatibilität: PHP 8.1+, WordPress 6.6–7.0

## Automatisierte Prüfungen

`npm run build`: bestanden. Drei JavaScript-Assets wurden erzeugt und mit `node --check` validiert; `block.json` wurde geparst.

`npm test`: 18/18 bestanden.

- Photon-Extent und Bounding-Box-Validierung
- TankPuls-Parameter, reales `items`-Antwortschema und Stationsfilter
- Median gerade/ungerade und deterministische günstigste Station
- Szenariorechnung ohne vorzeitige Rundung
- Cache Miss, Hit, Expiry, paralleler Lock und Stale-if-error
- Full- und Compact-Renderer
- zwei Blockkonfigurationen mit unterschiedlichen Bounding Boxes
- Shortcode
- Aktivierung und Deaktivierung
- GitHub-Updater
- ausgeführter vollständiger Uninstall mit Options-, Cache-, Lock- und Site-Transient-Bereinigung

PHP-Syntaxprüfung: alle PHP-Dateien bestanden.

ZIP-Prüfung: bestanden. `build/afd-spritpreise.zip` besitzt genau einen Plugin-Wurzelordner `afd-spritpreise/`, enthält die Hauptdatei direkt darunter und enthält keine Entwicklungs- oder Testabhängigkeiten.

## Live-Smoke-Checks

- TankPuls-Endpunkt mit Chemnitz-Bounding-Box: HTTP 200, `items`-Liste mit aktuellen Stationen; `priceCents` als Tausendstel-Euro bestätigt.
- Photon-Suche `Chemnitz`, `lang=de`, `countrycode=DE`: gültige GeoJSON-Antwort; Extent `[12.7275333, 50.9039377, 13.0540169, 50.7413804]` bestätigt.

## Nicht automatisierbar in dieser Umgebung

Die visuelle Browser-QA der lokalen Full-/Compact-Vorschau wurde durch die Sicherheitsrichtlinie der Desktop-App für `127.0.0.1` blockiert. Die Renderer-Struktur, Tab-Zustände, Breakpoints und Overflow-Regeln wurden automatisiert bzw. statisch geprüft. Vor einem öffentlichen Release wird zusätzlich ein manueller Staging-Test mit dem produktiven Theme empfohlen.
