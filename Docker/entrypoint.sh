name="yourname"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/Docker/docker-compose.yml"
mkdir blog
cd blog
mkdir templates
wget -P ./templates/  "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikel.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikel.html"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/index.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/index.css"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/404.jpg"
wget "https://raw.githubusercontent.com/cure53/DOMPurify/main/dist/purify.min.js"
cp index.css ./templates/index.css
sed -i "s/janisplayer/$name/g" ./index.php
sed -i "s/janisplayer/$name/g" ./artikel.html
sed -i "s/janisplayer/$name/g" ./templates/artikel.php

sudo chown -cR www-data:www-data ./
