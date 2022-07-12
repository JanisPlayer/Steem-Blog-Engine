name="yourname"
//Bin grade zu blöd dafür Modus einfach in der index.php einstellen.
modus="modus = 2; //select" //1 Javascirpt_Steem / 2 Javascript PHP / 3 PHP Only
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/Docker/docker-compose.yml"
mkdir blog
cd blog
mkdir templates
wget -P ./templates/  "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikel.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikel.html"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/artikellist.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/index.php"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/index.css"
wget "https://raw.githubusercontent.com/JanisPlayer/Steem-Blog-Engine/main/404.jpg"
wget "https://raw.githubusercontent.com/cure53/DOMPurify/main/dist/purify.min.js"
wget "https://raw.githubusercontent.com/erusev/parsedown/master/Parsedown.php"
cp index.css ./templates/index.css
sed -i "s/janisplayer/$name/g" ./index.php

smodus = "modus = 3; //select"
//sed -i "s/$smodus/$modus/g" ./index.php

sed -i "s/janisplayer/$name/g" ./artikel.html
sed -i "s/janisplayer/$name/g" ./templates/artikel.php

sudo chown -cR www-data:www-data ./
