# Changelog

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
