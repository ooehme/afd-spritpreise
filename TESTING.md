# Testprotokoll

Stand: 19. August 2026

## Zielarchitektur

Der Gutenberg-Baukasten registriert genau drei Plugin-Blöcke:

- `afd-spritpreise/fuel-price`
- `afd-spritpreise/fuel-tabs`
- `afd-spritpreise/data-value`

Alle früheren Präsentationsblöcke müssen abwesend sein. Layout und statische Inhalte werden mit Gutenberg-Core-Blöcken gebaut.

## Automatisierte Prüfungen

`npm run build` validiert die JavaScript-Assets und alle vorhandenen `block.json`-Dateien rekursiv.

`npm test` prüft unter anderem:

- Photon-Extent und Bounding-Box-Validierung
- TankPuls-Parameter, Antwortschema und Stationsfilter
- Median und deterministische günstigste Station
- Szenariorechnung ohne vorzeitige Rundung
- Cache Miss, Hit, Expiry, parallelen Lock und Stale-if-error
- eigenständigen Full-/Compact-Renderer des Shortcodes
- atomare Gutenberg-Datenwerte und Frontend-Datenbindung
- freie Verschachtelbarkeit von `fuel-tabs` und `data-value`
- native Gutenberg-Design-Supports inklusive Textausrichtung
- Abwesenheit aller entfernten Präsentationsblöcke
- mehrere Konfigurationen mit unterschiedlichen Bounding Boxes
- Shortcode, Lifecycle, GitHub-Updater und Uninstall

Der JavaScript-Smoke-Test erwartet exakt die drei oben genannten Blocktypen und lehnt die entfernten Blocktypen ausdrücklich ab.

## Vor einem Merge oder Release

```bash
npm run build
npm test
php -l afd-spritpreise.php
php -l includes/class-block.php
php -l includes/class-componentrenderer.php
npm run package
```

Zusätzlich im Gutenberg-Editor manuell prüfen:

1. `data-value` in Gruppe → Spalten → Spalte verschieben.
2. Schriftgröße, Schriftfamilie, Textausrichtung, Farbe, Hintergrund, Padding, Margin, Rahmen und Schatten ändern.
3. Beschriftung als normalen Absatz oder normale Überschrift daneben setzen.
4. Kraftstoff umschalten und prüfen, dass alle `data-value`-Blöcke aktualisiert werden.
5. Standard-Template vollständig umbauen und zusätzliche Core-Blöcke einfügen.
