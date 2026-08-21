# AfD Spritpreise

WordPress-Plugin für regionale Kraftstoffpreise und eine transparent konfigurierte Szenariorechnung. Tankstellendaten werden serverseitig geladen, je Kraftstoff wird der Median berechnet.

Autor: Oliver Oehme · Projektseite: https://oliveroehme.de/werkzeuge/afd-spritpreise

## Voraussetzungen

- WordPress 6.6 oder neuer
- PHP 8.1 oder neuer
- ausgehende HTTPS-Verbindungen zu TankPuls, Photon und GitHub
- Node.js 20+ nur für Entwicklung und Build

## Gutenberg

Im Gutenberg-Inserter gibt es zwei eigenständige Hauptblöcke:

- `AfD Spritpreise` (`afd-spritpreise/fuel-price`) – ausführlicher Preisvergleich mit Rechenbestandteilen und Methodik
- `AfD Spritpreise – kompakt` (`afd-spritpreise/fuel-price-compact`) – kompakte Karte mit Kraftstofftabs, drei Preisfeldern und Quellenzeile

Beide Hauptblöcke verwenden dasselbe benutzerdefinierte Gutenberg-Symbol und denselben Datenkontext. Neue Blöcke erhalten jeweils ein eigenes Default-Layout; bereits gespeicherte Inhalte werden nicht migriert oder umgebaut.

Gemeinsame Plugin-Bausteine:

- `afd-spritpreise/fuel-tabs` – interaktiver Kraftstoffumschalter
- `afd-spritpreise/fuel-tab` – einzelner Kraftstoff-Tab mit nativem WordPress-Core-Button
- `afd-spritpreise/data-value` – ein einzelner dynamischer Datenwert

Das ausführliche Default-Layout enthält:

- Überschrift „Das kostet Kraftstoff mit der AfD“
- Tabs für Diesel, Super E5 und Super E10
- aktueller Preis, Preis nach AfD-Forderungen und mögliche Ersparnis
- Median-/50-Liter-Zeile
- aktuell günstigste Tankstelle für den gewählten Kraftstoff
- Energiesteuer, CO₂-Bepreisung und Mehrwertsteuer mit Bundestagsdrucksachen
- Hinweis zu weiteren nicht eingerechneten Abgaben
- Methodik und Quellenzeile

Das kompakte Default-Layout verwendet die abgestimmte Kartenansicht mit Hellblau-/Dunkelblau-/Rot-/Nachtblau-Verläufen, drei Kraftstofftabs, drei Preisfeldern und Quellenzeile.

Layout und Präsentation der Gutenberg-Blöcke erfolgen mit normalen Gutenberg-Core-Blöcken. Überschriften, Texte, Gruppen, Spalten, Buttons, Hintergründe und Abstände können im Editor geändert werden. Die Default-Layouts sind Startpunkte und nicht gesperrt.

### Atomare Datenwerte

`afd-spritpreise/data-value` kann unterhalb eines der beiden Hauptblöcke beliebig tief in Core-Blöcken verschachtelt werden. Im Inspector wird ausgewählt, welcher Wert ausgegeben wird und welches HTML-Element verwendet wird.

Verfügbare Werte:

- Gebiet/Kraftstoff: `area_label`, `fuel_label`, `station_count`, `intro`
- Preise: `current`, `scenario`, `scenario_note`, `saving`, `saving_percent`, `saving_50l`
- Tankstelle: `station_label`, `station_name`, `station_address`, `station_price`
- Rechenwerte: `energy_change`, `energy_effect`, `co2_change`, `co2_effect`, `vat_change`, `vat_effect`
- Methodik: `method`

Der Datenwert besitzt native Gutenberg-Supports für Farben, Verläufe, Schrift, Textausrichtung, Abstände, Rahmen, Schatten, Mindestgrößen, Position und Ausrichtung.

## Block Context

Beide Hauptblöcke stellen dieselben Werte über Gutenberg Block Context bereit:

- Bounding Box
- Gebietsbezeichnung
- Standardkraftstoff

`data-value` und `fuel-tabs` akzeptieren beide Hauptblöcke als Vorfahren. Gruppen, Spalten oder andere Core-Blöcke dürfen dazwischen liegen.

Ist `fuel-tabs` vorhanden, enthält der Hauptblock die Daten für Diesel, E5 und E10. Beim Umschalten aktualisiert `frontend.js` alle Elemente mit `data-afdsp-bind`, ohne eine neue Browser-Anfrage an die Preis-API.

## Shortcodes

Die Shortcode-Ausgabe ist bewusst von Gutenberg getrennt und theme-unabhängig gestaltet.

Normal:

```text
[afd_spritpreise display="full"]
```

Kompakt:

```text
[afd_spritpreise display="compact"]
```

Weitere Beispiele:

```text
[afd_spritpreise fuel="diesel" display="compact"]
[afd_spritpreise min_lat="50.7413804" min_lng="12.7275333" max_lat="50.9039377" max_lng="13.0540169" area="Chemnitzer Stadtgebiet" fuel="diesel" display="full"]
```

Der normale Shortcode orientiert sich visuell am ausführlichen Gutenberg-Default. Der Compact-Shortcode orientiert sich am kompakten Gutenberg-Default. Beide Shortcode-Renderer besitzen eigene fest definierte Styles, damit die Darstellung auch unter anderen Themes konsistent bleibt.

Unter **Einstellungen → AfD Spritpreise** gibt es eine Infobox **Shortcodes** mit den beiden Standardvarianten und Kopier-Buttons.

## Backend

Unter **Einstellungen → AfD Spritpreise** werden konfiguriert:

- Standardgebiet inklusive Bounding Box
- TankPuls-Zugriff und Cache
- Photon-Endpunkt
- Berechnungswerte für Energiesteuer, CO₂ und Mehrwertsteuer

Ein eigener Abschnitt „Darstellung“ existiert nicht mehr. Die Gutenberg-Darstellung wird durch die zwei getrennten Hauptblöcke gewählt; bei Shortcodes wird die Variante explizit über `display="full"` oder `display="compact"` bestimmt.

## Entwicklung

```powershell
npm run build
npm test
npm run package
```

`npm run build` prüft die JavaScript-Dateien, kopiert die Dateien aus `src/` nach `assets/js/` und validiert alle `block.json`-Dateien.

`npm run package` erzeugt:

```text
build/afd-spritpreise.zip
```

## Releases und automatische Updates

WordPress prüft die GitHub-API auf das neueste veröffentlichte Release. Ist dessen Version höher als `AFDSP_VERSION`, erscheint das Update regulär unter **Dashboard → Aktualisierungen** und in der Plugin-Liste.

Ein Push eines Tags im Format `vX.Y.Z` startet `.github/workflows/release.yml`. Der Workflow:

1. prüft die Release-Version,
2. baut die JavaScript-Assets,
3. führt die Tests aus,
4. erzeugt `afd-spritpreise.zip`,
5. validiert die ZIP-Struktur,
6. erstellt ein GitHub Release und hängt `afd-spritpreise.zip` als Release-Asset an.

Vor einem Release müssen diese Versionsangaben identisch sein:

```text
afd-spritpreise.php  → Version: X.Y.Z und AFDSP_VERSION
readme.txt            → Stable tag: X.Y.Z
package.json          → "version": "X.Y.Z"
```

Release lokal auslösen:

```bash
git checkout main
git pull
git tag -a vX.Y.Z -m "AfD Spritpreise X.Y.Z"
git push origin vX.Y.Z
```

Für 1.2.4 nach dem Merge des Release-PRs:

```bash
git checkout main
git pull
git tag -a v1.2.4 -m "AfD Spritpreise 1.2.4"
git push origin v1.2.4
```

Den Tag erst erstellen, nachdem der Release-PR in `main` gemergt und der lokale Stand aktualisiert wurde.

## Datenschutz

Das Plugin speichert keine personenbezogenen Nutzerdaten. Preisabfragen laufen serverseitig über TankPuls. Photon wird nur bei aktiver Gebietssuche im Backend oder Block-Editor verwendet.
