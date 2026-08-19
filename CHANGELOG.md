# Changelog

Alle relevanten Änderungen werden in dieser Datei dokumentiert. Das Projekt verwendet [Semantic Versioning](https://semver.org/lang/de/).

## [1.1.0] - 2026-08-19

### Added

- Verschachtelter Gutenberg-Baukasten mit dynamischen Unterblöcken für alle wesentlichen Ausgabebereiche
- Einzelne Preisfelder für aktuellen Preis, Szenariopreis und Ersparnis mit jeweils eigenen Gutenberg-Designoptionen
- Native Farben, Hintergründe, Verläufe, Abstände, Typografie, Ausrichtung, Rahmen und Schatten je Abschnitt
- Theme-Schriftfamilien und konfigurierbare Schriftstärken je Unterblock
- Raster-, Listen- und Inline-Darstellung mit ein bis vier Spalten für Price Board und Zusatzinformationen
- Blockstile Card, Editorial und Vom Theme
- Clientseitige Aktualisierung aller frei angeordneten Komponenten beim Kraftstoffwechsel

### Compatibility

- Bestehende selbstschließende 1.0-Blöcke verwenden weiterhin den bisherigen Renderer und werden erst beim Bearbeiten in den Baukasten überführt.

## [1.0.0] - 2026-08-19

### Added

- Dynamischer Gutenberg-Block und Shortcode mit gemeinsamem Server-Renderer
- Photon-Suche mit validierter Extent-Konvertierung und eigener Bounding Box je Instanz
- TankPuls-Client, Median, günstigste Tankstelle und editierbare Szenariorechnung
- Cache mit TTL, parallelem Refresh-Lock, Stale-if-error und Backend-Diagnose
- Eigenständige Full- und Compact-Darstellung
- GitHub-Release-Updater, vollständiger Lifecycle und Uninstall-Bereinigung
- Test-, Build- und Release-Paketierungsprozess

### Changed

- Interaktive Kartenauswahl auf Projektentscheidung entfernt; Photon ist die alleinige Gebietsauswahl.
