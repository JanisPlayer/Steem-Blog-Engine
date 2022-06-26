<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="de">

<head>
  <title>Helden des Bildschirms <?=$title?></title>

  <meta charset="utf-8">

  <meta name="description" content="<?=$description?>">

  <meta name="keywords" content="minecraft, rlcraft, gameserver, server, teamspeak, discord, meet, voiceserver, steem, <?=$keywords?> ">

  <meta name="author" content="Janis">

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">

  <link rel="stylesheet" href="/templates/index.css">

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
  <script type="text/javascript" src="/artikel/purify.min.js"></script>
  <!-- <script type="text/javascript" src="https://raw.githubusercontent.com/cure53/DOMPurify/main/dist/purify.min.js"></script> -->
  <script>
    //createArtikelPage();

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/artikel/generator.php");

    xhr.setRequestHeader("Accept", "application/json");
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onload = () => console.log(xhr.responseText);

    let data = `{
      "genallcontent": true,
    }`;

    xhr.send(data);

    document.addEventListener ("DOMContentLoaded", () => {
      //createArtikelContent("janisplayer", "<?=$permlink?>");
      createArtikelContent_steamworld_api("<?=$permlink?>");
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

    function nl2br(str) {
      return str.replace(/(\n)/g, '<br>');
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
          img.src = "https://heldendesbildschirms.dynv6.net/img/Janis_Pickel_Bild.jpg"
          img.style = "width: auto; height: 100%; object-fit: cover;";
        }
        title.innerHTML = "<h1>" + result["title"];
        title.href = "https://steemit.com" + result["url"];
        content_text.innerHTML = DOMPurify.sanitize(nl2br(marked.parse(result["body"])));
        date.innerText = result["created"];
        //button.innerText = "Vote: wird noch erstellt. Ich hatte wegen den Partnern zu wenig Zeit."
        //button.setAttribute = "ArtikelVote(" + author + "," + permlink + ")"
      });
      content_box.appendChild(artikel);
      artikel.appendChild(title);
      artikel.appendChild(content_text);
      steem.api.getActiveVotes(author, permlink, function(err, result) {
      votes.innerText = "Votes: " + result.length;
      });
      artikel.appendChild(votes);
      artikel.appendChild(button);
      artikel.appendChild(date);
    }

    function createArtikelContent_steamworld_api(permlink) {
      /*var jfn = new XMLHttpRequest();
      jfn.open("GET", "/artikel/generator.php?artikel=" + permlink, false);
      jfn.send(null)*/

      var jf = new XMLHttpRequest();
      jf.open("GET", "/artikel/templates/index_"+ permlink + ".json", false);
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
        content_text.innerHTML = DOMPurify.sanitize(nl2br(marked.parse(JSON.parse(jf.response).body)));

        date.innerText = new Date(JSON.parse(jf.response).datum * 1000);
        //button.innerText = "Vote: wird noch erstellt. Ich hatte wegen den Partnern zu wenig Zeit."
        //button.setAttribute = "ArtikelVote(" + author + "," + permlink + ")"

      content_box.insertBefore(artikel,content_read.children[0]);
      artikel.appendChild(title);
      artikel.appendChild(content_text);

      votes.innerText = "Votes: " + JSON.parse(jf.response).upvote_count;

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
  <div class="head">
    <img src="/img/logo.png" alt="Logo von @Zauberah erstellt." style="width:64px;height:51px;">
    <a href="/artikel/">Helden des Bildschirms</a>
  </div>

</head>

<body>
  <content_box>

    <div class="content_read" id ="content_read" >
    </div>

    <div class="content">
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
      <li><a href="mailto:heldendesbildschirms@gmail.com">Kontakt</a></li>
      <li><a href="/datenschutz.html">Datenschutz</a></li>
      <li><a href="/impressum.html">Impressum</a></li>
    </ul>
  </text>
</footer>

</html>
