# availability_adler

[![Coverage Status](https://coveralls.io/repos/github/ProjektAdLer/MoodlePluginAvailability/badge.svg?branch=main)](https://coveralls.io/github/ProjektAdLer/MoodlePluginAvailability?branch=main)

Dieses Plugin implementiert eine _availability condition_ für die Adler-Raum-Logik (`requiredSpacesToEnter`, bspw `(5)v((7)^(4))`). 

Ist das Haupt-Plugin nicht installiert wird die durch dieses Plugin implementierte _availability condition_ immer `true` zurück geben

## Kompatibilität
Die minimal notwendige Moodle Version ist auf 4.1.0 gesetzt, daher wird die Installation auf älteren Versionen nicht funktionieren.
Prinzipiell sollte dieses Plugin auch auf älteren Versionen funktionieren, dies wird aber nicht getestet und spätestens bei der
Nutzung weiterer AdLer Plugins wird es zu Problemen kommen, da diese Features nutzen, die erst in neueren Moodle Versionen verfügbar sind.

Folgende Versionen werden unterstützt (mit mariadb und postresql getestet):

| Moodle Branch           | PHP Version |
|-------------------------|-------------|
| MOODLE_401_STABLE (LTS) | 8.1         |
| MOODLE_402_STABLE       | 8.1         |

## Installation
1. Dieses Plugin benötigt das Plugin `local_adler` als Abhängigkeit. Beide müssen zeitgleich installiert werden (= vor dem upgrade in die Moodle-Installation entpackt sein). Installation siehe `local_adler`
2. Plugin in moodle in den Ordner `availability/condition` entpacken (bspw` moodle/availability/condition/adler/version.php` muss es geben)
3. Moodle upgrade ausführen

## Dev Setup / Tests
Dieses Plugin nutzt Mockery für Tests.
Die composer.json von Moodle enthält Mockery nicht, daher enthält das Plugin eine eigene composer.json.
Diese ist (derzeit) nur für die Entwicklung relevant, sie enthält keine Dependencies die für die normale Nutzung notwendig sind.
Um die dev-dependencies zu installieren muss `composer install` im Plugin-Ordner ausgeführt werden.

## Plugin Dokumentation

### Parser für boolsche Algebra
Das Statement wird mittels einer rekursiven Methode ausgewertet. 
Zustände (true/false) werden temporär als 't'/'f' in den String geschrieben.
Aufgrund der geringen Anzahl an Operatoren (`v`, `^`, `!` und Klammern) funktioniert dieser Ansatz recht gut.
Bei Hinzufügen weiterer Operatoren dürfte dieser Ansatz schnell an seine Grenzen stoßen, ein komplexerer Parser mit Baumstruktur wäre dann sinnvoller.
Bei dem gegebenen Umfang ist die zusätzliche Komplexität aber nicht notwendig.
