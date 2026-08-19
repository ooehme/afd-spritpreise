=== AfD Spritpreise ===
Contributors: ooehme
Tags: kraftstoffpreise, tankstellen, median, shortcode, gutenberg
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Regionale Median-Kraftstoffpreise und ein transparent konfigurierbares Steuerszenario als dynamischer Block oder Shortcode.

== Description ==

AfD Spritpreise lädt aktuelle Tankstellendaten serverseitig von TankPuls, berechnet den Median für Diesel, Super E5 und Super E10, zeigt die günstigste aktive Tankstelle und stellt einen hypothetischen Preis auf Basis editierbarer Steuer- und CO₂-Werte dar.

Features:

* Photon-Orts-/PLZ-Suche im Backend und Gutenberg-Editor
* eigene Bounding Box je Block
* dynamischer Gutenberg-Block `afd-spritpreise/fuel-price`
* Shortcode `[afd_spritpreise]`
* getrennte Full- und Compact-Ausgabe
* 15-Minuten-Cache, Refresh-Lock und Stale-if-error
* vollständige Quellen- und Methodikdarstellung
* automatische Updates über öffentliche GitHub-Releases
* keine personenbezogenen Nutzerdaten und keine Frontend-Standortabfrage

== Installation ==

1. Plugin-ZIP unter Plugins → Installieren hochladen.
2. Plugin aktivieren.
3. Unter Einstellungen → AfD Spritpreise das Standardgebiet und die Rechenwerte konfigurieren.
4. Block einfügen oder `[afd_spritpreise]` verwenden.

== Frequently Asked Questions ==

= Ruft das Frontend Photon auf? =

Nein. Photon wird nur bei der Gebietskonfiguration im Backend und Editor aufgerufen. Das Frontend nutzt die gespeicherte Bounding Box.

= Warum sehe ich zuletzt verfügbare Preise? =

Bei einem API-Fehler verwendet das Plugin den letzten erfolgreichen Datensatz als Stale-Fallback. So vervielfachen Seitenaufrufe weder Last noch Ausfälle.

= Welche Shortcode-Parameter gibt es? =

`fuel`, `display`, `min_lat`, `min_lng`, `max_lat`, `max_lng`, `area` und optionale `show_*`-Schalter. Vollständige Beispiele stehen in README.md.

= Gibt es eine Karte? =

Nein. Die Gebietsauswahl erfolgt projektgemäß direkt über Photon; die technische Bounding Box bleibt in den Einstellungen prüfbar.

== Privacy ==

Das Plugin speichert keine personenbezogenen Nutzerdaten. Es sendet serverseitige Preisabfragen an TankPuls und nur bei aktiver Backend-/Editor-Suche den eingegebenen Suchtext an Photon. GitHub wird im Backend höchstens alle zwölf Stunden auf ein neues Release geprüft.

== Changelog ==

= 1.0.0 =

* Erstveröffentlichung mit dynamischem Block, Shortcode, Photon-Gebietssuche, TankPuls-Cache, Szenariorechnung, Full-/Compact-Renderer und GitHub-Updater.
