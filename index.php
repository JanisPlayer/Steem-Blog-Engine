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

function read_api(int $i, string $cols) {
  global $jsond;
  if (isset($jsond) == false) {
  if (!isset($jsond)) {
    $jsond = open_api_getPostsByAuthor();
  /*  echo "ja";
  } else {
  echo "nein";
  echo isset($jsond);*/
    }
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
    if (read_api($i,"permlink") == $permlink) {
        $permlink = read_api($i,"permlink");
        $data['title'] = read_api($i,"title");
        //Muss verbessert werden.
        $data['description'] = read_api($i,"body");
        $data['keywords'] = implode(", ", json_decode(read_api($i,"json_metadata"), true)["tags"]);
        $data['upvote_count'] = read_api($i,"upvote_count");
        $data['downvote_count'] = read_api($i,"downvote_count");
        $data['datum'] = read_api($i,"created");
        $data['category'] = read_api($i,"category");
        $data['author'] = read_api($i,"author");
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
ob_start();
if (isset($_GET['artikel'])) {
  gen_site($_GET['artikel'],true);
} else {
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    gen_site(read_api($i,"permlink"), false);
  }
}
ob_end_clean();

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
    $permlink = read_api($i,"permlink");
    $data['title'] = read_api($i,"title");
    //Muss verbessert werden.
    $data['description'] = read_api($i,"body");
    $data['keywords'] = implode(", ", json_decode(read_api($i,"json_metadata"), true)["tags"]);
    $data['upvote_count'] = read_api($i,"upvote_count");
    $data['downvote_count'] = read_api($i,"downvote_count");
    $data['datum'] = read_api($i,"created");
    $data['category'] = read_api($i,"category");
    $data['author'] = read_api($i,"author");
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
