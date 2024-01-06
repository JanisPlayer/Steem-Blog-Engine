<?php
error_reporting(-1);
ini_set('display_errors', 'On');

function render(string $pathtemplate, string $pathtsite, string $filename, array $data){
  global $modus;
  global $domain_path;
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
  $image = json_decode($data["getPost"]["result"]["json_metadata"], true)["image"];
  //$image = $data["getPost"]["result"]["json_metadata"]["image"];
  $img_url = $image[0];

  $img_src = $pathtsite.$data['permlink'].'/img/'.'preview_og:image.jpg'; //Damit das funktioniert in der IF Abfrage muss erst die Basis geändert werden.
  if (file_exists($img_src)  || (isset($image) && isset($img_url))) {
    //$data['img_src_preview'] = '<meta property="og:image" content="'.'./img/preview_og:image.jpg'.'"/>';
    $data['img_src_preview'] = '<meta property="og:image" content="'.$domain_path.$data['permlink'].'/img/preview_og:image.jpg'.'"/>';
    $data['img_src_preview_2'] = '<meta property="og:image" content="'.$domain_path.$data['permlink'].'/img/preview.webp'.'"/>';
  } else {
    $data['img_src_preview'] = '';
    $data['img_src_preview_2'] = '';
  }

  //Wird noch wo anderes hingemacht.
  if ($modus == 3) {
    require_once 'Parsedown.php';
    $Parsedown = new Parsedown();
    $Parsedown->setSafeMode(true);
    $Parsedown->setMarkupEscaped(true);
    $Parsedown->setBreaksEnabled(true);

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

function render_content_images(string $body, $json_metadata, string $permlink, $compress) { //array $compress aber mit geht das unsaubere unten nicht.
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
        //$filename = 'preview'.'.webp'; bug wieso auch immer.

        if (!file_exists($pathtsite.$permlink.'/')) {
          mkdir($pathtsite.$permlink.'/'); //Ja ich sollte das auch anderes lösen. :D
        }

        if (!file_exists($img_src)) {
          mkdir($img_src);
        }

        $trusted_format = array("jpg", "jpeg", "webp", "png", "gif", "bmp", "tif", "heif", "flif", "avif");

        $img_url_format = strtolower(substr($img_url,strlen($img_url)-4,4));
        $img_url_format_found = false;

        for ($ai=0; $ai < count($trusted_format); $ai++) {//ups ich weiß wieso es eine dauer schleife gegeben hat. i geht nicht ist ja schon oben.
          if (strpos($img_url_format, $trusted_format[$ai]) !==  false) {
            $img_url_format = $trusted_format[$ai];
            $img_url_format_found = true;
            break;
          } // else {
            // if ($i == count($trusted_format)) { //kann man besser lösen mit einer $img_url_format_temp.
            //   $img_url_format = false;
            // }
          // }
        }

        // if ($img_url_format == false) {
        if ($img_url_format_found == false) {
          $img_url_format = "webp";
        }
        $img_src_avif = $img_src.$i.'.avif';
        $img_src_compress = $img_src.$i.'.webp'; //Ja das sollte man auch anderes machen.
        $img_src = $img_src.$i.'.'.$img_url_format;

        echo " ". $img_src;
          if (!file_exists($img_src) || !file_exists($img_src_compress) || !file_exists($img_src_avif)) {

            $file_temp = file_get_contents($img_url);

            //($img_url_format_found != "webp") um ein Format zu prüfen, auszusortieren für die Speicherung in Original, nützlich für eine Speicherung aller Dateien als Original auf dem Webserver oder um Dateinen zu ersetzem mit einer WebServer eigenen Version als die nur im Blog verfügbar ist.
            if ((!file_exists($img_src)) && ($compress == false)) {
            $savefile = fopen($img_src, "w");
            fwrite($savefile, $file_temp);
            fclose($savefile);
            }

            if ($compress !== false) { //sehr unsauber.
              try { //Geht warscheinlich besser, aber mir fällt nichts besseres ein.
                ini_set("default_socket_timeout", 2); //get_headers wäre noch eine Möglichkeit das zu verbessern.

                // if ($compress !== false) { //sehr unsauber.

                    if (!file_exists($img_src_compress)) {
                    $img = new Imagick();
                    $img->readImageBlob($file_temp);
                    // if (array_key_exists('x', $compress) && array_key_exists('y', $compress)) {
                    //   $img->scaleImage(compress['x'], compress['y'], true);
                    // }
                    //$img->cropThumbnailImage(compress['x'], compress['y'], true);
                    //$img->setImageCompression(Imagick::COMPRESSION_JPEG);
                    $img->setImageFormat('webp');
                    // if ("gif" == $img_url_format) {
                    //   $profileImg->setImageCompressionQuality(100);
                    //   $profileImg->setOption('webp:lossless', 'true');
                    // } else {
                    //   $img->setImageCompressionQuality(90);
                    // }
                    $orientation = $img->getImageOrientation();
                    if (!empty($orientation)) {
                        switch ($orientation) {
                            case imagick::ORIENTATION_BOTTOMRIGHT:
                                $img->rotateimage("#000", 180);
                                break;

                            case imagick::ORIENTATION_RIGHTTOP:
                                $img->rotateimage("#000", 90);
                                break;

                            case imagick::ORIENTATION_LEFTBOTTOM:
                                $img->rotateimage("#000", -90);
                                break;
                        }
                    }
                    $img->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
                    $img->stripImage();
                    $img->writeImage($img_src_compress);
                  }
                  //$img->clean();
                  $body = str_replace($img_url,$pathtsitebugfix.$permlink.'/img/'.$i.'.webp',$body);
                ini_set("default_socket_timeout", 10); //Eigentlich 60 aber 10 Sekunden sollten genügen.
              } catch (Exception $e) {
                  echo 'Datei weißt einen Fehler auf: ',  $e->getMessage(), "\n";
              }

            if (!file_exists($img_src_avif)) {
              try { //Geht warscheinlich besser, aber mir fällt nichts besseres ein.
                ini_set("default_socket_timeout", 2); //get_headers wäre noch eine Möglichkeit das zu verbessern.
                    if (!file_exists($img_src_avif)) {
                    $img = new Imagick();
                    $img->readImageBlob($file_temp);
                    $img->setImageFormat('avif');
                    $img->setCompressionQuality(50);
                    $img->scaleImage(100, 56, true);
                    if (!empty($orientation)) {
                        switch ($orientation) {
                            case imagick::ORIENTATION_BOTTOMRIGHT:
                                $img->rotateimage("#000", 180);
                                break;

                            case imagick::ORIENTATION_RIGHTTOP:
                                $img->rotateimage("#000", 90);
                                break;

                            case imagick::ORIENTATION_LEFTBOTTOM:
                                $img->rotateimage("#000", -90);
                                break;
                        }
                    }
                    $img->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
                    $img->stripImage();
                    $img->writeImage($img_src_avif);
                  }
                  //$img->clean();
                  //$body = str_replace($img_url,$pathtsitebugfix.$permlink.'/img/'.$i.'.avif',$body);
                  unset($file_temp);
                  ini_set("default_socket_timeout", 10); //Eigentlich 60 aber 10 Sekunden sollten genügen.
                } catch (Exception $e) {
                    echo 'Datei weißt einen Fehler auf: ',  $e->getMessage(), "\n";
                }
              } else {
                $savefile = fopen($img_src, "w");
                fwrite($savefile, file_get_contents($img_url));
                fclose($savefile);
                $body = str_replace($img_url,'./img/'.$i.'.'.$img_url_format,$body); //str_replace damit später das Format von der Webseite übernommen wird, aber wegen angriffen muss man das erst filtern, also ersteinmal wieder unsauber.
              }
          }
        } else {
          //if (!file_exists($pathtsitebugfix.$permlink.'/img/'.$i.'.webp')) {
          if (!$compress !== false || "gif" == $img_url_format) {
            $body = str_replace($img_url,$pathtsitebugfix.$permlink.'/img/'.$i.'.'.$img_url_format,$body);
          } else {
            $body = str_replace($img_url,$pathtsitebugfix.$permlink.'/img/'.$i.'.webp',$body);
          }
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

  $scaley = 568;
  $scalex = 340;

  require_once 'Parsedown.php';
  $Parsedown = new Parsedown();
  $Parsedown->setSafeMode(true);
  $Parsedown->setMarkupEscaped(true);
  $renderdata['body_parsedown'] = "";
    for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
      $data = gen_site_data(read_api($i,"permlink", 0),true);
      $renderdata['body_parsedown'] = $renderdata['body_parsedown']
      ."<artikel>"
      //.'<a href="?artikel='. $data['permlink'] . '"><imgcontainer style="width: 100%; height: 180px; overflow: hidden; display: inline-block; position: relative;"><picture>'
      .'<a href="?artikel='. $data['permlink'] . '"><imgcontainer style="width: 100%; max-height: '.$scalex.'px; overflow: hidden; display: inline-block; position: relative;"><picture>'
      ;

      $json_metadata = read_api($i,"json_metadata", 0);
      $image = json_decode($json_metadata, true)["image"];
      $img_url = $image[0]; //Uncaught TypeError: Cannot access offset of type string on string 1h Stunde RIP nur weil ich Idiot $jsond genutzt habe.
      if (isset($image) && isset($img_url)) {
      //if (isset(json_decode(read_api($i,"json_metadata", 0), true)["image"]) && isset(json_decode(read_api($i,"json_metadata", 0), true)["image"][0])) {
        //Erstellt Vorschaubild zuerst nur für den PHP only Modus. Sollte vielleicht noch eine Funktion werden. https://stackoverflow.com/questions/10870129/compress-jpeg-on-server-with-php
        $img_src = $pathtsite.$data['permlink'].'/img/';
        //$filename = 'preview'.'.webp'; bug wieso auch immer.

        if (!file_exists($pathtsite.$data['permlink'])) {
          $modus = 3; //Das auf alle fälle, aber ich mache das wann Anderes.
          gen_site($data['permlink'], false); //Ja ich sollte das auch anderes lösen. :D
          $modus = 4; //Das auf alle fälle, aber ich mache das wann Anderes.
        }

        if (!file_exists($img_src)) {
          mkdir($img_src);
        }

        $img_src_og_image = $img_src.'preview_og:image.jpg';
        $img_src_avif = $img_src.'preview'.'.avif';
        $img_src = $img_src.'preview'.'.webp';
        echo " ". $img_src;
        if (!file_exists($img_src) || !file_exists($img_src_avif) || !file_exists($img_src_og_image)) {
          $file_temp = file_get_contents($img_url);
          if (!file_exists($img_src)) {
            try { //Geht warscheinlich besser, aber mir fällt nichts besseres ein.
              $img = new Imagick();
              ini_set("default_socket_timeout", 2); //get_headers wäre noch eine Möglichkeit das zu verbessern.
              $img->readImageBlob($file_temp);
              ini_set("default_socket_timeout", 10); //Eigentlich 60 aber 10 Sekunden sollten genügen.
              //$img->scaleImage(568, 340, true); //Zu stark verpixelt bei der Index Seite.
              $img->cropThumbnailImage($scaley, $scalex, true); //Ist sonst zu stark verpixelt bei der Index Seite.
              //$img->setImageCompression(Imagick::COMPRESSION_JPEG);
              $img->setImageFormat('webp');
              $img->setImageCompressionQuality(90);
              $img->stripImage();
              $img->writeImage($img_src);
              //$img->clean();
            } catch (Exception $e) {
                $img_src = "404.webp";
                echo 'Datei weißt einen Fehler auf: ',  $e->getMessage(), "\n";
            }
          }

          if (!file_exists($img_src_avif)) {
            try { //Geht warscheinlich besser, aber mir fällt nichts besseres ein.
              $img = new Imagick();
              ini_set("default_socket_timeout", 2); //get_headers wäre noch eine Möglichkeit das zu verbessern.
              $img->readImageBlob($file_temp);
              ini_set("default_socket_timeout", 10); //Eigentlich 60 aber 10 Sekunden sollten genügen.
              //$img->scaleImage(568, 340, true); //Zu stark verpixelt bei der Index Seite.
              $img->cropThumbnailImage(100, 56, true); //Ist sonst zu stark verpixelt bei der Index Seite.
              //$img->setImageCompression(Imagick::COMPRESSION_JPEG);
              $img->setImageFormat('avif');
              $img->setCompressionQuality(50);
              $img->stripImage();
              $img->writeImage($img_src_avif);
              //$img->clean();
            } catch (Exception $e) {
                $img_src = "404.webp";
                echo 'Datei weißt einen Fehler auf: ',  $e->getMessage(), "\n";
            }
          }

          if (!file_exists($img_src_og_image)) {
            try { //Geht warscheinlich besser, aber mir fällt nichts besseres ein.
              $img = new Imagick();
              ini_set("default_socket_timeout", 2); //get_headers wäre noch eine Möglichkeit das zu verbessern.
              $img->readImageBlob($file_temp);
              ini_set("default_socket_timeout", 10); //Eigentlich 60 aber 10 Sekunden sollten genügen.
              //$img->scaleImage(568, 340, true); //Zu stark verpixelt bei der Index Seite.
              $img->cropThumbnailImage(300, 200, true); //Ist sonst zu stark verpixelt bei der Index Seite.
              //$img->setImageCompression(Imagick::COMPRESSION_JPEG);
              $img->setImageFormat('webp');
              $img->setImageCompressionQuality(90);
              $img->stripImage();
              $img->writeImage($img_src_og_image);
              //$img->clean();
            } catch (Exception $e) {
                $img_src_og_image = "404.jpg";
                echo 'Datei weißt einen Fehler auf: ',  $e->getMessage(), "\n";
            }
          }
          unset($file_temp);
        }

        $renderdata['body_parsedown'] = $renderdata['body_parsedown']
        .'<img src="'
        .$img_src
        //.'" style="width: 100%; height: 180px; object-fit: cover;">';
        .'" style="width: 100%; max-height: '.$scalex.'px; object-fit: cover;">';
      } else {
        $renderdata['body_parsedown'] = $renderdata['body_parsedown']
        .'<img src="'
        ."404.webp"
        .'" style="width: 100%; max-height: '.$scalex.'px; object-fit: cover;">';
      }

      $renderdata['body_parsedown'] = $renderdata['body_parsedown']
      .'</picture><br></imgcontainer></a>'
      .'<a href="'."?artikel=".$data['permlink'].'">'.$data['title']."</a>"
      .'<description>'.$data['description'].'<description>'
      .'<button onclick="createArtikelContent_steamworld_api(' . "'" . $data['permlink'] . "'" . '); location.href=' . "'" . '#content_read' . "'" . ';">Beitrag lesen (Schnellansicht)</button>'
      ."<votes>Votes: up: ".$data['upvote_count'] ." down: ". +$data['downvote_count']."</votes>"
      ."<datum>".date("d.m.Y H:i",$data['datum'])."</datum>"
      ."</artikel>";
      $data['javascirpt'] = "//";
      $data['javascirpt_steemit'] = "//";
    }
    render($pathtsite.'artikellist.php', $pathtsite, "artikel.html", $renderdata);
    $modus =3;
}

class SimpleXMLExtended extends SimpleXMLElement {
  public function addCData($cdata_text) {
    $node= dom_import_simplexml($this);
    $no = $node->ownerDocument;
    $node->appendChild($no->createCDATASection($cdata_text));
  }
}

function render_rss_feed($jsond) {
    global $pathtsite, $pathtemplate, $domain_path;

    $parsed_url = parse_url($domain_path);
    $domain = $parsed_url['scheme'] . '://' . $parsed_url['host'];
    $time = "-2 weeks";
    $min_artikel = 10;

    $lastartikelcreate = 0;

    for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
        $artikelcreate = read_api($i, "created", 0);

        if ($lastartikelcreate < $artikelcreate) {
            $lastartikelcreate = $artikelcreate;
        }
    }

    $rssFilePath = $pathtemplate . "rss_feed.rdf";

    if (file_exists($rssFilePath) || $lastartikelcreate <= filemtime($rssFilePath)) {
        return;
    }

    $rssFeed = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"></rss>');
    $channel = $rssFeed->addChild('channel');
    $channel->addChild('title', 'Helden des Bildschirms | Blog');
    $channel->addChild('link', $domain);
    $channel->addChild('description', 'Blog über Technik und alles Mögliche was mir einfällt');
    $channel->addChild('lastBuildDate', date("D, d M Y H:i:s O", strtotime("now")));
    $channel->addChild('language', 'de');

    for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
        $createdTimestamp = read_api($i, "created", 0);

        if ($createdTimestamp >= strtotime($time) || $i < $min_artikel) {
            $item = $channel->addChild('item');
            $item->addChild('title', read_api($i, "title", 1));
            $item->addChild('link', $domain_path . read_api($i, "permlink", 0));
            $item->addChild('description', str_replace(["\n", "\r", "  "], ' ', trim(remove_makedown(read_api($i, "body", 0)))));

            $json_metadata = read_api($i, "json_metadata", 0);
            $image = json_decode($json_metadata, true)["image"];
            $img_url = $image[0];

            if (isset($image) && isset($img_url)) {
                $img_src = $domain_path . read_api($i, "permlink", 0) . '/img/preview.webp';
                $item->addChild('image_link', $img_src);
                $cdata = $item->addChild('encoded', '', 'http://purl.org/rss/1.0/modules/content/');
                $cdata[0]->addCData('<img src="'.$img_src.'" alt="preview image">');
            }

            $item->addChild('pubDate', date("D, d M Y H:i:s O", $createdTimestamp));
        }
    }

    $xmlrssFeed= $rssFeed->asXML();
    
    $dom = new DOMDocument();
    $dom->loadXML($xmlrssFeed);
    $dom->formatOutput = true;
    $formattedXML = $dom->saveXML();

    $fp = fopen($rssFilePath,'w+');
    fwrite($fp, $formattedXML);
    fclose($fp);
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
      //$json = file_get_contents("https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer");
      $cURLConnection = curl_init();
      curl_setopt($cURLConnection, CURLOPT_URL, 'https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer');
      curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
      $json = curl_exec($cURLConnection);
      curl_close($cURLConnection);

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
              render_rss_feed($jsond);
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
    //$json = file_get_contents("https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer");
    $cURLConnection = curl_init();
    curl_setopt($cURLConnection, CURLOPT_URL, 'https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer');
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($cURLConnection);
    curl_close($cURLConnection);
    $jsond = json_decode($json, true);

    $jsone =[
        'datum'=> time(),
        'inhalt'=> $json,
    ];

    file_put_contents($pathtemplate."PostsByAuthor.json",json_encode($jsone));
    if ($modus == 3) {
      render_list($jsond);
      render_rss_feed($jsond);
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

function gen_site_data(string $permlink, bool $read_local) {  //Gibt es diesen Beitrag im Blog?
  global $pathtemplate;
  global $pathtsite;
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
        $data['last_update'] = read_api($i,"last_update", 0);

          //$json_getPost = file_get_contents("https://sds.steemworld.org/posts_api/getPost/janisplayer/".$permlink);
          if ($read_local == false || !file_exists($pathtemplate."index_".$permlink.".json")) {
            $cURLConnection = curl_init();
            curl_setopt($cURLConnection, CURLOPT_URL, 'https://sds.steemworld.org/posts_api/getPost/janisplayer/'.$permlink);
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            $json_getPost = curl_exec($cURLConnection);
            curl_close($cURLConnection);
            $jsond_getPost = json_decode($json_getPost, true);
          } else {
            $file = json_decode(file_get_contents($pathtemplate."index_".$permlink.".json"), true);
            $jsond_getPost = $file["getPost"];
          }

          $data['getPost'] = $jsond_getPost;

          $data['body'] = $jsond_getPost["result"]["body"];
          $data['body']  = render_content_images($data['body'], $jsond_getPost["result"]["json_metadata"], $permlink, true);
          //$data['description'] = substr(remove_makedown($data['body']),0,200); //Eine Idee für bessere Beschreibungen, die entweder mit ... oder nach einem Satz zeichen gekürtzt werden bis zu einer minimalen Zeichen anzahl von 150 Zeichen, wenn das nicht geht soll "..." dahinter stehen.
          //$data['last_update'] = $jsond_getPost["result"]["last_update"];

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
        $data = gen_site_data($permlink,true);
        if (file_exists($pathtemplate."index_".$permlink.".json")) {  //Datei vorhanden?
          $file = json_decode(file_get_contents($pathtemplate."index_".$permlink.".json"), true);

          if ($file["last_update"] != $data["last_update"]) { //Sind die Daten noch aktuell? So ist das ganze schonender für die SSD braucht aber mehr leistung, vielleicht ist ein kompletter Abgleich auch besser.
            $savejson = true;
          } elseif ($file["upvote_count"] != $data["upvote_count"]) {
            $savejson = true;
          } elseif ($file["downvote_count"] != $data["downvote_count"]) {
            $savejson = true;
          }
        } else {
            $savejson = true;
        }

        if ($savejson == true) {
          $data = gen_site_data($permlink,false);
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
    $gen_site_bool = gen_site_data($permlink,false);
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
$domain_path = "https://heldendesbildschirms.de/artikel/";

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
    //header('Location: ./artikel.html', true, 302); //fix long load but is bad. geht nicht.
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
