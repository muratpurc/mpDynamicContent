# CONTENIDO CMS Modul mpDynamicContent zum dynamischen Einbinden von beliebigen Content-Typen

## Beschreibung

Das Modul mpDynamicContent erlaubt das Einbinden von beliebigen Content-Typen
(z. B. `CMS_HTMLHEAD`, `CMS_HTML`, `CMS_IMGEDITOR`, usw.) in einen Container.

Damit lassen sich dynamisch mehrere Inhalte basierend auf Content-Typen in einem
Layout ausgeben. Die Konfiguration kann direkt über die Editor-Ansicht des
Artikels vorgenommen werden.

Standardmäßig werden alle vorhandenen Content-Typen unterstützt, Ausnahmen gibt
es für `CMS_IMG`, `CMS_IMGDESCR`, `CMS_LINK`, `CMS_LINKTARGET`, `CMS_LINKDESCR`. Dafür
gibt es die neuen Content-Typen `CMS_IMGEDITOR` und `CMS_LINKEDITOR`, die die
bekannte Funktionalität übernehmen.

Es ist auch möglich, zu jedem Content-Typ ein Template auszuwählen.

----

## Voraussetzungen

- CONTENIDO >= 4.10.*
- PHP >= 7.1 und PHP < 8.2
- CONTENIDO Plugin "Mp Dev Tools"

Das benötigte Plugin (Package "mp_dev_tools.zip") von der [GitHub-Seite](https://github.com/muratpurc/CONTENIDO-plugin-mp_dev_tools/releases)
herunterladen und in CONTENIDO installieren.

----

## Installation/Verwendung

Die im Modulpackage enthaltenen Dateien/Sourcen sind wie im Folgenden beschrieben
zu installieren.
Die Pfade zu den Sourcen (CSS, JS und Templates) können von Projekt zu Projekt
unterschiedlich sein und sind bei Bedarf anzupassen.
Bei der Installationsbeschreibung wird davon ausgegangen, dass CONTENIDO in das
DocumentRoot-Verzeichnis eines Webservers installiert wurde und das
Mandantenverzeichnis `cms` ist.

### Modulinstallation:

Den Modulordner `mp_dynamic_content` samt aller Inhalte in das Modulverzeichnis
des Mandanten `cms/data/modules` kopieren.
Danach sollte man im Backend die Funktion `Module synchronisieren` unter
`Style -> Module` ausführen.


### Einrichten des Moduls:

Dieses Modul in einer Artikelvorlage einrichten.

Einen Artikel erstellen, welches auf die Vorlage basiert.

In der Editoransicht des Artikels die gewünschten Optionen setzen.


#### Mandanten- oder Systemeinstellungen:

Mit folgender Mandanten- oder Systemeinstellung kann man die unterstützten
Content-Typen konfigurieren. Mehrere Werte sind mit Komma zu trennen:
```
Typ:  module_mp_dynamic_content
Name: supported_content_types
Wert: CMS_HEAD,CMS_HTML,CMS_HTMLHEAD,CMS_IMGEDITOR,CMS_LINKEDITOR
```

Ist die Einstellung nicht gesetzt, so wird dafür per default folgender Wert verwendet:
```
CMS_HEAD,CMS_HTML,CMS_HTMLHEAD,CMS_IMGEDITOR,CMS_LINKEDITOR
```

#### Basiseinstellungen (im Popup Dialog):

- Container-Nummer: Die Container-Nummer die zum Erstellen der Inhalte verwendet
  werden soll. Die eingegebene Nummer sollte nicht im Layout vergeben sein und
  es sollte ausreichend Puffer zur nächsten im Layout verwendeten Container-Nummer
  sein, da das Modul die angegebene Container-Nummer für jeden konfigurierten
  Content-Typen hochzählt, z. B. Container-Nummer = 500, 5 Content-Typen,
  benötigter Container-Nummer Bereich = 500 - 505.

#### Content-Typ Einstellungen (im Popup Dialog):

Jeder Content-Typ Eintrag enthält 4 konfigurierbare Felder.

- `Beschreibung`: Angabe der Beschreibung zum Content-Typ, wenn angegeben, wird
  es im Backend in der Editor-Ansicht als label-Element dargestellt

- `Content-Typ`: Auswahl des zu verwendenden Content-Typen

- `Template`: Auswahl des Templates, in dem der Inhalt des Content-Typ gerendert
  werden soll. Mitgeliefert werden 5 verschiedene Templates.
  Damit ein Content-Typ Template erkannt und korrekt im  Auswahlfeld erscheint,
  muss es folgende Kriterien erfüllen.
  - Das Template muss im Modulverzeichnis im Ordner template liegen.
  - Der Dateiname des Templates muss mit dem Präfix "type." beginnen.
  - Die Erste Zeile des Templates sollte eine das Template beschreibende kurze
    Kommentarzeile sein.

- `Zusätzlicher Text`: Dieses benutzerdefiniertes Feld kann für den eigenen Bedarf
  verwendet werden, z. B. für Ausgabe als reiner Text oder auch als Wert für ein
  class-Attribut. Im Template hat man Zugriff auf den Wert mit "$content.userdefined".

- `Aktionen`:
  - `Online Status`: Content-Typ lassen sich online/offline stellen, dabei werde die Elemente
    mit dem Status offline bei der Ausgabe ausgelassen. Diese Inhalte können aber
    immer noch über die Suche im Frontend gefunden werden.

----

## Changelog

**2019-11-12 mpDynamicContent 0.1.3 (für CONTENIDO 4.9.x - 4.10.x)**
- bugfix: Mehrfachen Aufruf des Con.Loader callbacks unterbinden
- change: Verwendung von unterstützten default Content-Typen, wenn Einstellung nicht gesetzt

**2013-12-05 mpDynamicContent 0.1.2 (für CONTENIDO 4.9.x)**
- bugfix: Besseres Handling für das Laden von jQuery UI im Backend
- bugfix: Anzeige des ausgewählten Bildes im Backend in der Editor-Ansicht
- change: Laden und Ausgabe der benötigten Styles nur einmal pro Seite
- new:    Sortierung für Templates

**2013-12-02 mpDynamicContent 0.1 (für CONTENIDO 4.9.x)**
- Erste Veröffentlichung des mpDynamicContent Moduls

----

## mpDynamicContent Modul Links

- [GitHub-Seite](https://github.com/muratpurc/mpDynamicContent)

- [CONTENIDO Forum unter "CONTENIDO 4.10 -> Module und Plugins"](https://forum.contenido.org/viewtopic.php?t=43770)

- [CONTENIDO Forum unter "CONTENIDO 4.9 -> Module und Plugins"](http://forum.contenido.org/viewtopic.php?t=34753)
