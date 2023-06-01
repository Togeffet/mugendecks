<?php
  session_start();
  require('scripts.php');
  use Limelight\Limelight;

  require '../vendor/autoload.php';

  if (isset($_GET['dev_mode'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
  }

  

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
        return $item->lemma;
      }
    })->all();

    $lemma_array = array_filter($lemma_array);
   
    $lemma_count_array = array_count_values($lemma_array);
    arsort($lemma_count_array);

    foreach($lemma_count_array as $item=>$count) {
        $possibilities = [];

        $original_item = $item;
        $item = mb_convert_kana(trim($item), 'RS'); // trim and change half width kana to full width

        $possibilities[] = $item; // Regular version
        $possibilities[] = mb_convert_kana($item, 'Cc'); // Other kana version

        if (mb_substr($item, 0, 1) == 'お' || mb_substr($item, 0, 1) == '御') {
          $item = mb_substr($item, 1);
          $possibilities[] = $item;
          $possibilities[] = mb_convert_kana($item, 'Cc');
        }

        if (mb_substr($item, -1) == 'っ' || mb_substr($item, -1) == 'ッ' || mb_substr($item, -1) == 'ー' || mb_substr($item, -1) == 'に') {
          $item = mb_substr($item, 0, mb_strlen($item) - 1);
          $possibilities[] = $item;
          $possibilities[] = mb_convert_kana($item, 'Cc');
        }

        if (mb_substr($item, -2) == "する" || mb_substr($item, -2) == "スル") {
          $item = mb_substr($item, 0, mb_strlen($item) - 2);
          if (mb_strlen($item) > 0) {
            $possibilities[] = $item; // Non suru version
            $possibilities[] = mb_convert_kana($item, 'Cc'); // Other kana non suru version
          }
        }
        
        if (mb_substr($item, -2) == "そう" || mb_substr($item, -2) == "ソウ") {
          $item = mb_substr($item, 0, mb_strlen($item) - 2);
          if (mb_strlen($item) > 0) {
            $possibilities[] = $item; // Non sou version
            $possibilities[] = mb_convert_kana($item, 'Cc'); // Other kana non sou version
          }
        }

        if (mb_substr($item, -2) == "たち" || mb_substr($item, -2) == "タチ") {
          $item = mb_substr($item, 0, mb_strlen($item) - 2);
          if (mb_strlen($item) > 0) {
            $possibilities[] = $item; // Non sou version
            $possibilities[] = mb_convert_kana($item, 'Cc'); // Other kana non sou version
          }
        }

        if (mb_substr($item, -1) == "達") {
          $item = mb_substr($item, 0, mb_strlen($item) - 1);
          if (mb_strlen($item) > 0) {
            $possibilities[] = $item; // Non sou version
            $possibilities[] = mb_convert_kana($item, 'Cc'); // Other kana non sou version
          }
        }


        $question_marks = array_fill(0, count($possibilities), '?');
        $question_mark_string = implode(',', $question_marks);

        $types = array_fill(0, count($possibilities), 's');
        $type_string = implode('', $types);
        $type_string .= $type_string;
        
        $possibilities = array_merge($possibilities,$possibilities);

        $row = [];
        if (isset($_GET['check_db'])) {
          
          $stmt = mysqli_prepare(
            $conn,
            "SELECT vocab_id, symbol, word_priority FROM 
              (SELECT vocab_id, kanji AS symbol, 0 AS word_priority FROM jmdict.jmdict_kanji WHERE kanji IN (${question_mark_string})
                UNION 
               SELECT vocab_id, kana AS symbol, 1 AS word_priority FROM jmdict.jmdict_kana WHERE kana IN (${question_mark_string})) V
             ORDER BY word_priority ASC
             LIMIT 1"
          );

          if ( !$stmt ) {
            die('mysqli error: '.mysqli_error($conn));
          }
  
          mysqli_stmt_bind_param($stmt, $type_string, ...$possibilities);
          
          if (mysqli_stmt_execute($stmt)) {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
          } else {
            die( 'stmt error: '.mysqli_stmt_error($stmt) );
          }
        }
  
        //if ($item->partOfSpeech != 'symbol' && $item->partOfSpeech != 'postposition') {
          if (isset($_GET['missing_only'])) {
            if ($row == [] && isset($_GET['check_db'])) {
              echo "MISSING: ITEM: " .$original_item . ", SHOWN " . $count . " TIME(S)<br>";
            }
          } else {
            
            if ($row == [] && isset($_GET['check_db'])) {
              echo "MISSING: ";
            }
            echo "ITEM: " .$original_item . ", SHOWN " . $count . " TIME(S)" . (isset($_GET['check_db']) ? "--- IN DATABASE AS - <a target='_blank' href='/index?word_id=".$row['vocab_id']."'>ID: ". $row['vocab_id'] . ', WORD: ' . $row['symbol'] . '</a><br>' : '<br>');
          }
          
          
        //}
      //echo $key . ' -> ' . $val . '<br>';
      
    }

?>
</body>
</html>