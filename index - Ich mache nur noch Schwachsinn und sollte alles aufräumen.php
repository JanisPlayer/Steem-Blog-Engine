<?php
error_reporting(-1);
ini_set('display_errors', 'On');

function render(string $pathtemplate, string $pathtsite, string $filename, array $data){
  global $modus;
    if (!file_exists($pathtsite)) {
        if (!mkdir($pathtsite, 0700, true)) {
          die('Erstellung der Verzeichnisse schlug fehl...');
        }
    }

    if(!is_file($pathtemplate)){
        trigger_error('Datei "'.$pathtemplate.'" wurde nicht gefunden');
        return null;
    }

    //Wird noch wo anderes hingemacht.

  if ($modus == 3) {
    require_once 'Parsedown.php';
    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);
    $Parsedown->setMarkupEscaped(true);
    $data['body_parsedown'] = "<artikel>"
    .'<a href="'."https://steemit.com/".$data['category']."/@".$data['author']."/".$data['permlink'].'"><h1>'.$data['title']."</h1></a>"
    .$Parsedown->text($data['body'])
    ."<votes>Votes: up: ".$data['upvote_count'] ." down: ". +$data['downvote_count']."</votes>"
    ."<datum>".$datum = date("d.m.Y H:i",$data['datum'])."</datum>"
    ."</artikel>";
    $data['javascirpt'] = "//";
    $data['javascirpt_steemit'] = "//";
  } elseif ($modus == 2) {
    $data['javascirpt_steemworld'] = "";
    $data['javascirpt_steemit'] = "//";
  } elseif ($modus == 1) {
    $data['javascirpt_steemit'] = "";
    $data['javascirpt_steemworld'] = "//";
  }

    extract($data,EXTR_SKIP);

    ob_start();
    require $pathtemplate;
    $content = ob_get_clean();

    $handle = fopen ("$pathtsite".$filename, "w");
    fwrite ($handle, $content);
    fclose ($handle);

    echo "Seite erstellt unter".$pathtsite.$filename;
}

function escape(string $data){
    return htmlspecialchars($data,ENT_QUOTES,'UTF-8');
}


function file_check(string $filename, int $sec) {
  global $pathtemplate;
  if (file_exists($pathtemplate.$filename)) {  //Datei vorhanden?
    if (time() - filemtime($pathtemplate.$filename)  >= $sec) { //Sind die 5 Minuten abgelaufen?
      return 1;
    }
  } else {
    return 1;
  }
  return 0;
}

function render_content_images(string $body, $json_metadata, string $savepath, string $permlink, $compress) { //array $compress aber mit geht das unsaubere unten nicht.
  global $pathtsite;
  global $pathtsitebugfix; //Wird natürlich noch anders gelöst.
  $image = json_decode($json_metadata, true)["image"];
  $img_url = $image[0]; //Uncaught TypeError: Cannot access offset of type string on string 1h Stunde RIP nur weil ich Idiot $jsond genutzt habe.
  if (isset($image) && isset($img_url)) {
    for ($i=0; $i < count($image); $i++) { //json_decode($json_metadata, true)["image"] könnte man zwischenspeichern in Variable.
        $img_url = $image[$i];
        //if (isset(json_decode(read_api($i,"json_metadata", 0), true)["image"]) && isset(json_decode(read_api($i,"json_metadata", 0), true)["image"][0])) {
        //Erstellt Vorschaubild zuerst nur für den PHP only Modus. Sollte vielleicht noch eine Funktion werden. https://stackoverflow.com/questions/10870129/compress-jpeg-on-server-with-php
        $img_src = $permlink.'/img/';
        //$filename = 'preview'.'.jpg'; bug wieso auch immer.

        if (!file_exists($pathtsite.$permlink.'/')) {
          mkdir($pathtsite.$permlink.'/'); //Ja ich sollte das auch anderes lösen. :D
        }

        if (!file_exists($img_src)) {
          mkdir($img_src);
        }

        $img_src = $img_src.$i.'.jpg';
        echo " ". $img_src;
          if (!file_exists($img_src)) {
            try { //Geht warscheinlich besser, aber mir fällt nichts besseres ein.
              ini_set("default_socket_timeout", 2); //get_headers wäre noch eine Möglichkeit das zu verbessern.
              if ($compress !== false) { //sehr unsauber.
                if (array_key_exists('x', $compress) && array_key_exists('y', $compress)) {
                $img = new Imagick();
                $img->readImage($img_url);
                $img->scaleImage(compress['x'], compress['y'], true);
                //$img->cropThumbnailImage(compress['x'], compress['y'], true);
                $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                $img->setImageCompressionQuality(90);
                $img->stripImage();
                $img->writeImage($img_src);
                //$img->clean();
                $body = str_replace($img_url,$savepath.'/img/'.$i.'.jpg',$body);
              }
            } else {
              $savefile = fopen($img_src, "w");
              fwrite($savefile, file_get_contents($img_url));
              fclose($savefile);
              $body = str_replace($img_url,'./img/'.$i.'.jpg',$body); //str_replace damit später das Format von der Webseite übernommen wird, aber wegen angriffen muss man das erst filtern, also ersteinmal wieder unsauber.
            }
              ini_set("default_socket_timeout", 10); //Eigentlich 60 aber 10 Sekunden sollten genügen.
            } catch (Exception $e) {
                echo 'Datei weißt einen Fehler auf: ',  $e->getMessage(), "\n";
            }
          } else {
            $body = str_replace($img_url,$savepath.'/img/'.$i.'.jpg',$body);
          }
        }
          return $body;
    } else {
      return $body;
   }
}

function render_list ($jsond) {
  global $pathtsite;

  global $modus; //Ja ich weiß das geht auch schöner.
  $modus = 4;

  require_once 'Parsedown.php';
  $Parsedown = new Parsedown();
  $Parsedown->setSafeMode(true);
  $Parsedown->setMarkupEscaped(true);
  $renderdata['body_parsedown'] = "";
    for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
      $data = gen_site_data(read_api($i,"permlink", 0));
      $data['body']  = render_content_images($data['body'], './'.$data["permlink"] , $data["getPost"]["result"]["json_metadata"], $data["permlink"], false);
      $renderdata['body_parsedown'] = $renderdata['body_parsedown']
      ."<artikel>"
      .'<a href="?artikel='. $data['permlink'] . '"><imgcontainer style="width: 100%; height: 180px; overflow: hidden; display: inline-block; position: relative;"><picture>'
      ;

      $json_metadata = read_api($i,"json_metadata", 0);
      $image = json_decode($json_metadata, true)["image"];
      $img_url = $image[0]; //Uncaught TypeError: Cannot access offset of type string on string 1h Stunde RIP nur weil ich Idiot $jsond genutzt habe.
      if (isset($image) && isset($img_url)) {
      //if (isset(json_decode(read_api($i,"json_metadata", 0), true)["image"]) && isset(json_decode(read_api($i,"json_metadata", 0), true)["image"][0])) {
        //Erstellt Vorschaubild zuerst nur für den PHP only Modus. Sollte vielleicht noch eine Funktion werden. https://stackoverflow.com/questions/10870129/compress-jpeg-on-server-with-php
        $img_src = $pathtsite.$data['permlink'].'/img/';
        //$filename = 'preview'.'.jpg'; bug wieso auch immer.

        if (!file_exists($pathtsite.$data['permlink'])) {
          $modus = 3; //Das auf alle fälle, aber ich mache das wann Anderes.
          gen_site($data['permlink'], false); //Ja ich sollte das auch anderes lösen. :D
          $modus = 4; //Das auf alle fälle, aber ich mache das wann Anderes.
        }

        if (!file_exists($img_src)) {
          mkdir($img_src);
        }

        $img_src = $img_src.'preview'.'.jpg';
        echo " ". $img_src;
        if (!file_exists($img_src)) {
          try { //Geht warscheinlich besser, aber mir fällt nichts besseres ein.
            $img = new Imagick();
            ini_set("default_socket_timeout", 2); //get_headers wäre noch eine Möglichkeit das zu verbessern.
            $img->readImage($img_url);
            ini_set("default_socket_timeout", 10); //Eigentlich 60 aber 10 Sekunden sollten genügen.
            //$img->scaleImage(284, 180, true); //Zu stark verpixelt bei der Index Seite.
            $img->cropThumbnailImage(284, 180, true); //Ist sonst zu stark verpixelt bei der Index Seite.
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(90);
            $img->stripImage();
            $img->writeImage($img_src);
            //$img->clean();
          } catch (Exception $e) {
              $img_src = "404.jpg";
              echo 'Datei weißt einen Fehler auf: ',  $e->getMessage(), "\n";
          }
        }

        $renderdata['body_parsedown'] = $renderdata['body_parsedown']
        .'<img src="'
        .$img_src
        .'" style="width: 100%; height: 180px; object-fit: cover;">';
      } else {
        $renderdata['body_parsedown'] = $renderdata['body_parsedown']
        .'<img src="'
        ."404.jpg"
        .'" style="width: 100%; height: 180px; object-fit: cover;">';
      }

      $renderdata['body_parsedown'] = $renderdata['body_parsedown']
      .'</picture><br></imgcontainer></a>'
      .'<a href="'."?artikel=".$data['permlink'].'">'.$data['title']."</a>"
      .'<description>'.$data['description'].'<description>'
      .'<button onclick="createArtikelContent_steamworld_api(' . "'" . $data['permlink'] . "'" . '); location.href=' . "'" . '#content_read' . "'" . ';">Beitrag lesen (Schnellansicht)</button>'
      ."<votes>Votes: up: ".$data['upvote_count'] ." down: ". +$data['downvote_count']."</votes>"
      ."<datum>".$datum = date("d.m.Y H:i",$data['datum'])."</datum>"
      ."</artikel>";
      $data['javascirpt'] = "//";
      $data['javascirpt_steemit'] = "//";
    }
    render($pathtsite.'artikellist.php', $pathtsite, "artikel.html", $renderdata);
    $modus =3;
}

function open_api_getPostsByAuthor () { //Die Funktion kann später falls benötigt auch die gesamte API öffnen.
  global $jsond;
  global $pathtemplate;
  global $modus;
  //Maximale Aufrufe 1 alle 5 Minuten.
  if (file_exists($pathtemplate."PostsByAuthor.json")) {  //Datei vorhanden?
    if (time() - filemtime($pathtemplate."PostsByAuthor.json")  >= 300) { //Sind die 5 Minuten abgelaufen? Vielleicht schneller als die komplette Datei zu lesen.
    $file = json_decode(file_get_contents($pathtemplate."PostsByAuthor.json"), true);
    //if ((time() - $file["datum"] >= 300)  ) { //Sind die 5 Minuten abgelaufen?
      //Neue Datei erstellen
      echo "Neue Datei erstellen";
      $json = file_get_contents("https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer");
      $jsond = json_decode($json, true);

      $jsone =[
          'datum'=> time(),
          'inhalt'=> $json,
      ];
      if ($jsond != json_decode($file['inhalt'], true)) { //Gleicher Inhalt?
          file_put_contents($pathtemplate."PostsByAuthor.json",json_encode($jsone));

          //Kommt vielleicht auch noch wo anderes hin oder wird über eine andere Funktion aufgerufen, obwohl eher nicht.
          if ($modus == 3) {
              render_list($jsond);
          }

      } else {
          touch($pathtemplate."PostsByAuthor.json"); //Gleicher Inhalt Datum ändern.
      }

      } else {
      //Datei lesen
      echo "Datei lesen";
      $file = json_decode(file_get_contents($pathtemplate."PostsByAuthor.json"), true);
      $jsond = json_decode($file['inhalt'], true);
    }
  } else {
    //Datei erstellen
    echo "Datei erstellen";
    $json = file_get_contents("https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer");
    $jsond = json_decode($json, true);

    $jsone =[
        'datum'=> time(),
        'inhalt'=> $json,
    ];

    file_put_contents($pathtemplate."PostsByAuthor.json",json_encode($jsone));
    if ($modus == 3) {
      render_list($jsond);
      //Renderfunktion PHP Schnellansicht als JSON oder HTML zum laden über GET oder POST.
    }
  }
  return $jsond;
}

function read_api(int $i, string $cols, bool $xss_protection) {
  global $jsond;
  if (isset($jsond) == false) {
  if (!isset($jsond)) {
    $jsond = open_api_getPostsByAuthor();
    }
}

if ($xss_protection) {
  return htmlspecialchars($jsond["result"]["rows"][$i][$jsond["result"]["cols"][$cols]], ENT_QUOTES, 'UTF-8');
}
return $jsond["result"]["rows"][$i][$jsond["result"]["cols"][$cols]];
}

//Wurde dieser Beitrag schon erstellt?
function exist_site(string $artikel) {
  global $pathtsite;
  $files = scandir($pathtsite, 1);
  foreach ($files as $key => $value) {
  if (!strstr($value, '.')) {
      if ($artikel == $value) {
          return $value;
        break;
      }
    }
  }
  return false;
}

function strposa($haystack, $needles=array(), $offset=0) { //https://stackoverflow.com/questions/6284553/using-an-array-as-needles-in-strpos
        $chr = array();
        foreach($needles as $needle) {
                $res = strpos($haystack, $needle, $offset);
                if ($res !== false) $chr[$needle] = $res; //Muss noch geändert werden. return
        }
        if(empty($chr)) return false;
        return min($chr);
}

function remove_makedown(string $text)
{
  //HTML Makedown Filter
  $text = str_replace(array("####### ", "###### ", "#### ", "### ", "## ", "# ","---","* ", "+ ", "- ", "= " , "`", "> "), "", $text);

  //Enter ersetzen
  $text = trim(preg_replace('/\s\s+/', ' ', $text));

  //HTML Code Filter
  for ($i=0; ($i <= 10 && (strpos($text, "<") !== false)  && (strpos($text, ">") !== false)); $i++) {
    $position = strpos($text, "<");
    if($position !== false) {
      $position2 = strpos($text, ">", $position) - $position;
      if($position2 != false) {
            $text = str_replace(substr($text, $position, $position2-$position+1), "",$text);
      }
    }
  }

  //Makedown Links Filter. Der ist so komplex geworden der sollte in eine Funktion und damit der leicher zu programieren ist.
  //while (strpos($text, "[") !== false && (strpos($text, ")") !== false)) { Gibt sonnst eine Endlosschleife.
  /*  for ($i=0; ($i <= 10 && (strpos($text, "[") !== false || strpos($text, "![") !== false) && ((strpos($text, ")") !== false) || strpos($text, "(") !== false)); $i++) {
      $position = strposa($text, array("[", "!["));
      if($position !== false) {
          $position2 = strposa($text, array(")", " "), $position - $position);
          if($position2 !== false) {
             $text = str_replace(substr($text, $position, $position2-$position+1), "",$text);
          } else {
              $text = str_replace(substr($text, $position, strlen($text)), "",$text);
          }
      }
  }*/

  for ($i=0; ($i <= 10 && (strpos($text, "[") !== false || strpos($text, "![") !== false) && ((strpos($text, ")") !== false) || strpos($text, "(") !== false)); $i++) {
    $position = strpos($text, "![");
    if($position !== false) {
          $position2 = strpos($text, ")", $position - $position);
          if($position2 !== false) {
             $text = str_replace(substr($text, $position, $position2-$position+1), "",$text);
          } else {
            $position2 = strpos($text, "(", $position - $position);
            if($position2 !== false) {
               $text = str_replace(substr($text, $position, $position2-$position+1), "",$text);
            } else {
              $text = str_replace(substr($text, $position, strlen($text)), "",$text);
              break;
            }
          }
      } else {
      $position = strpos($text, ("["));
      if($position !== false) {
        $position2 = strpos($text, ")", $position - $position);
        if($position2 !== false) {
           $text = str_replace(substr($text, $position, $position2-$position+1), "",$text);
        } else {
          $position2 = strpos($text, "(", $position - $position);
          if($position2 !== false) {
             $text = str_replace(substr($text, $position, $position2-$position+1), "",$text);
          } else {
            $text = str_replace(substr($text, $position, strlen($text)), "",$text);
            break;
          }
        }
      }
    }
  }

  //HTML Link Filter. Erkennt nur http und https Links.
  for ($i=0; ($i <= 10 && (strpos($text, "http") !== false)  && (strpos($text, " ") !== false)); $i++) {
    $position = strpos($text, "http");
    if($position !== false) {
      $position2 = strpos($text, " ", $position) - $position;
      if($position2 != false) {
            $text = str_replace(substr($text, $position, $position2), "",$text);
      }
    }
  }
  return htmlspecialchars($text);
}

function gen_site_data(string $permlink) {  //Gibt es diesen Beitrag im Blog?
//global $jsond; //global
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    if (read_api($i,"permlink", 0) == $permlink) {
        $permlink = read_api($i,"permlink", 0);
        $data['title'] = read_api($i,"title", 1);
        //Muss verbessert werden.
        $data['description'] = remove_makedown(read_api($i,"body", 0));
        $data['keywords'] = implode(", ", json_decode(read_api($i,"json_metadata", 0), true)["tags"]);
        $data['upvote_count'] = read_api($i,"upvote_count", 0);
        $data['downvote_count'] = read_api($i,"downvote_count", 0);
        $data['datum'] = read_api($i,"created", 0);
        $data['category'] = read_api($i,"category", 1);
        $data['author'] = read_api($i,"author", 1);
        $data['foldername'] = $permlink;
        $data['permlink'] = $permlink;

          $json_getPost = file_get_contents("https://sds.steemworld.org/posts_api/getPost/janisplayer/".$permlink);
          $jsond_getPost = json_decode($json_getPost, true);

          $data['getPost'] = $jsond_getPost;

          $data['body'] = $jsond_getPost["result"]["body"];
          //$data['body']  = render_content_images($data['body'], $jsond_getPost["result"]["json_metadata"], $permlink, false); Damit es für Docker mit den Links geht kommt das erst nach dem verarbeiten der Daten dran.

          $data['last_update'] = $jsond_getPost["result"]["last_update"];

        //return $permlink;
        return $data;
        break;
    }
  }
  return false;
}


function gen_site(string $permlink, bool $weiterleitung) { //Seite erstellen.
global $pathtemplate;
global $pathtsite;

  $exist_site_bool = false;
  $exist_site_bool = exist_site($permlink);

  if ($exist_site_bool != false) {
      $permlink = $exist_site_bool;

      if (file_check("index_".$permlink.".json", 3600)) { //Wenn 1 Stunde vergangen sind, die Datei exisistiert wird diese neu erstellt oder später auf neustellung geprüft.

        $savejson = false;
        $data = gen_site_data($permlink);
        $data['body']  = render_content_images($data['body'], './'.$data["permlink"] , $data["getPost"]["result"]["json_metadata"], $data["permlink"], false);
        //$data_index['body']  = render_content_images($data['body'], './'.$data["permlink"] , $data["getPost"]["result"]["json_metadata"], $data["permlink"], false);

        if (file_exists($pathtemplate."index_".$permlink.".json")) {  //Datei vorhanden?
          $file = json_decode(file_get_contents($pathtemplate."index_".$permlink.".json"), true);

          if ($file["permlink"] != $data["permlink"]) { //Sind die Daten noch aktuell? So ist das ganze schonender für die SSD braucht aber mehr leistung, vielleicht ist ein kompletter Abgleich auch besser.
            $savejson = ture;
          } elseif ($file["last_update"] != $data["last_update"]) {
            $savejson = true;
          } elseif ($file["upvote_count"] != $data["upvote_count"]) {
            $savejson = true;
          } elseif ($file["downvote_count"] != $data["downvote_count"]) {
            $savejson = true;
          } elseif ($file["body"] != $data["body"]) {
            $savejson = true;
          }
        } else {
            $savejson = true;
        }

        if ($savejson == true) {
          file_put_contents($pathtemplate."index_".$permlink.".json",json_encode($data)); //Möglicherweise Probleme bei anderen Überschriften.
          global $modus; //Ja okay ich schreibe das vielleicht noch um, ist dann halt größer aber sauberer und übersichtlicher und schneller beim ausführen.
          if ($modus == 3) { //Damit im PHP Modus die Seite auch neu erstellt wird.
            render($pathtemplate.'artikel.php', $pathtsite.$permlink.'/', "index.html", $data);
            /*$exist_site_bool = false;
            $gen_site_bool =  ture;*/
          }
        } else {
          touch($pathtemplate."index_".$permlink.".json"); //Ansonsten funktioniert der file_check nicht.
        }
      }
  }

  $gen_site_bool = false;
  $data;
  // if (($exist_site_bool == false) && (gen_site_bool == false)) {
  if ($exist_site_bool == false) {
    $gen_site_bool = gen_site_data($permlink);
    $data = $gen_site_bool;
    /*if ($gen_site_bool != false) { Ist eh Blödsinn-
        $permlink = $gen_site_bool;
    }*/
  }

  echo $exist_site_bool? 'true' : 'false';;
  echo $gen_site_bool? 'true' : 'false';;

  if (($exist_site_bool == false) && ($gen_site_bool != false)) {
      file_put_contents($pathtemplate."index_".$permlink.".json",json_encode($data));
      render($pathtemplate.'artikel.php', $pathtsite.$permlink.'/', "index.html", $data);
      if ($weiterleitung == true) {
      echo "Generiert weiterleitung... zu ".'./'.$permlink.'/';
      header('Location: ./'.$permlink.'/', true, 301);
      }
  } else {
    if ($exist_site_bool != false) {
      if ($weiterleitung == true) {
      echo "Generiert weiterleitung... zu ".'./'.$permlink.'/';
      header('Location: ./'.$permlink.'/', true, 301);
      }
      } else {
        if ($weiterleitung == true) {
        echo "Seite ungültig weiterleitung... zu ".'./';
        header('Location: ./');
        }
      }
  }
}

$data =[
    'title'=>'',
    'description'=>'',
    'keywords'=>'',
    'permlink'=>'',
    'datum'=>'',
    'foldername'=>'',
    'body' => '',
    'upvote_count' => '',
    'downvote_count' => '',
    'last_update' => '',
    'category' => '',
    'author' => '',
    'getPost' => ''
];

$pathtemplate = './templates/';
$pathtemplatename = './artikel.php';
$pathtsite = './';
if ($_SERVER['DOCUMENT_ROOT'] != getcwd()) {
  $pathtsitebugfix = '/'.basename(getcwd()).'/';
} else {
  $pathtsitebugfix = '/';
}

$permlink = "";

$modus = 3; //select 1 Javascirpt_Steem / 2 Javascript PHP / 3 PHP Only
/*if (isset($_GET['artikel'])) {
  gen_site($_GET['artikel'],true);
}*/

//Für die Hauptseite eine Möglichkeit alle Artikel zu überprüfen.

ob_start(); //Debug

if (isset($_GET['artikel'])) {
    gen_site($_GET['artikel'],true);
} else {
  if (file_exists('./artikel.html')) { //Verkürzt Ladezeit bei neu generierung der Seite.
    ob_end_clean(); //Debug
    include_once './artikel.html';
    ob_start(); //Debug
  }

  if (file_check("PostsByAuthor.json", 300)) { //Sind die 5 Minuten abgelaufen?
    $jsond = open_api_getPostsByAuthor();
    for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
        gen_site(read_api($i,"permlink", 0), false);
    }
  }
}

ob_end_clean(); //Debug

include_once './artikel.html'; //Muss vielleicht wo anders hin um den Dealy vom neugenerien nach 5 Min im PHP Modus zu verkleinern. Da es include_once ist sollte es selbst erkennen dass es oben schon eingebunden wurde.

/*if (iset($_POST['genallcontent'])) { //Wird per Javascript aufgerufen.
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    gen_site(read_api($i,"permlink"), false);
  }
}

if (isset($_GET['genalljson_pass1234'])) { //Wird später als Funktion umgeschreiben, die die ganzen Beiträge auf veränderung prüft, last_update, upvote_count, downvote_count.
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    $permlink = read_api($i,"permlink", 0);
    $data['title'] = read_api($i,"title", 1);
    //Muss verbessert werden.
    $data['description'] = read_api($i,"body", 1);
    $data['keywords'] = implode(", ", json_decode(read_api($i,"json_metadata", 0), true)["tags"]);
    $data['upvote_count'] = read_api($i,"upvote_count", 0);
    $data['downvote_count'] = read_api($i,"downvote_count", 0);
    $data['datum'] = read_api($i,"created", 0);
    $data['category'] = read_api($i,"category", 1);
    $data['author'] = read_api($i,"author", 1);
    $data['foldername'] = $permlink;
    $data['permlink'] = $permlink;

    $json_getPost = file_get_contents("https://sds.steemworld.org/posts_api/getPost/janisplayer/".$permlink);
    $jsond_getPost = json_decode($json_getPost, true);

    $data['getPost'] = $jsond_getPost;

    $data['body'] = $jsond_getPost["result"]["body"];
    $data['last_update'] = $jsond_getPost["result"]["last_update"];
    file_put_contents($pathtemplate."index_".$permlink.".json",json_encode($data));
  }
}*/
?>
