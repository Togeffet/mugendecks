<?php
  session_start();
  require_once('dictionary.php');

  // ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
  // error_reporting(E_ALL);

  require('scripts.php');
  require '../vendor/autoload.php';

  $dexter = new Dexter();
  $random_int = rand(0, 211387);

  if ($stmt = mysqli_prepare($conn, 
      "SELECT KJ.kanji, KN.kana, G.meaning, G.applies_to_kanji, G.applies_to_kana FROM (SELECT * FROM jmdict_vocab ORDER BY RAND() LIMIT 1) V
        LEFT JOIN jmdict_kanji KJ ON KJ.vocab_id = V.vocab_id
        LEFT JOIN jmdict_kana KN ON KN.vocab_id = V.vocab_id AND (KN.applies_to_kanji = 0 OR KN.applies_to_kanji = KJ.kanji_sub_id)
        LEFT JOIN jmdict_glossary G ON G.vocab_id = V.vocab_id")) {

    if (mysqli_stmt_execute($stmt)) {
      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
        echo print_r($row) . '<br>';
      }
      
    }
  }
