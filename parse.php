<?php
  session_start();
  require_once('dictionary.php');

  if (isset($_GET['dev_mode'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
  }

  use Limelight\Limelight;
  require('scripts.php');
  require '../vendor/autoload.php';

  $text = '本当にデブが大好きです！';
  if (isset($_GET['text'])) {
    $text = $_GET['text'];
  } else if (isset($_GET['hanahira'])) {
    $text = file_get_contents("scripts/hanahira.txt");
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Parse</title>
  <style>html { background-color: #212121; color: #FAFAFA; } a { color: #448AFF; text-decoration: none; cursor: pointer; }</style>
  <base href="/" />
  <meta charset="utf-8" />
  <meta name="google" content="notranslate" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
<?php

  echo '<div>Type some Japanese text in the input below to see count of words - or go <a href="/parse?hanahira">here</a> if you want to see the result of parsed art</div>
        <form action="/parse" method="get">
          <textarea name="text" placeholder="本当にデブが大好きです！"></textarea>
          <input type="submit" value="Submit" />
        </form>';
        
    $limelight = new Limelight();
    $results = $limelight->parse($text);

    $lemma_array = $results->map(function ($item, $key) {
      if ($item->partOfSpeech != 'symbol' && $item->partOfSpeech != 'postposition') {
        return $item->lemma . ', ' . $item->partOfSpeech;
      }
    })->all();

    $lemma_count_array = array_count_values($lemma_array);
    arsort($lemma_count_array);

    foreach($lemma_count_array as $key=>$val){
      echo $key . ' -> ' . $val . '<br>';
    }

?>
</body>
</html>