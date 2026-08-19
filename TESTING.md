# Testprotokoll

Stand: 19. August 2026

## Umgebung

- PHP 8.3.32
- Node.js 24.15.0
- npm 11.12.1
- Zielkompatibilität: PHP 8.1+, WordPress 6.6–7.0

## Automatisierte Prüfungen

`npm run build` validiert die JavaScript-Assets und alle vorhandenen `block.json`-Dateien rekursiv.

`npm test` prüft unter anderem:

- Photon-Extent und Bounding-Box-Validierung
- TankPuls-Parameter, Antwortschema und Stationsfilter
- Median und deterministische günstigste Station
- Szenariorechnung ohne vorzeitige Rundung
- Cache Miss, Hit, Expiry, parallelen Lock und Stale-if-error
- eigenständigen Full-/Compact-Renderer des Shortcodes
- Gutenberg-Komponenten und Datenbindung
- native Gutenberg-Design-Supports der Datenblöcke
- freie Verschachtelbarkeit aller Datenblöcke unter `afd-spritpreise/fuel-price`
- Abwesenheit der entfernten Container `price-board` und `facts`
- mehrere Konfigurationen mit unterschiedlichen Bounding Boxes
- Shortcode, Lifecycle, GitHub-Updater und Uninstall

Der JavaScript-Smoke-Test erwartet acht Gutenberg-Blocktypen:

- `afd-spritpreise/fuel-price`
- `afd-spritpreise/header`
- `afd-spritpreise/fuel-tabs`
- `afd-spritpreise/metric`
- `afd-spritpreise/tank-saving`
- `afd-spritpreise/cheapest-station`
- `afd-spritpreise/demands`
- `afd-spritpreise/method`

`price-board` und `facts` dürfen nicht mehr registriert sein.

## Vor einem Release

```bash
npm run build
npm test
php -l afd-spritpreise.php
npm run package
```

Zusätzlich ist ein manueller Staging-Test mit dem produktiven Theme sinnvoll, insbesondere für Theme-spezifische Gutenberg-Presets, Layoutbreiten und responsive Core-Blöcke.
