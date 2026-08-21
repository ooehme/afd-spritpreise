=== AfD Spritpreise ===
Contributors: ooehme
Tags: kraftstoffpreise, tankstellen, median, shortcode, gutenberg
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Regionale Median-Kraftstoffpreise und ein transparent konfigurierbares Steuerszenario als frei gestaltbarer Gutenberg-Datenbaukasten oder Shortcode.

== Description ==

AfD Spritpreise von Oliver Oehme lädt aktuelle Tankstellendaten serverseitig von TankPuls, berechnet den Median für Diesel, Super E5 und Super E10 und stellt daraus dynamische Datenwerte bereit.

Projektseite: https://oliveroehme.de/werkzeuge/afd-spritpreise

Im Gutenberg-Inserter stehen zwei eigenständige Hauptblöcke zur Verfügung:

* `AfD Spritpreise` (`afd-spritpreise/fuel-price`) mit ausführlichem Default-Layout für Preisvergleich, 50-Liter-Ersparnis, aktuell günstigste Tankstelle, Rechenbestandteile und Methodik
* `AfD Spritpreise – kompakt` (`afd-spritpreise/fuel-price-compact`) mit kompaktem Default-Layout aus Kraftstofftabs, drei Preisfeldern und Quellenzeile

Beide Hauptblöcke verwenden dasselbe benutzerdefinierte Symbol und denselben Datenkontext. Bestehende Inhalte bleiben unverändert kompatibel.

Gemeinsame Plugin-Blöcke sind `fuel-tabs`, `fuel-tab` und `data-value`. Layout und Präsentation erfolgen mit normalen Gutenberg-Core-Blöcken wie Gruppe, Spalten, Überschrift, Absatz und Button. Die Default-Layouts sind vollständig bearbeitbare Startpunkte.

Der Datenwert kann frei unterhalb eines der beiden Hauptblöcke verschachtelt und mit nativen Gutenberg-Einstellungen für Farben, Typografie, Textausrichtung, Abstände, Rahmen, Schatten und Größen gestaltet werden.

Die Shortcode-Ausgabe bleibt eigenständig und theme-unabhängig:

* normal: `[afd_spritpreise display="full"]`
* kompakt: `[afd_spritpreise display="compact"]`

Der normale Shortcode orientiert sich am detaillierten Gutenberg-Default, der Compact-Shortcode am kompakten Gutenberg-Default. Unter Einstellungen → AfD Spritpreise gibt es eine Shortcode-Infobox mit Kopier-Buttons für beide Varianten.

== Installation ==

1. Plugin-ZIP unter Plugins → Installieren hochladen.
2. Plugin aktivieren.
3. Unter Einstellungen → AfD Spritpreise Standardgebiet und Rechenwerte konfigurieren.
4. Einen der beiden Gutenberg-Hauptblöcke einfügen oder einen der Shortcodes verwenden.

== Frequently Asked Questions ==

= Kann ich normale Gutenberg-Gruppen und Spalten verwenden? =

Ja. `fuel-tabs` und `data-value` benötigen nur `afd-spritpreise/fuel-price` oder `afd-spritpreise/fuel-price-compact` als Vorfahren. Beliebige Core-Blöcke dürfen dazwischen liegen.

= Kann ich Preis, Beschriftung und Hinweis getrennt gestalten? =

Ja. Beschriftungen und Layout werden mit normalen Core-Blöcken aufgebaut. Jeder dynamische Wert ist ein eigener `data-value`-Block und kann separat gestaltet werden.

= Welche Shortcodes gibt es? =

Für die normale Ansicht `[afd_spritpreise display="full"]`, für die kompakte Ansicht `[afd_spritpreise display="compact"]`. Im Backend können beide Varianten direkt kopiert werden.

= Welche Shortcode-Parameter gibt es? =

`fuel`, `display`, `min_lat`, `min_lng`, `max_lat`, `max_lng`, `area` und optionale `show_*`-Schalter.

= Ruft das Frontend Photon auf? =

Nein. Photon wird nur bei der Gebietskonfiguration im Backend und Editor aufgerufen. Das Frontend nutzt die gespeicherte Bounding Box.

== Privacy ==

Das Plugin speichert keine personenbezogenen Nutzerdaten. Es sendet serverseitige Preisabfragen an TankPuls und nur bei aktiver Backend-/Editor-Suche den eingegebenen Suchtext an Photon.

== Changelog ==

= 1.2.4 =

* Zwei klar getrennte Gutenberg-Hauptblöcke: ausführlich und kompakt, jeweils mit eigenem Default-Layout.
* Ausführliches Gutenberg-Default-Layout an die aktuelle Preis-, Tankstellen-, Rechen- und Methodikdarstellung angepasst.
* Compact-Gutenberg-Default und beide Shortcode-Varianten visuell aufeinander abgestimmt.
* Full- und Compact-Shortcode besitzen eigene theme-unabhängige Styles und bleiben dadurch unter unterschiedlichen Themes konsistent.
* Backend-Abschnitt „Darstellung“ entfernt; Full/Compact wird über Blocktyp bzw. explizites Shortcode-Attribut gewählt.
* Neue Shortcode-Infobox im Backend mit Kopier-Buttons für `[afd_spritpreise display="full"]` und `[afd_spritpreise display="compact"]`.
* Tankstellenbezeichnung auf „aktuell günstigste Tankstelle für …“ vereinheitlicht.
* Pointer-/Touch-Fokusrahmen der Gutenberg-Kraftstofftabs korrigiert, Tastaturfokus bleibt erhalten.
* Beide Gutenberg-Hauptblöcke verwenden dasselbe benutzerdefinierte SVG-Symbol; Unterblöcke erhalten passende Funktionssymbole.
* Block-Metadaten und Build-Assets auf Version 1.2.4 vereinheitlicht.

= 1.2.3 =

* Gutenberg-Kraftstofftabs verwenden native WordPress-Core-Buttons und übernehmen Theme-/Site-Editor-Stile vollständig.
* Der konfigurierte Standardkraftstoff erhält den vom Theme definierten Active-State ohne plugin-eigene hardcodierte Button-Stile.
* Beim Wechsel zwischen Diesel, Super E5 und Super E10 wird der aktive Zustand exklusiv auf genau einen Button übertragen.
* Veraltete plugin-eigene Active-Outline für Gutenberg-Tabs entfernt.
* Maus- und Touch-Klicks hinterlassen keinen dauerhaften Fokusrahmen; Tastatur-Fokus und `:focus-visible` bleiben erhalten.

= 1.2.2 =

* Kompakte Shortcode-Ausgabe an das auf afd-chemnitz.de eingesetzte Layout angeglichen.
* Drei interaktive Kraftstoff-Tabs für Diesel, Super E5 und Super E10.
* Farbverläufe übernehmen die WordPress-Presets mit identischen Fallbacks.
* Theme-Typografie wird in der kompakten Shortcode-Ausgabe geerbt.

= 1.2.1 =

* GitHub-Updater liefert auch bei bereits aktueller Version vollständige Update-Metadaten, damit WordPress das Plugin als Auto-Update-fähig erkennt.
* Dadurch wird der Schalter für automatische Plugin-Aktualisierungen in der Plugin-Liste angezeigt.

= 1.2.0 =

* GitHub-Release-Workflow für Tags `v*` mit Build, Tests und WordPress-kompatiblem Release-ZIP.
* GitHub-Updater bevorzugt das veröffentlichte Release-Asset `afd-spritpreise.zip`.
* Kompaktes Gutenberg-Startlayout und separat gestaltbare Kraftstoff-Tabs.

= 1.1.0 =

* Gutenberg-Architektur auf Hauptblock, Kraftstoffumschalter und atomaren Datenwert reduziert.
* Alle festen Präsentationsblöcke entfernt; Core-Blöcke übernehmen Layout, Überschriften, Texte, Gruppen und Spalten.
* `data-value` unterstützt native Gutenberg-Gestaltung inklusive Textausrichtung.
* `allowedBlocks`-Einschränkung entfernt.
* Shortcode-Full-/Compact-Ausgabe bleibt eigenständig erhalten.
