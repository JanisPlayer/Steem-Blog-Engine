<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="de">

<head>
  <title>Helden des Bildschirms Blog</title>

  <meta charset="utf-8">

  <meta name="description" content="Helden des Bildschirms bietet dir Gameserver und Voiceserver, Minecraft, Mods, TeamSpeak, Discord, Meet.">

  <meta name="keywords" content="minecraft, rlcraft, gameserver, server, teamspeak, discord, meet, voiceserver, steem">

  <meta name="author" content="Janis">

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

  <link rel="stylesheet" href="index.css">

  <link rel="apple-touch-icon" sizes="57x57" href="/icon/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/icon/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/icon/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/icon/apple-/icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/icon/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/icon/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/icon/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/icon/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/icon/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/icon/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/icon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/icon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/icon/favicon-16x16.png">
  <link rel="manifest" href="/icon/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
  <meta name="theme-color" content="#0033CC" />
  <style type="text/css">
  </style>

  <script src="https://cdn.jsdelivr.net/npm/steem/dist/steem.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script type="text/javascript" src="purify.min.js"></script>
  <!-- <script type="text/javascript" src="https://raw.githubusercontent.com/cure53/DOMPurify/main/dist/purify.min.js"></script> -->
  <script>
    //createArtikelPage();

    document.addEventListener("DOMContentLoaded", () => {
      //createArtikelPage_steamworld_api();
    });

    function createArtikelPage() {
      var author = "janisplayer";
      steem.api.getBlogEntries(author, 0, 74, function(err, data) {
        for (var i = 0; i < 74; i++) {
          if (data[i]["author"] == author) {
            createArtikel(author, data[i]["permlink"], i);
          }
        }
      });
    }

    function createArtikel(author, permlink, nummer) {
      const br = document.createElement("br");
      const imgcontainer = document.createElement("imgcontainer");
      const picture = document.createElement("picture");
      const imgcontainera = document.createElement("a");
      const title = document.createElement("a");
      const img = document.createElement("img");
      const button = document.createElement("button");
      const date = document.createElement("datum");
      const content_box = document.getElementsByClassName("content")[0];

      const artikel = document.createElement("artikel");
      steem.api.getContent(author, permlink, function(err, result) {
        if (JSON.parse(result["json_metadata"]).image != null && JSON.parse(result["json_metadata"]).image[0] != undefined) {
          img.src = JSON.parse(result["json_metadata"]).image[0];
          imgcontainera.href = "?artikel=" + permlink;
          img.style = "width: 100%; height: 180px; object-fit: cover;";
        } else {
          img.src = "404.jpg";
          imgcontainera.href = "?artikel=" + permlink;
          img.style = "width: 100%; height: 180px; object-fit: cover;";
        }
        title.innerText = result["title"];
        title.href = "?artikel=" + permlink;
        date.innerText = result["created"];
        button.innerText = "Beitrag lesen (Schnellansicht)"
        button.onclick = function() {
          createArtikelContent(author, permlink);
          location.href="#content_read";
        };
      });
      content_box.appendChild(artikel);
      artikel.appendChild(imgcontainera);
      imgcontainer.style = "width:100%; height:180px; overflow: hidden; display: inline-block; position: relative;";
      imgcontainera.appendChild(imgcontainer);
      imgcontainer.appendChild(picture);
      if (img.src != null) {
        picture.appendChild(img);
        imgcontainer.appendChild(br);
      }
      artikel.appendChild(title);
      artikel.appendChild(button);
      artikel.appendChild(date);
    }

    function createArtikelContent(author, permlink) {
      const br = document.createElement("br");
      const imgcontainer = document.createElement("imgcontainer");
      const picture = document.createElement("picture");
      const imgcontainera = document.createElement("a");
      const title = document.createElement("a");
      const content_text = document.createElement("content_text");
      const img = document.createElement("img");
      const button = document.createElement("button");
      const votes = document.createElement("votes");
      const date = document.createElement("datum");
      const content_box = document.getElementsByClassName("content_read")[0];

      const artikel = document.createElement("artikel");
      steem.api.getContent(author, permlink, function(err, result) {
        if (JSON.parse(result["json_metadata"]).image != null && JSON.parse(result["json_metadata"]).image[0] != undefined) {
          img.src = JSON.parse(result["json_metadata"]).image[0];
          img.style = "width: auto; height: 100%; object-fit: cover;";
        } else {
          img.src = "404.jpg";
          img.style = "width: auto; height: 100%; object-fit: cover;";
        }
        title.innerHTML = "<h1>" + result["title"];
        title.href = "https://steemit.com" + result["url"];
        //content_text.innerHTML = DOMPurify.sanitize((marked.parse(result["body"])));
        marked.setOptions({
          breaks: true,
        });
        content_text.innerHTML = DOMPurify.sanitize(marked.parse(result["body"]));
        date.innerText = result["created"];
        //button.innerText = "Vote: wird noch erstellt. Ich hatte wegen den Partnern zu wenig Zeit."
        //button.setAttribute = "ArtikelVote(" + author + "," + permlink + ")"
      });
      content_box.insertBefore(artikel,content_read.children[0]);
      artikel.appendChild(title);
      artikel.appendChild(content_text);
      steem.api.getActiveVotes(author, permlink, function(err, result) {
      votes.innerText = "Votes: " + result.length;
      });
      artikel.appendChild(votes);
      artikel.appendChild(button);
      artikel.appendChild(date);
    }

    function oldcreateArtikel(author, permlink) {
      const br = document.createElement("br");
      const title = document.createElement("a");
      const img = document.createElement("img");
      const date = document.createElement("datum");
      const content_box = document.getElementsByClassName("content")[0];

      const artikel = document.createElement("artikel");
      steem.api.getContent(author, permlink, function(err, result) {
        if (JSON.parse(result["json_metadata"]).image != null) {
          img.src = JSON.parse(result["json_metadata"]).image[0];
          img.style = "width:100px;height:auto;";
        }
        title.innerText = result["title"];
        title.href = "https://steemit.com" + result["url"];
        date.innerText = result["created"];
      });
      artikel.innerText;
      content_box.appendChild(artikel);
      artikel.appendChild(img);
      artikel.appendChild(br);
      artikel.appendChild(title);
      artikel.appendChild(date);
    }

    function createArtikelPage_steamworld_api() {
      var jf = new XMLHttpRequest();
      jf.open("GET", "templates/PostsByAuthor.json", false);
      jf.send(null)
      const jf_PBA = JSON.parse(jf.response).inhalt;
      const cols = JSON.parse(jf_PBA).result.cols;
      const rows = JSON.parse(jf_PBA).result.rows;

      console.log(cols.author);

      var author = "janisplayer";
      for (var i = 0; i < rows.length; i++) {
        if (rows[i][cols.author] == author) {
          createArtikel_steamworld_api(i,rows,cols);
        }
      }
    }

    function createArtikel_steamworld_api(i, rows, cols) {
      const br = document.createElement("br");
      const imgcontainer = document.createElement("imgcontainer");
      const picture = document.createElement("picture");
      const imgcontainera = document.createElement("a");
      const title = document.createElement("a");
      const img = document.createElement("img");
      const button = document.createElement("button");
      const date = document.createElement("datum");
      const content_box = document.getElementsByClassName("content")[0];

      const artikel = document.createElement("artikel");

        if (JSON.parse(rows[i][cols.json_metadata]).image != null && JSON.parse(rows[i][cols.json_metadata]).image[0] != undefined) {
          img.src = JSON.parse(rows[i][cols.json_metadata]).image[0];
          imgcontainera.href = "?artikel=" + rows[i][cols.permlink];
          img.style = "max-width:100%; height:auto; display: inline-block; vertical-align: middle;";
          img.style = "width: 100%; height: 180px; object-fit: cover;";
        } else {
          img.src = "404.jpg";
          imgcontainera.href = "?artikel=" + rows[i][cols.permlink];
          img.style = "width: 100%; height: 180px; object-fit: cover;";
        }
        title.innerText = rows[i][cols.title];
        title.href = "?artikel=" + rows[i][cols.permlink];
        date.innerText = new Date(rows[i][cols.created] * 1000);
        button.innerText = "Beitrag lesen (Schnellansicht)"
        button.onclick = function() {
          createArtikelContent_steamworld_api(rows[i][cols.permlink]);
          location.href="#content_read";
        };

      content_box.appendChild(artikel);
      artikel.appendChild(imgcontainera);
      imgcontainer.style = "width:100%; height:180px; overflow: hidden; display: inline-block; position: relative;";
      imgcontainera.appendChild(imgcontainer);
      imgcontainer.appendChild(picture);
      if (img.src != null) {
        picture.appendChild(img);
        imgcontainer.appendChild(br);
      }
      artikel.appendChild(title);
      artikel.appendChild(button);
      artikel.appendChild(date);
    }

    function createArtikelContent_steamworld_api(permlink) {

      var jf = new XMLHttpRequest();
      jf.open("GET", "templates/index_"+ permlink + ".json", false);
      jf.send(null)

      const br = document.createElement("br");
      const imgcontainer = document.createElement("imgcontainer");
      const picture = document.createElement("picture");
      const imgcontainera = document.createElement("a");
      const title = document.createElement("a");
      const content_text = document.createElement("content_text");
      const img = document.createElement("img");
      const button = document.createElement("button");
      const votes = document.createElement("votes");
      const date = document.createElement("datum");
      const content_box = document.getElementsByClassName("content_read")[0];

      const artikel = document.createElement("artikel");

        title.innerHTML = "<h1>" + JSON.parse(jf.response).title;
        title.href = "https://steemit.com" + "/" + JSON.parse(jf.response).category + "/@" + JSON.parse(jf.response).author + "/" +permlink;
        marked.setOptions({
          breaks: true,
        });
        content_text.innerHTML = DOMPurify.sanitize(marked.parse(JSON.parse(jf.response).body));

        date.innerText = Date(JSON.parse(jf.response).datum);
        //button.innerText = "Vote: wird noch erstellt. Ich hatte wegen den Partnern zu wenig Zeit."
        //button.setAttribute = "ArtikelVote(" + author + "," + permlink + ")"

      content_box.insertBefore(artikel,content_read.children[0]);
      artikel.appendChild(title);
      artikel.appendChild(content_text);

      votes.innerText = "Votes: up: " + JSON.parse(jf.response).upvote_count + " down: " + JSON.parse(jf.response).downvote_count;

      artikel.appendChild(votes);
      artikel.appendChild(button);
      artikel.appendChild(date);
    }

  </script>

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-176121451-1"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-176121451-1', {
      'anonymize_ip': true
    });
  </script>

  <!-- Google Tag Manager -->
  <script>
    (function(w, d, s, l, i) {
      w[l] = w[l] || [];
      w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
      });
      var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
      j.async = true;
      j.src =
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
      f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-K73ZCBF');
  </script>
  <!-- End Google Tag Manager -->

  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5350651163680266" crossorigin="anonymous"></script>

  <script>
    addEventListener('DOMContentLoaded', (event) => {
      document.getElementById('suche_summit').addEventListener('click', suche);

      document.getElementById('suche_input').onkeydown = function(e) {
        if (e.keyCode == 13) {
          suche();
        }
      };
    });

    function suche() {
      var suche_text = document.getElementById('suche_input').value
      document.getElementById('suche_input').value = "";
      document.location = ("https://cse.google.com/cse?cx=7718be5d5ddc85d34&q=" + suche_text);
    }
  </script>

</head>

<body>

  <div class="head">
    <img src="/img/logo.png" alt="Logo von @Zauberah erstellt." style="width:64px;height:51px;">
    <a href="/">Helden des Bildschirms</a>
  </div>

  <div class="over_nav">
    <a href="/">Home</a>

    <div class="suchen" style="float:right">
      <input type="text" value="" id="suche_input" class="suche"><button id="suche_summit" class="suche">suchen</button>
    </div>
  </div>

  <content_box>
    <div class="content_read" id ="content_read" >
    </div>

    <div class="content">
      <?=$body_parsedown?>
    </div>

    <div id="werbung_google">
      <!-- Adblock -->
      <ins class="adsbygoogle" style="display:block; text-align:center; margin-top: 1%;" data-ad-client="ca-pub-5350651163680266" data-ad-slot="9833970873" data-ad-format="auto" data-full-width-responsive="true"></ins>
      <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
      </script>
    </div>

  </content_box>
</body>

<footer>
  <text>
    <ul>
      <!-- <li>&copy; 2019 Helden des Bildschirms</li> -->
      <li><a href="mailto:support@heldendesbildschirms.de">Kontakt</a></li>
      <li><a href="/datenschutz.html">Datenschutz</a></li>
      <li><a href="/impressum.html">Impressum</a></li>
    </ul>
  </text>
</footer>

</html>
