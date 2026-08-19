=== AfD Spritpreise ===
Contributors: ooehme
Tags: kraftstoffpreise, tankstellen, median, shortcode, gutenberg
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Regionale Median-Kraftstoffpreise und ein transparent konfigurierbares Steuerszenario als frei gestaltbarer Gutenberg-Baukasten oder Shortcode.

== Description ==

AfD Spritpreise lädt aktuelle Tankstellendaten serverseitig von TankPuls, berechnet den Median für Diesel, Super E5 und Super E10, zeigt die günstigste aktive Tankstelle und stellt ein konfigurierbares Preis-Szenario dar.

Der Gutenberg-Baukasten besteht aus Datenblöcken für Überschrift, Kraftstoffauswahl, Preisfeld, 50-Liter-Ersparnis, günstigste Tankstelle, Forderungen und Methodik. Diese Blöcke können frei unterhalb des Hauptblocks verschachtelt werden. Für Layout und Gestaltung können normale WordPress-Blöcke wie Gruppe, Zeile, Stapel, Spalten und Spalte verwendet werden.

Features:

* Photon-Orts-/PLZ-Suche im Backend und Gutenberg-Editor
* eigene Bounding Box je Hauptblock
* frei verschachtelbare Gutenberg-Datenblöcke
* native Gutenberg-Designoptionen für Farben, Verläufe, Typografie, Abstände, Rahmen, Schatten, Dimensionen und weitere Block-Supports
* Shortcode `[afd_spritpreise]` mit eigenständiger Full-/Compact-Ausgabe
* 15-Minuten-Cache, Refresh-Lock und Stale-if-error
* Quellen- und Methodikdarstellung
* automatische Updates über öffentliche GitHub-Releases
* keine personenbezogenen Nutzerdaten und keine Frontend-Standortabfrage

== Installation ==

1. Plugin-ZIP unter Plugins → Installieren hochladen.
2. Plugin aktivieren.
3. Unter Einstellungen → AfD Spritpreise das Standardgebiet und die Rechenwerte konfigurieren.
4. Den Block AfD Spritpreise einfügen und die Datenblöcke mit Gutenberg-Core-Blöcken frei anordnen oder `[afd_spritpreise]` verwenden.

== Frequently Asked Questions ==

= Kann ich Preisfelder in Gruppen und Spalten verschachteln? =

Ja. Preisfelder und die übrigen Datenblöcke benötigen nur den Hauptblock AfD Spritpreise als Vorfahren. Dazwischen können normale Gutenberg-Blöcke verwendet werden.

= Ruft das Frontend Photon auf? =

Nein. Photon wird nur bei der Gebietskonfiguration im Backend und Editor aufgerufen. Das Frontend nutzt die gespeicherte Bounding Box.

= Warum sehe ich zuletzt verfügbare Preise? =

Bei einem API-Fehler verwendet das Plugin den letzten erfolgreichen Datensatz als Stale-Fallback.

= Welche Shortcode-Parameter gibt es? =

`fuel`, `display`, `min_lat`, `min_lng`, `max_lat`, `max_lng`, `area` und optionale `show_*`-Schalter. Die Full-/Compact-Ausgabe gehört ausschließlich zum Shortcode-Renderer und ist unabhängig vom frei gestaltbaren Gutenberg-Baukasten.

= Gibt es eine Karte? =

Nein. Die Gebietsauswahl erfolgt über Photon; die Bounding Box bleibt in den Einstellungen prüfbar.

== Privacy ==

Das Plugin speichert keine personenbezogenen Nutzerdaten. Es sendet serverseitige Preisabfragen an TankPuls und nur bei aktiver Backend-/Editor-Suche den eingegebenen Suchtext an Photon. GitHub wird im Backend höchstens alle zwölf Stunden auf ein neues Release geprüft.

== Changelog ==

= 1.1.0 =

* Frei verschachtelbarer Gutenberg-Baukasten mit Datenblöcken für Überschrift, Tabs, Preisfelder, Ersparnis, Tankstelle, Forderungen und Methodik.
* Preisfelder sind nicht an einen Plugin-eigenen Container gebunden und können in Gruppe, Spalten und anderen Core-Blöcken liegen.
* Native Gutenberg-Unterstützung für Farben, Hintergründe, Verläufe, Typografie, Abstände, Rahmen, Schatten und Dimensionen erweitert.
* Obsolete Plugin-Container Price Board und Zusatzinformationen entfernt.
* Nicht benötigte Kompatibilitätslogik für frühere Blockstrukturen entfernt.

= 1.0.0 =

* Entwicklungsstand mit dynamischem Block, Shortcode, Photon-Gebietssuche, TankPuls-Cache, Szenariorechnung, Full-/Compact-Shortcode-Renderer und GitHub-Updater.
