# Changelog

## 1.2.3

- Gutenberg-Kraftstofftabs verwenden native WordPress-Core-Buttons und übernehmen Theme-/Site-Editor-Stile vollständig.
- Der konfigurierte Standardkraftstoff erhält den vom Theme definierten Active-State ohne plugin-eigene hardcodierte Button-Stile.
- Beim Wechsel zwischen Diesel, Super E5 und Super E10 wird der aktive Zustand exklusiv auf genau einen Button übertragen.
- Veraltete plugin-eigene Active-Outline für Gutenberg-Tabs entfernt.
- Maus- und Touch-Klicks hinterlassen keinen dauerhaften Fokusrahmen; Tastatur-Fokus und `:focus-visible` bleiben erhalten.

## 1.2.2

- Kompakte Shortcode-Ausgabe an das auf afd-chemnitz.de eingesetzte Layout angeglichen.
- Drei interaktive Kraftstoff-Tabs für Diesel, Super E5 und Super E10.
- Farbverläufe übernehmen die WordPress-Presets mit identischen Fallbacks.
- Theme-Typografie wird in der kompakten Shortcode-Ausgabe geerbt.

## 1.2.1

- GitHub-Updater liefert auch bei bereits aktueller Version vollständige Update-Metadaten, damit WordPress das Plugin als Auto-Update-fähig erkennt.
- Der Schalter für automatische Plugin-Aktualisierungen wird dadurch in der Plugin-Liste angezeigt.

## 1.2.0

- GitHub-Release-Workflow für Tags `v*` mit Build, Tests und WordPress-kompatiblem Release-ZIP.
- GitHub-Updater bevorzugt das veröffentlichte Release-Asset `afd-spritpreise.zip`.
- Kompaktes Gutenberg-Startlayout und separat gestaltbare Kraftstoff-Tabs.

## 1.1.0

- Gutenberg-Architektur konsequent auf Daten statt Präsentationscontainer reduziert.
- `afd-spritpreise/fuel-price` dient nur noch als Datenkontext und frei bearbeitbare InnerBlocks-Fläche.
- `afd-spritpreise/fuel-tabs` bleibt als spezialisierter Kraftstoffumschalter erhalten.
- Neuer atomarer Block `afd-spritpreise/data-value` für einzelne dynamische Werte.
- Feste Gutenberg-Blöcke `header`, `metric`, `tank-saving`, `cheapest-station`, `demands`, `method`, `price-board` und `facts` entfernt.
- Startlayout verwendet ausschließlich Gutenberg-Core-Blöcke für Überschriften, Texte, Gruppen und Spalten.
- `allowedBlocks`-Einschränkung entfernt; das Start-Template ist vollständig entsperrt.
- Native Gutenberg-Supports um Textausrichtung ergänzt.
- Gutenberg-CSS von den festen Shortcode-Komponenten entkoppelt; Datenwerte erhalten keine Plugin-eigene Preisfeld-Gestaltung.
- Full-/Compact-Renderer bleibt ausschließlich für den eigenständigen Shortcode erhalten.

## 1.0.0

- Erstfassung mit TankPuls-Anbindung, Photon-Gebietssuche, Median- und Szenariorechnung, Cache, Shortcode und GitHub-Updater.
