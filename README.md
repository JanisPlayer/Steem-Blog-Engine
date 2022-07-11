# Steem-Blog-Engine
Eine kleine Möglichkeit, wie man Steem über Javascript und PHP als Datenbank nutzt.  

Javascript only Modus mit Steem_API oder Steemworld_API  
PHP und Javascript Modus, die Seiten werden mit Javascript zur Hälfte erstellt.  
PHP only Modus, die Seiten werden zum Großteil nur mit PHP erstellt.  

Was fehlt 
* Ein möglicher Temp-Speicher der Bilder, wenn das Urheberrecht es zuslässt die Bilder auf dem Server zu speichern.  
* PHP only Modus mit https://parsedown.org/ . (Erledigt Naja)  
* HTML: Vote-Funktion, Design, Darstellung von Bildern bei manchen Posts(Bug).  
* PHP only Modus Seitenabschnitte generieren, die dann einfach aneinander geheftet werden und das passiert, wenn der nutzer am ende der Seite angelangt ist.  Für den Javasciprt Modus ist das natürlich dann auch benötigt.

Erledigt:
* Ein Docker Script, welches es ermöglicht die Engine leicht zu eigenen Webseite hinzuzufügen. (Erledigt)  
* Es solle eigentlich nur noch ein Username oder Community-Name eingeben werden in den Docker File. (Erledigt)  
* Minimum Zeit für die Aktrealitätsprüfung, welche ja bei 5 Minuten liegen sollte und wahrscheinlich nutze ich dafür auch die Datei. (Erledigt)  
* Prüfung auf Aktualität, beim Aufrufen der Hauptseite und beim Aufrufen eines Artikels, anhand von Zeitstempel der Änderungen und Votes und Comments. (Erledigt) 

Vorschau als Docker:
https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/Docker/entrypoint.sh  
Name eintragen, Modus wählen und Docker Container starten.  
Die Beispielseiten können einfach angepasst und ausgetauscht werden.  
Da das Projekt sich noch sehr stark verändern wird, sollte es aber wirklich nur als Demo betrachtet werden.  
Ihr könnt dieses Projekt als Vorlage nutzen und natürlich verbessern.  
