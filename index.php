<?php
  session_start();
  require_once('dictionary.php');

  // ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
  // error_reporting(E_ALL);

  require('scripts.php');
  require '../vendor/autoload.php';

  $dexter = new Dexter();

  if (isset($_GET['word_id']) && intval($_GET['word_id']) >= 0) {
    $value = $dexter->getWordById(intval($_GET['word_id']));
    echo '<pre>'.json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</pre>';
  } else if (isset($_GET['get_load_starting_at']) && intval($_GET['get_load_starting_at']) >= 0) {

    $value = $dexter->getRange(intval($_GET['get_load_starting_at']));

    foreach($value as $vocab) {
      $kanji = $vocab['kanji'];
      $kana = $vocab['kana'];
      $sense = $vocab['sense'];

      echo 'Kana: <pre>'.json_encode($kana, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</pre><br><br>';


      if ($kanji != []) {
        echo 'Kanji: <pre>'.json_encode($kanji, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</pre><br><br>';
      }

      echo 'Sense: <pre>'.json_encode($sense, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</pre><br><br>';
      
    }
    // echo '<div>Refresh for a random vocab word from JMDict!</div>
    //       <pre>'.json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</pre>';



          
  } else {
    $random_int = rand(0, 206149);
    $value = $dexter->getWordById($random_int);
    echo '<div>Refresh for a random vocab word from JMDict!</div>
          <pre>'.json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</pre>';
  }

  if (isset($_GET['kanji'])) {
    $value = $dexter->getWordByKanji($_GET['kanji']);
    echo print_r($value);
  }