# AfD Spritpreise

Produktionsreifes WordPress-Plugin für regionale Kraftstoffpreise und eine transparent konfigurierte Szenariorechnung. Das Plugin lädt Tankstellendaten ausschließlich serverseitig, bildet je Kraftstoff den Median und zeigt die günstigste aktive Tankstelle.

## Voraussetzungen

- WordPress 6.6 oder neuer, getestet bis WordPress 7.0
- PHP 8.1 oder neuer
- Ausgehende HTTPS-Verbindungen zu TankPuls, Photon und GitHub
- Keine Node.js- oder Composer-Abhängigkeit auf dem Produktivserver

## Installation

1. `build/afd-spritpreise.zip` in WordPress unter **Plugins → Installieren → Plugin hochladen** auswählen.
2. Plugin aktivieren.
3. Unter **Einstellungen → AfD Spritpreise** ein Standardgebiet mit Photon wählen und die Berechnungswerte prüfen.
4. Den Block **AfD Spritpreise** einfügen oder den Shortcode `[afd_spritpreise]` verwenden.

Alternativ kann der Ordner `afd-spritpreise` nach `wp-content/plugins/` kopiert werden.

## Gebiet und Photon

Photon wird ausschließlich auf der Einstellungsseite und im Gutenberg-Editor über eine berechtigungsgeschützte WordPress-REST-Route abgefragt. Normale Frontend-Aufrufe lösen keine Photon-Anfrage aus.

Die Suche startet ab drei Zeichen, wartet 350 ms, bricht überholte Requests ab, hält einen fünfminütigen Browser-Cache und zeigt maximal fünf deutsche Treffer. Die ausgewählte Bounding Box wird dauerhaft gespeichert und unverändert an TankPuls übergeben.

Photon liefert `extent` als `[west, north, east, south]`. Das Plugin wandelt dies zu `minLat`, `minLng`, `maxLat`, `maxLng` um; die Reihenfolge ist durch einen Regressionstest abgesichert. Fehlt ein Extent, entsteht um die GeoJSON-Kartenposition eine kleine rechteckige Startbox. Es findet weder ein Radius- noch ein Haversine-Filter statt.

Die zuvor vorgesehene interaktive Kartenauswahl wurde auf ausdrücklichen Projektentscheid entfernt. Photon ist die primäre Gebietsauswahl; die gespeicherten Koordinaten können in den Einstellungen unter **Gespeicherte Bounding Box** geprüft oder präzisiert werden.

## Gutenberg

Blockname: `afd-spritpreise/fuel-price`

Jede Instanz speichert nur Konfiguration, nie Livepreise. Im Inspector stehen zur Verfügung:

- Photon-Orts-/PLZ-Suche und Gebietsbezeichnung
- eigene Bounding Box pro Block
- Diesel, Super E5 oder Super E10 als Standard
- vollständige oder kompakte Darstellung
- Titel, Forderungen, Methodik, günstigste Tankstelle, 50-Liter-Ersparnis und Details einzeln schaltbar

Der Block wird auf dem Server gerendert. Mehrere Blöcke mit verschiedenen Gebieten verwenden getrennte Cache-Keys.

## Shortcode

```text
[afd_spritpreise]
[afd_spritpreise display="compact"]
[afd_spritpreise fuel="diesel" display="compact"]
[afd_spritpreise min_lat="50.7413804" min_lng="12.7275333" max_lat="50.9039377" max_lng="13.0540169" area="Chemnitzer Stadtgebiet" fuel="diesel"]
```

Parameter: `fuel`, `display`, `min_lat`, `min_lng`, `max_lat`, `max_lng`, `area` sowie die optionalen Schalter `show_title`, `show_demands`, `show_method`, `show_cheapest_station`, `show_tank_saving`, `show_details_link` (`true`/`false`). Ohne vollständige Koordinaten gilt das globale Standardgebiet.

## Full und Compact

`full` lädt und rendert Diesel, E5 und E10 als zugängliche Tabs. Es enthält Price Board, 50-Liter-Ersparnis, günstigste Tankstelle, Forderungsbeleg und Methodik.

`compact` lädt nur den gewählten Kraftstoff und verwendet eine eigenständige reduzierte HTML-Struktur. Die Card ist für Startseiten-Grids, Gutenberg-Spalten und schmale Mobilansichten ausgelegt. Quellen und Kurzmethodik sind optional aufklappbar.

## TankPuls und Cache

Standard-Endpunkt: `https://api.tankpuls.de/api/search/cheapest`

Übertragen werden ausschließlich Bounding Box, Kraftstoff und Limit. Der Client akzeptiert eine direkte Stationsliste sowie Listen unter `items`, `stations`, `data` oder `results`, validiert die Antwort und berücksichtigt nur aktive, verfügbare Einträge mit plausiblen Preisen. TankPuls liefert `priceCents` aktuell als ganzzahligen Tausendstel-Euro-Wert (`2229` entspricht `2,229 €/l`).

Der Standard-Timeout beträgt 60 Sekunden, der Cache-TTL 15 Minuten. Der Cache-Key enthält Bounding Box, Kraftstoff, API-URL und Limit. Ein atomarer Object-Cache-/Options-Lock verhindert parallele identische Refreshes. Der letzte erfolgreiche Datensatz bleibt sieben Tage als Stale-Fallback verfügbar. Technische Fehler werden nur intern und auf der Einstellungsseite geführt; im Frontend erscheint ohne Daten ausschließlich eine neutrale Meldung.

## Berechnungsmodell

Für jede Kraftstoffart wird der Median ohne vorzeitige Rundung berechnet. Bei gerader Anzahl ist er das arithmetische Mittel der beiden mittleren Preise. Preisgleiche günstigste Tankstellen werden deterministisch nach Stations-ID sortiert.

```text
Netto aktuell = Bruttopreis / (1 + aktuelle MwSt)
CO₂-Kosten = kg CO₂/l × CO₂-Preis €/t / 1000
Szenario-Netto = Netto aktuell - aktuelle Energiesteuer - aktuelle CO₂-Kosten
                 + Szenario-Energiesteuer + Szenario-CO₂-Kosten
Szenario-Brutto = Szenario-Netto × (1 + Szenario-MwSt)
```

Alle Steuer-, CO₂- und Emissionsparameter sind im Backend editierbar. Der Standard-CO₂-Preis von 65 €/t folgt der im Projekt vorgegebenen Beispielrechnung (15,6 ct/l für Benzin). Erdölbevorratungsbeitrag und THG-Quote werden ohne konkrete Zielgröße nicht zusätzlich abgezogen.

## Quellen

- Energiesteuer: [BT-Drs. 21/6332](https://dserver.bundestag.de/btd/21/063/2106332.pdf)
- CO₂-Bepreisung: [BT-Drs. 21/6334](https://dserver.bundestag.de/btd/21/063/2106334.pdf)
- Mehrwertsteuer: [BT-Drs. 21/5326](https://dserver.bundestag.de/btd/21/053/2105326.pdf)
- Preisdaten: [TankPuls](https://tankpuls.de/) · MTS-K
- Geocoding: [Photon](https://photon.komoot.io/) · OpenStreetMap

## Datenschutz und Sicherheit

Das Plugin speichert keine personenbezogenen Nutzerdaten. Photon erhält nur die im Backend oder Editor eingegebene Orts-/PLZ-Suche. Im Frontend werden keine Standortdaten abgefragt. Externe Antworten werden normalisiert und vor der Ausgabe escaped. Einstellungen, Cache-Aktionen und Photon-Proxy sind durch Capabilities und Nonces geschützt.

## Entwicklung und Tests

```bash
npm run build
npm test
php -l afd-spritpreise.php
npm run package
```

Der JavaScript-Build benötigt Node.js 20 oder neuer, aber keine npm-Pakete. `npm test` führt einen eigenständigen PHP-Smoke-/Unit-Testlauf mit WordPress-kompatiblen Stubs aus. Abgedeckt sind Photon-Extent, Bounding-Box-Validierung, TankPuls-Parameter und -Filter, Median, günstigste Station, Szenario, Cache Hit/Miss/Expiry/Lock/Stale, mehrere Blockgebiete, beide Renderer, Shortcode, Lifecycle, Uninstall-Schutz und GitHub-Updater.

## GitHub-Releases und Updates

1. Version in `afd-spritpreise.php`, `block/block.json`, `package.json`, `readme.txt` und `CHANGELOG.md` aktualisieren.
2. `npm run build`, `npm test` und PHP-Syntaxprüfung ausführen.
3. `npm run package` ausführen und die ZIP-Struktur prüfen.
4. Commit und SemVer-Tag erstellen, z. B. `v1.1.0`.
5. Auf GitHub ein Release aus diesem Tag veröffentlichen.
6. `build/afd-spritpreise.zip` als Release-Asset mit exakt dem Namen `afd-spritpreise.zip` anhängen.

Der Updater prüft das neueste öffentliche GitHub-Release höchstens alle zwölf Stunden und bevorzugt dieses Release-Asset. Fehlt es, verwendet er GitHubs `zipball_url` und normalisiert beim Installieren den Verzeichnisnamen.

## Troubleshooting

- **Keine Preise:** API-URL, ausgehende HTTPS-Verbindungen und Cache-Diagnose unter Einstellungen prüfen. Danach **Daten aktualisieren** wählen.
- **Ortssuche ohne Ergebnis:** Mindestens drei Zeichen eingeben und Photon-Erreichbarkeit prüfen.
- **Alte Daten:** Stale-Daten werden bewusst angezeigt, wenn ein API-Refresh fehlschlägt. Der Hinweis steht direkt in der Ausgabe.
- **Updates fehlen:** Release muss veröffentlicht sein und einen gültigen SemVer-Tag besitzen. Den Site-Transient `afdsp_github_release` löschen oder zwölf Stunden warten.

## Bekannte Einschränkungen

- Es gibt auf Projektentscheidung keine interaktive Karte; ungewöhnliche Photon-Gebietsgrenzen können über die technischen Koordinaten korrigiert werden.
- Ohne Photon-Extent wird eine kleine rechteckige Startbox erzeugt und sollte vor Veröffentlichung geprüft werden.
- GitHubs anonyme API-Rate-Limits gelten weiterhin, werden durch den zwölfstündigen Cache aber stark reduziert.
- Die Tests simulieren WordPress-Kernfunktionen; ein finaler Staging-Test mit dem eingesetzten Theme, Object Cache und Hosting bleibt für jede Installation empfohlen.

## Lizenz

GPL-2.0-or-later. Siehe [LICENSE](LICENSE).
