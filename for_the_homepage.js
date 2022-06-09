  <script>

    //function createArtikelPage();

    document.addEventListener("DOMContentLoaded", () => {
      createArtikelPage_steamworld_api();
    });

    function createArtikelPage() {
      var author = "janisplayer";
      steem.api.getBlogEntries(author, 0, 4, function(err, data) {
        for (var i = 0; i < 74; i++) {
          if (data[i]["author"] == author) {
            createArtikel(author, data[i]["permlink"], i);
          }
        }
      });
    }

    function createArtikel(author, permlink, nummer) {
      const br = document.createElement("br");
      const title = document.createElement("a");
      const date = document.createElement("datum");
      const content_box = document.getElementsByClassName("lastartikellist")[0];

      const artikel = document.createElement("artikel");
      steem.api.getContent(author, permlink, function(err, result) {
        title.innerText = result["title"];
        title.href = "https://heldendesbildschirms.de/artikel/generator.php?artikel=" + permlink;
        //title.innerHTML = title.innerHTML + "<br>";
        date.innerText = result["created"];
      });
      title.style = "font-size: 100%;";
      date.style = "font-size: 50%;";
      content_box.appendChild(artikel);
      artikel.appendChild(title);
      artikel.appendChild(date);
      artikel.appendChild(br);
    }

    function createArtikelPage_steamworld_api() {
      var jf = new XMLHttpRequest();
      jf.open("GET", "https://heldendesbildschirms.de/artikel/templates/PostsByAuthor.json", false);
      jf.send(null)
      const jf_PBA = JSON.parse(jf.response).inhalt;
      const cols = JSON.parse(jf_PBA).result.cols;
      const rows = JSON.parse(jf_PBA).result.rows;

      //jf.open("GET", "https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer", false);
      //jf.send(null)

      //const cols = JSON.parse(jf.response).result.cols;
      //const rows = JSON.parse(jf.response).result.rows;

      console.log(cols.author);

      var author = "janisplayer";
      for (var i = 0; i < 4; i++) {
        if (rows[i][cols.author] == author) {
          createArtikel_steamworld_api(i,rows,cols);
        }
      }
    }

    function createArtikel_steamworld_api(i, rows, cols) {
      const br = document.createElement("br");
      const title = document.createElement("a");
      const date = document.createElement("datum");
      const content_box = document.getElementsByClassName("lastartikellist")[0];

      const artikel = document.createElement("artikel");

      title.innerText = rows[i][cols.title];
      title.href = "https://heldendesbildschirms.de/artikel/generator.php?artikel=" + rows[i][cols.permlink];
        //title.innerHTML = title.innerHTML + "<br>";
      date.innerText = Date(rows[i][cols.created]);

      title.style = "font-size: 100%;";
      date.style = "font-size: 50%;";
      content_box.appendChild(artikel);
      artikel.appendChild(title);
      artikel.appendChild(date);
      artikel.appendChild(br);
    }
  </script>