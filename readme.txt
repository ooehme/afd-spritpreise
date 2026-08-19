=== AfD Spritpreise ===
Contributors: ooehme
Tags: kraftstoffpreise, tankstellen, median, shortcode, gutenberg
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Regionale Median-Kraftstoffpreise und ein transparent konfigurierbares Steuerszenario als frei gestaltbarer Gutenberg-Datenbaukasten oder Shortcode.

== Description ==

AfD Spritpreise lädt aktuelle Tankstellendaten serverseitig von TankPuls, berechnet den Median für Diesel, Super E5 und Super E10 und stellt daraus dynamische Datenwerte bereit.

Der Gutenberg-Baukasten verwendet nur drei Plugin-Blöcke:

* `afd-spritpreise/fuel-price` als Datenkontext
* `afd-spritpreise/fuel-tabs` als Kraftstoffumschalter
* `afd-spritpreise/data-value` für einen einzelnen dynamischen Wert

Layout und Präsentation erfolgen mit normalen Gutenberg-Core-Blöcken wie Gruppe, Spalten, Überschrift und Absatz. Es gibt keine Plugin-eigenen Price-Board-, Facts-, Header-, Metric-, Station-, Forderungs- oder Methodik-Container mehr.

Der Datenwert kann frei unterhalb des Hauptblocks verschachtelt und mit nativen Gutenberg-Einstellungen für Farben, Typografie, Textausrichtung, Abstände, Rahmen, Schatten und Größen gestaltet werden.

Der Shortcode `[afd_spritpreise]` bleibt als eigenständige Full-/Compact-Ausgabe erhalten.

== Installation ==

1. Plugin-ZIP unter Plugins → Installieren hochladen.
2. Plugin aktivieren.
3. Unter Einstellungen → AfD Spritpreise das Standardgebiet und die Rechenwerte konfigurieren.
4. Block einfügen oder `[afd_spritpreise]` verwenden.

== Frequently Asked Questions ==

= Kann ich normale Gutenberg-Gruppen und Spalten verwenden? =

Ja. `fuel-tabs` und `data-value` benötigen nur den Hauptblock `afd-spritpreise/fuel-price` als Vorfahren. Beliebige Core-Blöcke dürfen dazwischen liegen.

= Kann ich Preis, Beschriftung und Hinweis getrennt gestalten? =

Ja. Beschriftungen und Layout werden mit normalen Core-Blöcken aufgebaut. Jeder dynamische Wert ist ein eigener `data-value`-Block und kann separat gestaltet werden.

= Ruft das Frontend Photon auf? =

Nein. Photon wird nur bei der Gebietskonfiguration im Backend und Editor aufgerufen. Das Frontend nutzt die gespeicherte Bounding Box.

= Welche Shortcode-Parameter gibt es? =

`fuel`, `display`, `min_lat`, `min_lng`, `max_lat`, `max_lng`, `area` und optionale `show_*`-Schalter.

== Privacy ==

Das Plugin speichert keine personenbezogenen Nutzerdaten. Es sendet serverseitige Preisabfragen an TankPuls und nur bei aktiver Backend-/Editor-Suche den eingegebenen Suchtext an Photon.

== Changelog ==

= 1.1.0 =

* Gutenberg-Architektur auf Hauptblock, Kraftstoffumschalter und atomaren Datenwert reduziert.
* Alle festen Präsentationsblöcke entfernt; Core-Blöcke übernehmen Layout, Überschriften, Texte, Gruppen und Spalten.
* `data-value` unterstützt native Gutenberg-Gestaltung inklusive Textausrichtung.
* `allowedBlocks`-Einschränkung entfernt.
* Shortcode-Full-/Compact-Ausgabe bleibt eigenständig erhalten.
