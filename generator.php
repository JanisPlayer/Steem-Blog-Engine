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
    $file = json_decode(file_get_contents($pathtemplate."PostsByAuthor.json"), true);
    if ((time() - $file["datum"] >= 300)  ) { //Sind die 5 Minuten abgelaufen?
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

$pathtemplate = '/var/www/html/artikel/templates/';
$pathtemplatename = 'artikel.php';
$pathtsite = '/var/www/html/artikel/';

$permlink = "";

if (isset($_GET['artikel'])) {
//Wurde dieser Beitrag schon erstellt?
$exist_site_bool = false;

  $files = scandir("/var/www/html/artikel/", 1);
  foreach ($files as $key => $value) {
  if (!strstr($value, '.')) {
      if ($_GET['artikel'] == $value) {
          $exist_site_bool = true;
          $permlink = $value;
        break;
      }
    }
  }
//Gibt es diesen Beitrag im Blog?
$gen_site_bool = false;


if ($exist_site_bool == false) { //Ich habe vergessen das $ zu setzen, juhu 1 Stunde RIP, aber dafür den Code überprüft und andere Fehler gefunden. :D

  //global $jsond; //global
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    if (read_api($i,"permlink") == $_GET['artikel']) {
        $permlink = read_api($i,"permlink");
        $data['title'] = read_api($i,"title");
        //Muss verbessert werden.
        //$data['description'] $jsond["result"]["rows"][$i][$jsond["cols"]["body"]];
        //$data['keywords'] = $jsond["result"]["rows"][$i][$jsond["cols"]["json_metadata"]];
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

        $gen_site_bool = true;
    }
  }

  /*for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    if ($jsond["result"]["rows"][$i][$jsond["result"]["cols"]["permlink"]] == $_GET['artikel']) {
        $permlink = $jsond["result"]["rows"][$i][$jsond["result"]["cols"]["permlink"]];
        $data['title'] = $jsond["result"]["rows"][$i][$jsond["result"]["cols"]["title"]];
        //Muss verbessert werden.
        //$data['description'] $jsond["result"]["rows"][$i][$jsond["cols"]["body"]];
        //$data['keywords'] = $jsond["result"]["rows"][$i][$jsond["cols"]["json_metadata"]];
        $data['upvote_count'] = $jsond["result"]["rows"][$i][$jsond["result"]["cols"]["upvote_count"]];
        $data['downvote_count'] = $jsond["result"]["rows"][$i][$jsond["result"]["cols"]["downvote_count"]];
        $data['datum'] = $jsond["result"]["rows"][$i][$jsond["result"]["cols"]["created"]];
        $data['foldername'] = $permlink;
        $data['permlink'] = $permlink;

        $json_getPost = file_get_contents("https://sds.steemworld.org/posts_api/getPost/janisplayer/".$permlink);
        $jsond_getPost = json_decode($json_getPost, true);

        $data['body'] = $jsond_getPost["result"]["body"];
        $data['last_update'] = $jsond_getPost["result"]["last_update"];

        $gen_site_bool = true;
    }
  }*/
}

echo $exist_site_bool? 'true' : 'false';;
echo $gen_site_bool? 'true' : 'false';;

if (($exist_site_bool == false) && ($gen_site_bool == true)) {
    file_put_contents($pathtemplate."index_".$permlink.".json",json_encode($data));
    render($pathtemplate.'artikel.php', $pathtsite.$permlink.'/', $data);
    echo "Generiert weiterleitung... zu ".'https://heldendesbildschirms.de/artikel/'.$permlink.'/';
    header('Location: https://heldendesbildschirms.de/artikel/'.$permlink.'/', true, 301);
} else {
  if ($exist_site_bool == true) {
    echo "Weiterleitung... zu ".'https://heldendesbildschirms.de/artikel/'.$permlink.'/';
    header('Location: https://heldendesbildschirms.de/artikel/'.$permlink.'/', true, 301);
    } else {
      echo "Seite ungültig weiterleitung... zu ".'https://heldendesbildschirms.de/artikel/';
      header('Location: https://heldendesbildschirms.de/artikel/');
    }
}
}

if (isset($_GET['genalljson_pass'])) { //Wird später als Funktion umgeschreiben, die die ganzen Beiträge auf veränderung prüft, last_update, upvote_count, downvote_count.
  $jsond = open_api_getPostsByAuthor();
  for ($i=0; $i < count($jsond["result"]["rows"]); $i++) {
    $permlink = read_api($i,"permlink");
    $data['title'] = read_api($i,"title");
    //Muss verbessert werden.
    //$data['description'] $jsond["result"]["rows"][$i][$jsond["cols"]["body"]];
    //$data['keywords'] = $jsond["result"]["rows"][$i][$jsond["cols"]["json_metadata"]];
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
}
?>
