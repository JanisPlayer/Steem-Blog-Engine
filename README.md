# Steem-Blog-Engine
Eine kleine Möglichkeit, wie man Steem über Javascript und PHP als Datenbank nutzt.  

Javascript only Modus mit Steem_API oder Steemworld_API  
PHP und Javascript Modus, die Seiten werden mit Javascript zur Hälfte erstellt.  
PHP only Modus, die Seiten werden zum Großteil nur mit PHP erstellt.  

* Bugs: IMG Verzeichnis ist noch nicht an die URL oder Indexseite angepasst, wodurch die Bilder in der Schnellansicht nicht laden.

Was fehlt 
* Die Performance muss optimiert werden. Überall, wo zum Beispiel `$data = gen_site_data($permlink, false);` auf false steht, wie beispielsweise in der gen_site Funktion, könnte man direkt `read_api` nutzen, um ein bisschen Zeit zu sparen. Die gen_site_data Funktion wurde etwas verbessert, mit einem lokalen Lese-Modus, um schnell die Performance zu verbessern, ohne alle Funktionen, die es nutzen, aufwendig umzuschreiben.
* Eine Möglichkeit, Sitemaps mit Priorität nach Erstelldatum zu erstellen.
* Eine Möglichkeit RSS Feeds nach Tags zu erstellen.
* HTML: Vote-Funktion, Design, Darstellung von Bildern bei manchen Posts(Bug).  
* PHP only Modus Seitenabschnitte generieren, die dann einfach aneinander geheftet werden und das passiert, wenn der nutzer am ende der Seite angelangt ist.  Für den Javasciprt Modus ist das natürlich dann auch benötigt.
* PHP only Modus Schnellansicht über Get.
* PHP loader PHP die die HTML auf Aktualität prüft oder über Javascript Post. Ein CronJob wäre hier die bessere Lösung, je nach Nutzerzahlen.

Erledigt:
* Ein möglicher Temp-Speicher der Bilder, wenn das Urheberrecht es zuslässt die Bilder auf dem Server zu speichern.(Erledigt Naja)  -Vorschaubilder  -Artikel Bilder können auf dem Server gespeichert werden mit und ohne Kompression in einem img Ordner.
* PHP only Modus mit https://parsedown.org/ . (Erledigt Naja)  
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

Wird es einen Hive Support geben?  
Ja, eine ausführliche Erklärung wie und wann findet ihr hier:  
https://hive.blog/deutsch/@janisplayer/riw3hf  

Wird es eine Post Funktion geben?  
Das weiß ich noch nicht, ich denke, ich werde eine bauen und wenn für beide Plattformen, vielleicht auch eine Multipost Funktion, damit beide Blockchains synchron sind.  
