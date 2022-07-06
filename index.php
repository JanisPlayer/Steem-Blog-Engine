<?php
error_reporting(-1);
ini_set('display_errors', 'On');

function render(string $pathtemplate, string $pathtsite, array $data){

    if (!file_exists($pathtsite)) {
        if (!mkdir($pathtsite, 0700, true)) {
          die('Erstellung der Verzeichnisse schlug fehl...');
        }
    }

    if(!is_file($pathtemplate)){
        trigger_error('Datei "'.$pathtemplate.'" wurde nicht gefunden');
        return null;
    }
    extract($data,EXTR_SKIP);

    ob_start();
    require $pathtemplate;
    $content = ob_get_clean();

    $handle = fopen ("$pathtsite".'index.html', "w");
    fwrite ($handle, $content);
    fclose ($handle);

    echo "Seite erstellt unter".$pathtsite."index.html";
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

function open_api_getPostsByAuthor () { //Die Funktion kann später falls benötigt auch die gesamte API öffnen.
  global $jsond;
  global $pathtemplate;
  //Maximale Aufrufe 1 alle 5 Minuten.
  if (file_exists($pathtemplate."PostsByAuthor.json")) {  //Datei vorhanden?
    if (time() - filemtime($pathtemplate."PostsByAuthor.json")  >= 300) { //Sind die 5 Minuten abgelaufen? Vielleicht schneller als die komplette Datei zu lesen.
    //$file = json_decode(file_get_contents($pathtemplate."PostsByAuthor.json"), true);
    //if ((time() - $file["datum"] >= 300)  ) { //Sind die 5 Minuten abgelaufen?
      //Neue Datei erstellen
      echo "Neue Datei erstellen";
      $json = file_get_contents("https://sds.steemworld.org/feeds_api/getPostsByAuthor/janisplayer");
      $jsond = json_decode($json, true);

      $jsone =[
          'datum'=> time(),
          'inhalt'=> $json,
      ];
      file_put_contents($pathtemplate."PostsByAuthor.json",json_encode($jsone));
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

function gen_site_data(string $permlink) {  //Gibt es diesen Beitrag im Blog?
//global $jsond; //global
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    if (read_api($i,"permlink", 0) == $permlink) {
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
        $file = json_decode(file_get_contents($pathtemplate."index_".$permlink.".json"), true);

        $data = gen_site_data($permlink);

        $savejson = false;

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

        if ($savejson == true) {
          file_put_contents($pathtemplate."index_".$permlink.".json",json_encode($data)); //Möglicherweise Probleme bei anderen Überschriften.
        }
      }
  }

  $gen_site_bool = false;
  $data;
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
      render($pathtemplate.'artikel.php', $pathtsite.$permlink.'/', $data);
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

$permlink = "";

/*if (isset($_GET['artikel'])) {
  gen_site($_GET['artikel'],true);
}*/

//Für die Hauptseite eine Möglichkeit alle Artikel zu überprüfen.

ob_start(); //Debug

if (isset($_GET['artikel'])) {
  if (file_check("PostsByAuthor.json", 300)) { //Sind die 5 Minuten abgelaufen?
      gen_site($_GET['artikel'],true);
  }
} else {
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    gen_site(read_api($i,"permlink", 0), false);
  }
}

ob_end_clean(); //Debug

include_once './artikel.html';

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
