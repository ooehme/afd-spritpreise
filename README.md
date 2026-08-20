# AfD Spritpreise

WordPress-Plugin für regionale Kraftstoffpreise und eine transparent konfigurierte Szenariorechnung. Tankstellendaten werden serverseitig geladen, je Kraftstoff wird der Median berechnet.

Autor: Oliver Oehme · Projektseite: https://oliveroehme.de/werkzeuge/afd-spritpreise

## Voraussetzungen

- WordPress 6.6 oder neuer
- PHP 8.1 oder neuer
- ausgehende HTTPS-Verbindungen zu TankPuls, Photon und GitHub
- Node.js 20+ nur für Entwicklung und Build

## Gutenberg-Architektur

Der Gutenberg-Teil besteht bewusst nur noch aus drei Plugin-Blöcken:

- `afd-spritpreise/fuel-price` – Datenkontext und frei bearbeitbare InnerBlocks-Fläche
- `afd-spritpreise/fuel-tabs` – interaktiver Kraftstoffumschalter
- `afd-spritpreise/data-value` – genau ein dynamischer Datenwert

Alle Präsentations- und Layoutaufgaben werden normalen Gutenberg-Core-Blöcken überlassen. Überschriften, Texte, Gruppen, Zeilen, Stapel, Spalten, Hintergründe und Abstände sind keine Plugin-Komponenten mehr.

Der Hauptblock hat weder `templateLock` noch eine `allowedBlocks`-Einschränkung. Das Start-Template ist lediglich ein Vorschlag und kann vollständig umgebaut oder gelöscht werden.

Beispiel:

```text
AfD Spritpreise
├── Gruppe
│   ├── Überschrift (core/heading)
│   └── Datenwert: Einleitung
├── Kraftstoffauswahl
├── Spalten (core/columns)
│   ├── Spalte
│   │   ├── Absatz: Aktueller Preis
│   │   └── Datenwert: Aktueller Preis
│   ├── Spalte
│   │   └── Datenwert: Szenariopreis
│   └── Spalte
│       └── Datenwert: Ersparnis
└── beliebige weitere Core-Blöcke
```

### Atomarer Datenwert

`afd-spritpreise/data-value` kann unterhalb des Hauptblocks beliebig tief in Core-Blöcken verschachtelt werden. Im Inspector wird ausgewählt, welcher Wert ausgegeben wird und welches HTML-Element der Block verwendet.

Verfügbare Werte:

- Gebiet und Kraftstoff: `area_label`, `fuel_label`, `station_count`, `intro`
- Preise: `current`, `scenario`, `scenario_note`, `saving`, `saving_percent`, `saving_50l`
- günstigste Tankstelle: `station_label`, `station_name`, `station_address`, `station_price`
- Rechenwerte: `energy_change`, `energy_effect`, `co2_change`, `co2_effect`, `vat_change`, `vat_effect`
- Methodik: `method`

Der Datenwert besitzt native Gutenberg-Supports für Farben, Verläufe, Schrift, Textausrichtung, Abstände, Rahmen, Schatten, Mindestgrößen, Position und Ausrichtung. Das Plugin erzwingt für diesen Block keine eigene Preisfeld-Optik.

Damit können Beschriftung und Wert voneinander unabhängig aufgebaut werden. Eine Beschriftung kann beispielsweise ein normaler `core/paragraph` oder `core/heading` sein, während der dynamische Wert separat gestaltet wird.

## Block Context

Der Hauptblock stellt folgende Werte über Gutenberg Block Context bereit:

- Bounding Box
- Gebietsbezeichnung
- Standardkraftstoff

`data-value` und `fuel-tabs` verwenden nur `ancestor: ["afd-spritpreise/fuel-price"]`. Gruppen, Spalten oder andere Core-Blöcke dürfen deshalb dazwischen liegen.

Ist `fuel-tabs` vorhanden, enthält der Hauptblock die Daten für Diesel, E5 und E10. Beim Umschalten aktualisiert `frontend.js` alle Elemente mit `data-afdsp-bind`, ohne eine neue Browser-Anfrage an die Preis-API.

## Shortcode

Der Shortcode bleibt bewusst getrennt vom Gutenberg-Baukasten:

```text
[afd_spritpreise]
[afd_spritpreise display="compact"]
[afd_spritpreise fuel="diesel" display="compact"]
[afd_spritpreise min_lat="50.7413804" min_lng="12.7275333" max_lat="50.9039377" max_lng="13.0540169" area="Chemnitzer Stadtgebiet" fuel="diesel"]
```

Der Full-/Compact-Renderer und dessen CSS werden ausschließlich für diese eigenständige Shortcode-Ausgabe weitergeführt. Sie sind keine Gutenberg-Kompatibilitätsschicht.

## Entwicklung

```powershell
npm run build
npm test
npm run package
```

`npm run build` prüft die JavaScript-Dateien, kopiert `src/*.js` nach `assets/js/` und validiert alle vorhandenen `block.json`-Dateien.

`npm run package` erzeugt:

```text
build/afd-spritpreise.zip
```

## Datenschutz

Das Plugin speichert keine personenbezogenen Nutzerdaten. Preisabfragen laufen serverseitig über TankPuls. Photon wird nur bei aktiver Gebietssuche im Backend oder Block-Editor verwendet.
