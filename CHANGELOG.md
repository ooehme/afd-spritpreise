# Changelog

Alle relevanten Änderungen werden in dieser Datei dokumentiert. Das Projekt verwendet [Semantic Versioning](https://semver.org/lang/de/).

## [1.1.0] - 2026-08-19

### Added

- Frei verschachtelbarer Gutenberg-Baukasten mit dynamischen Datenblöcken für Überschrift, Kraftstoffauswahl, einzelne Preisfelder, 50-Liter-Ersparnis, günstigste Tankstelle, Forderungen und Methodik
- Native Gutenberg-Designoptionen für Farben, Hintergründe, Verläufe, Abstände, Typografie, Rahmen, Schatten, Dimensionen und weitere Block-Supports
- Standard-Template auf Basis normaler Core-Blöcke wie Gruppe, Spalten und Spalte
- Clientseitige Aktualisierung frei angeordneter Datenblöcke beim Kraftstoffwechsel

### Changed

- Preisfelder benötigen nur noch `afd-spritpreise/fuel-price` als Vorfahren und können beliebig tief in Core-Blöcken verschachtelt werden
- 50-Liter-Ersparnis und günstigste Tankstelle sind ebenfalls frei unterhalb des Hauptblocks platzierbar
- Gutenberg übernimmt Layout und Gestaltung; plugin-eigene Layout-Container sind nicht mehr Teil der Block-Architektur
- Manuelle Font-Family-/Font-Weight-Sonderlogik entfernt zugunsten nativer Block-Supports

### Removed

- Obsoleter Block `afd-spritpreise/price-board`
- Obsoleter Block `afd-spritpreise/facts`
- Fallback für alte selbstschließende Blockstrukturen
- Kompatibilitätslogik und Tests für frühere, noch nicht veröffentlichte Blockstrukturen

## [1.0.0] - 2026-08-19

### Added

- Entwicklungsstand mit dynamischem Gutenberg-Block und Shortcode
- Photon-Suche mit validierter Extent-Konvertierung und eigener Bounding Box je Instanz
- TankPuls-Client, Median, günstigste Station und editierbare Szenariorechnung
- Cache mit TTL, parallelem Refresh-Lock, Stale-if-error und Backend-Diagnose
- Eigenständige Full-/Compact-Ausgabe für den Shortcode
- GitHub-Release-Updater, vollständiger Lifecycle und Uninstall-Bereinigung
- Test-, Build- und Release-Paketierungsprozess
