#!/bin/bash

name="yourname"
modus=3 # 1 Javascirpt_Steem / 2 Javascript PHP / 3 PHP Only

# Lade docker-compose.yml
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/Docker/docker-compose.yml"

# Erstelle Blog-Verzeichnis und Unterverzeichnisse
mkdir -p blog/templates
cd blog

# Lade Dateien herunter
wget -P ./templates/ "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikel.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikel.html"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikellist.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/index.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/index.css"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/404.jpg"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/404.webp"
wget "https://raw.githubusercontent.com/cure53/DOMPurify/main/dist/purify.min.js"
wget "https://raw.githubusercontent.com/erusev/parsedown/master/Parsedown.php"

# Kopiere index.css in das templates Verzeichnis
cp index.css ./templates/index.css

# Ersetze Platzhalter in den Dateien
sed -i "s/janisplayer/$name/g" ./index.php
sed -i "s/modus = 4;/modus = $modus;/g" ./index.php

sed -i "s/janisplayer/$name/g" ./artikel.html
sed -i "s/janisplayer/$name/g" ./templates/artikel.php

# Setze die Berechtigungen
sudo chown -cR www-data:www-data ./
