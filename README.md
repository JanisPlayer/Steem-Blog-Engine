# Steem-Blog-Engine
Eine kleine Möglichkeit, wie man Steem über Javascript und PHP als Datenbank nutzt.

Javascript only Modus mit Steem_API oder Steemworld_API
PHP und Javascript Modus, die Seiten werden mit Javascript zur Hälfte erstellt.
PHP only Modus, die Seiten werden zum Großteil nur mit PHP erstellt.

Was fehlt:
Minimum Zeit für die Aktrealitätsprüfung, welche ja bei 5 Minuten liegen sollte und wahrscheinlich nutze ich dafür auch die Datei.
Prüfung auf Aktualität, beim Aufrufen der Hauptseite und beim Aufrufen eines Artikels, anhand von Zeitstempel der Änderungen und Votes und Comments.
Ein möglicher Temp-Speicher der Bilder, wenn das Urheberrecht es zuslässt die Bilder auf dem Server zu speichern.
Ein Docker Script, welches es ermöglicht die Engine leicht zu eigenen Webseite hinzuzufügen.
Es solle eigentlich nur noch ein Username oder Community-Name eingeben werden in den Docker File.
PHP only Modus mit https://parsedown.org/ .
HTML: Vote-Funktion, Design, Darstellung von Bildern bei manchen Posts(Bug).

Vorschau als Docker:
https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/Docker/entrypoint.sh
Name eintragen und Docker Container starten.
Die Beispielseiten können einfach angepasst und ausgetauscht werden.
Da das Projekt sich noch sehr stark verändern wird, sollte es aber wirklich nur als Demo betrachtet werden.
Ihr könnt dieses Projekt als Vorlage nutzen und natürlich verbessern.
