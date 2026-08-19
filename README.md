# AfD Spritpreise

WordPress-Plugin für regionale Kraftstoffpreise und eine transparent konfigurierte Szenariorechnung. Das Plugin lädt Tankstellendaten serverseitig, bildet je Kraftstoff den Median und stellt die Werte sowohl als frei gestaltbaren Gutenberg-Baukasten als auch über einen Shortcode bereit.

## Voraussetzungen

- WordPress 6.6 oder neuer
- PHP 8.1 oder neuer
- ausgehende HTTPS-Verbindungen zu TankPuls, Photon und GitHub
- Node.js 20+ nur für Entwicklung und Build

## Installation

1. `build/afd-spritpreise.zip` unter **Plugins → Installieren → Plugin hochladen** auswählen.
2. Plugin aktivieren.
3. Unter **Einstellungen → AfD Spritpreise** Standardgebiet und Rechenwerte konfigurieren.
4. Den Block **AfD Spritpreise** einfügen oder den Shortcode `[afd_spritpreise]` verwenden.

## Gutenberg-Baukasten

Hauptblock: `afd-spritpreise/fuel-price`

Datenblöcke:

- `afd-spritpreise/header`
- `afd-spritpreise/fuel-tabs`
- `afd-spritpreise/metric`
- `afd-spritpreise/tank-saving`
- `afd-spritpreise/cheapest-station`
- `afd-spritpreise/demands`
- `afd-spritpreise/method`

Die Datenblöcke benötigen nur `afd-spritpreise/fuel-price` als Vorfahren. Sie müssen **nicht** direkte Kinder des Hauptblocks sein. Damit sind beispielsweise folgende Strukturen möglich:

```text
AfD Spritpreise
├── Überschrift
├── Kraftstoffauswahl
├── Gruppe
│   └── Spalten
│       ├── Spalte
│       │   └── Preisfeld: Aktuell
│       ├── Spalte
│       │   └── Preisfeld: AfD-Szenario
│       └── Spalte
│           └── Preisfeld: Ersparnis
├── Gruppe / Zeile / Stapel
│   ├── 50-Liter-Ersparnis
│   └── Günstigste Tankstelle
├── Forderungen
└── Methodik
```

Das mitgelieferte Start-Template verwendet Core-Blöcke als Beispiel, ist aber nicht gesperrt. Blöcke können verschoben, ersetzt, gruppiert und beliebig tief verschachtelt werden.

### Gestaltung

Layout wird bewusst nicht durch Plugin-eigene Container vorgegeben. Für Raster, Zeilen, Stapel, Spalten, Breiten und responsive Anordnung werden die normalen Gutenberg-Core-Blöcke verwendet.

Die Plugin-Datenblöcke aktivieren native Gutenberg-Block-Supports für unter anderem:

- Text-, Hintergrund- und Linkfarben
- Verläufe
- Innen- und Außenabstände
- Schriftgröße und Zeilenhöhe
- Theme-Schriftfamilien und Schriftschnitt
- Schriftstil, Texttransformation, Textdekoration und Zeichenabstand
- Rahmen und Radius
- Schatten
- Mindesthöhe und Mindestbreite
- Ausrichtung und Anker, soweit für den Block sinnvoll

Welche Presets und Regler tatsächlich angeboten werden, hängt zusätzlich vom aktiven Theme und dessen `theme.json` ab.

### Preisfelder

`afd-spritpreise/metric` besitzt die Kennzahlen:

- `current` – aktueller regionaler Medianpreis
- `scenario` – berechneter Szenariopreis
- `saving` – mögliche Ersparnis je Liter

Ein Preisfeld kann direkt unter dem Hauptblock oder innerhalb normaler Gutenberg-Blöcke liegen. Der frühere Plugin-Container `price-board` wurde vollständig entfernt. Gleiches gilt für den früheren Container `facts`.

## Datenkontext und Kraftstoffwechsel

Der Hauptblock stellt Bounding Box, Gebietsbezeichnung und Standardkraftstoff per Gutenberg Block Context für alle Nachfahren bereit. Dadurch funktionieren die dynamischen Datenblöcke auch dann, wenn Gruppen, Spalten oder andere Core-Blöcke dazwischen liegen.

Ist der Block `fuel-tabs` vorhanden, lädt der serverseitige Hauptblock die Daten für Diesel, E5 und E10 in einen eingebetteten Datensatz. Beim Umschalten werden die gebundenen Datenfelder clientseitig aktualisiert, ohne eine weitere Preis-API-Abfrage auszulösen.

## Gebiet und Photon

Photon wird ausschließlich auf der Einstellungsseite und im Gutenberg-Editor über eine WordPress-REST-Route abgefragt. Normale Frontend-Aufrufe lösen keine Photon-Anfrage aus.

Die ausgewählte Bounding Box wird im Block gespeichert und serverseitig an TankPuls übergeben. Ohne vollständige Koordinaten wird das konfigurierte Standardgebiet verwendet.

## Shortcode

Der Shortcode ist vom Gutenberg-Baukasten getrennt und besitzt weiterhin seine eigenständige Full-/Compact-Ausgabe:

```text
[afd_spritpreise]
[afd_spritpreise display="compact"]
[afd_spritpreise fuel="diesel" display="compact"]
[afd_spritpreise min_lat="50.7413804" min_lng="12.7275333" max_lat="50.9039377" max_lng="13.0540169" area="Chemnitzer Stadtgebiet" fuel="diesel"]
```

Parameter: `fuel`, `display`, `min_lat`, `min_lng`, `max_lat`, `max_lng`, `area` sowie die optionalen `show_*`-Schalter.

Die Full-/Compact-Renderer werden weiterhin benötigt, weil sie die aktive Shortcode-Funktion implementieren. Sie sind keine Kompatibilitätsschicht für Gutenberg.

## TankPuls und Cache

Standard-Endpunkt: `https://api.tankpuls.de/api/search/cheapest`

Übertragen werden Bounding Box, Kraftstoff und Limit. Berücksichtigt werden aktive, verfügbare Stationen mit plausiblen Preisen.

Der Standard-Cache-TTL beträgt 15 Minuten. Ein Lock verhindert parallele identische Refreshes. Der letzte erfolgreiche Datensatz bleibt als Stale-Fallback verfügbar, wenn ein Refresh fehlschlägt.

## Berechnungsmodell

```text
Netto aktuell = Bruttopreis / (1 + aktuelle MwSt)
CO₂-Kosten = kg CO₂/l × CO₂-Preis €/t / 1000
Szenario-Netto = Netto aktuell - aktuelle Energiesteuer - aktuelle CO₂-Kosten
                 + Szenario-Energiesteuer + Szenario-CO₂-Kosten
Szenario-Brutto = Szenario-Netto × (1 + Szenario-MwSt)
```

Intern wird ohne vorzeitige Rundung gerechnet. Steuer-, CO₂- und Emissionsparameter sind im Backend editierbar.

## Quellen

- Energiesteuer: BT-Drs. 21/6332
- CO₂-Bepreisung: BT-Drs. 21/6334
- Mehrwertsteuer: BT-Drs. 21/5326
- Preisdaten: TankPuls · MTS-K
- Geocoding: Photon · OpenStreetMap

## Entwicklung und Tests

```bash
npm run build
npm test
php -l afd-spritpreise.php
npm run package
```

Der Build kopiert `src/*.js` nach `assets/js/` und validiert alle vorhandenen `block.json`-Dateien. Der JavaScript-Smoke-Test erwartet acht Gutenberg-Blocktypen und stellt sicher, dass die entfernten Container `price-board` und `facts` nicht registriert werden.

## Architekturentscheidung

Das Plugin ist noch nicht veröffentlicht. Deshalb gibt es keine Migrations- oder Rückwärtskompatibilitätsschicht für frühere Entwicklungsstände. Alte selbstschließende Gutenberg-Strukturen, `price-board`, `facts` und entsprechende Fallbacks wurden bewusst entfernt. Der aktuelle Blockaufbau ist die einzige unterstützte Gutenberg-Struktur.

## Lizenz

GPL-2.0-or-later. Siehe `LICENSE`.
