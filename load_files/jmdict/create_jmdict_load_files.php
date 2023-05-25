<?php
ini_set('memory_limit', '-1');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pick and choose if you don't need certain SQL load files
$LOAD_VOCAB_FILE = true;
$LOAD_KANA_FILE = true;
$LOAD_KANJI_FILE = true;
$LOAD_MEANING_FILES = true;
$LOAD_TAG_NAMES_FILE = true;

$jmdict = "../jsons/jmdict_eng.json";
$json_object = file_get_contents($jmdict);
$json_object = json_decode($json_object);

$overall_vocab_sql_string = "";

$overall_kana_sql_string = "";
$overall_kana_tags_sql_string = "";

$overall_kanji_sql_string = "";
$overall_kanji_tags_sql_string = "";

$overall_glossary_sql_string = "";
$overall_glossary_tags_sql_string = "";
$overall_glossary_info_sql_string = "";

$overall_tag_names_sql_string = "";

$vocab_id = 0;

$kana_id = 1;
$kanji_id = 1;
$glossary_id = 1;
foreach ($json_object->words as $word) {
  if (!isset($word->id)) {
    echo 'ERROR AFTER WORD ' . $vocab_id;
    exit;
  }

  $vocab_id = $word->id;

  if ($LOAD_VOCAB_FILE) {
    $overall_vocab_sql_string .= "($vocab_id),\n";
  }

  if ($LOAD_KANA_FILE && isset($word->kana) && count($word->kana) > 0) {
    $kana_string = "";
    foreach($word->kana as $kana) {
      $applies_to_kanji_index = 0;
      foreach($kana->appliesToKanji as $applies_to_kanji) {
        if ($applies_to_kanji != '*') {
          $applies_to_kanji_index = 1;
          foreach ($word->kanji as $searching_kanji) {
            if ($applies_to_kanji == $searching_kanji->text) {
              break;
            }
            $applies_to_kanji_index++;
          }
        }
        $kana_text = $kana->text;
        $common = $kana->common ? 1 : 0;
        $kana_string .= "(${kana_id}, ${vocab_id}, \"${kana_text}\", ${applies_to_kanji_index}, ${common}),\n";

        foreach($kana->tags as $tag) {
          $tag = addslashes($tag);
          $overall_kana_tags_sql_string .= "(${kana_id}, \"$tag\"),\n";
        }
        
        $kana_id++;
      }

    }
    $overall_kana_sql_string .= $kana_string;
  }


  if ($LOAD_KANJI_FILE && isset($word->kanji) && count($word->kanji) > 0) {
    $kanji_string = "";
    $kanji_sub_index = 1;
    foreach($word->kanji as $kanji) {
      $kanji_text = $kanji->text;
      $common = $kanji->common ? 1 : 0;
      $kanji_string .= "(${kanji_id}, ${vocab_id}, ${kanji_sub_index}, \"${kanji_text}\", ${common}),\n";
      $kanji_sub_index++;

      foreach($kanji->tags as $tag) {
        $tag = addslashes($tag);
        $overall_kanji_tags_sql_string .= "(${kanji_id}, \"$tag\"),\n";
      }

      $kanji_id++;
    }
    $overall_kanji_sql_string .= $kanji_string;
  }


  if ($LOAD_MEANING_FILES && isset($word->sense) && count($word->sense) > 0) {

    foreach($word->sense as $sense) {
      $applies_to_kanji_text = "";
      $applies_to_kana_text = "";
      $gloss_text = "";

      if (count($sense->appliesToKanji) == 1 && $sense->appliesToKanji[0] == '*') {
        $applies_to_kanji_text = "";
      } else { // Applies to a specific kanji
        foreach($sense->appliesToKanji as $applies_to_kanji) {
          $applies_to_kanji_text .= $applies_to_kanji . ', ';
        }
        $applies_to_kanji_text = substr_replace($applies_to_kanji_text, '', -2, 2);
      }

      if (count($sense->appliesToKana) == 1 && $sense->appliesToKana[0] == '*') {
        $applies_to_kana_index = 0;
      } else { // Applies to a specific kana
        foreach($sense->appliesToKana as $applies_to_kana) {
          $applies_to_kana_text .= $applies_to_kana . ', ';
        }
        $applies_to_kana_text = substr_replace($applies_to_kana_text, '', -2, 2);
      }

      foreach($sense->gloss as $gloss) {
        $gloss_text .= $gloss->text . '; ';
      }

      $gloss_text = substr_replace($gloss_text, '', -2, 2);
      $gloss_text = addslashes($gloss_text);
      $overall_glossary_sql_string .= "(${glossary_id}, ${vocab_id}, \"${gloss_text}\", \"${applies_to_kanji_text}\", \"${applies_to_kana_text}\"),\n";


      // Tags and other info
      foreach($sense->dialect as $dialect) {
        $dialect = addslashes($dialect);
        $overall_glossary_tags_sql_string .= "(${glossary_id}, \"$dialect\", \"dialect\"),\n";
      }

      foreach($sense->field as $field) {
        $field = addslashes($field);
        $overall_glossary_tags_sql_string .= "(${glossary_id}, \"$field\", \"field\"),\n";
      }

      foreach($sense->misc as $misc) {
        $misc = addslashes($misc);
        $overall_glossary_tags_sql_string .= "(${glossary_id}, \"$misc\", \"misc\"),\n";
      }

      foreach($sense->partOfSpeech as $partOfSpeech) {
        $partOfSpeech = addslashes($partOfSpeech);
        $overall_glossary_tags_sql_string .= "(${glossary_id}, \"$partOfSpeech\", \"part_of_speech\"),\n";
      }

      foreach($sense->info as $info) {
        $info = addslashes($info);
        $overall_glossary_info_sql_string .= "(${glossary_id}, \"$info\"),\n";
      }

      $glossary_id++;
    }
  }

}

if ($LOAD_TAG_NAMES_FILE && isset($json_object->tags)) {
  foreach ($json_object->tags as $key=>$value) {
    $key = addslashes($key);
    $value = addslashes($value);
    $overall_tag_names_sql_string .= "(\"${key}\", \"${value}\"),\n";
  }
}


if ($LOAD_VOCAB_FILE) {
  $overall_vocab_sql_string = substr_replace($overall_vocab_sql_string, ';', -2, 1);

  $overall_vocab_sql_string =
  "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
  "DROP TABLE IF EXISTS jmdict.jmdict_vocab;\n" .
  "CREATE TABLE IF NOT EXISTS jmdict.jmdict_vocab (vocab_id INT PRIMARY KEY, INDEX(vocab_id));\n\n" .
  "INSERT INTO jmdict.jmdict_vocab (vocab_id) VALUES\n" . $overall_vocab_sql_string;

  file_put_contents("complete/jmdict_vocab.sql", $overall_vocab_sql_string);
}

if ($LOAD_KANA_FILE) {
  $overall_kana_sql_string = substr_replace($overall_kana_sql_string, ';', -2, 1);
  $overall_kana_tags_sql_string = substr_replace($overall_kana_tags_sql_string, ';', -2, 1);

  $overall_kana_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_kana;\n" .
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_kana (kana_id INT PRIMARY KEY, vocab_id INT NOT NULL, kana VARCHAR(50) NOT NULL, applies_to_kanji INT(2), common TINYINT(1), INDEX(vocab_id));\n\n" .
      "INSERT INTO jmdict.jmdict_kana (kana_id, vocab_id, kana, applies_to_kanji, common) VALUES\n" . $overall_kana_sql_string;

  $overall_kana_tags_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_kana_tags;\n" . 
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_kana_tags (kana_tag_id INT PRIMARY KEY AUTO_INCREMENT, kana_id INT NOT NULL, tag VARCHAR(50) NOT NULL, INDEX(kana_id));\n\n" .
      "INSERT INTO jmdict.jmdict_kana_tags (kana_id, tag) VALUES\n" . $overall_kana_tags_sql_string;

  file_put_contents("complete/jmdict_kana.sql", $overall_kana_sql_string);
  file_put_contents("complete/jmdict_kana_tags.sql", $overall_kana_tags_sql_string);
}

if ($LOAD_KANJI_FILE) {
  $overall_kanji_sql_string = substr_replace($overall_kanji_sql_string, ';', -2, 1);
  $overall_kanji_tags_sql_string = substr_replace($overall_kanji_tags_sql_string, ';', -2, 1);

  $overall_kanji_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_kanji;\n" .
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_kanji (kanji_id INT PRIMARY KEY, vocab_id INT NOT NULL, kanji_sub_id INT NOT NULL, kanji VARCHAR(50) NOT NULL, common TINYINT(1), INDEX(vocab_id));\n\n" .
      "INSERT INTO jmdict.jmdict_kanji (kanji_id, vocab_id, kanji_sub_id, kanji, common) VALUES\n" . $overall_kanji_sql_string;

  $overall_kanji_tags_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_kanji_tags;\n" . 
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_kanji_tags (kanji_tag_id INT PRIMARY KEY AUTO_INCREMENT, kanji_id INT NOT NULL, tag VARCHAR(50) NOT NULL, INDEX(kanji_id));\n\n" .
      "INSERT INTO jmdict.jmdict_kanji_tags (kanji_id, tag) VALUES\n" . $overall_kanji_tags_sql_string;


  file_put_contents("complete/jmdict_kanji.sql", $overall_kanji_sql_string);
  file_put_contents("complete/jmdict_kanji_tags.sql", $overall_kanji_tags_sql_string);
}

if ($LOAD_MEANING_FILES) {
  $overall_glossary_sql_string = substr_replace($overall_glossary_sql_string, ';', -2, 1);
  $overall_glossary_tags_sql_string = substr_replace($overall_glossary_tags_sql_string, ';', -2, 1);
  $overall_glossary_info_sql_string = substr_replace($overall_glossary_info_sql_string, ';', -2, 1);

  $overall_glossary_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_glossary;\n" .
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_glossary (glossary_id INT NOT NULL PRIMARY KEY, vocab_id INT NOT NULL, meaning VARCHAR(1000) NOT NULL, applies_to_kanji VARCHAR(1000), applies_to_kana VARCHAR(1000), lang VARCHAR(3) DEFAULT 'eng', INDEX(vocab_id));\n\n" .
      "INSERT INTO jmdict.jmdict_glossary (glossary_id, vocab_id, meaning, applies_to_kanji, applies_to_kana) VALUES\n" . $overall_glossary_sql_string;

  $overall_glossary_tags_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_glossary_tags;\n" . 
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_glossary_tags (glossary_tag_id INT PRIMARY KEY AUTO_INCREMENT, glossary_id INT NOT NULL, tag VARCHAR(50) NOT NULL, tag_type VARCHAR(50) NOT NULL, INDEX(glossary_id));\n\n" .
      "INSERT INTO jmdict.jmdict_glossary_tags (glossary_id, tag, tag_type) VALUES\n" . $overall_glossary_tags_sql_string;

  $overall_glossary_info_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_glossary_info;\n" . 
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_glossary_info (glossary_info_id INT PRIMARY KEY AUTO_INCREMENT, glossary_id INT NOT NULL, info VARCHAR(1000) NOT NULL, INDEX(glossary_id));\n\n" .
      "INSERT INTO jmdict.jmdict_glossary_info (glossary_id, info) VALUES\n" . $overall_glossary_info_sql_string;
      
  file_put_contents("complete/jmdict_glossary.sql", $overall_glossary_sql_string);
  file_put_contents("complete/jmdict_glossary_tags.sql", $overall_glossary_tags_sql_string);
  file_put_contents("complete/jmdict_glossary_info.sql", $overall_glossary_info_sql_string);
}

if ($LOAD_TAG_NAMES_FILE) {
  $overall_tag_names_sql_string = substr_replace($overall_tag_names_sql_string, ';', -2, 1);

  $overall_tag_names_sql_string =
      "CREATE DATABASE IF NOT EXISTS jmdict CHARACTER SET utf8mb4;\n" . 
      "DROP TABLE IF EXISTS jmdict.jmdict_tag_names;\n" . 
      "CREATE TABLE IF NOT EXISTS jmdict.jmdict_tag_names (tag_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, tag_key VARCHAR(50) NOT NULL, tag_value VARCHAR(200) NOT NULL, INDEX(tag_key));\n\n" .
      "INSERT INTO jmdict.jmdict_tag_names (tag_key, tag_value) VALUES\n" . $overall_tag_names_sql_string;

  file_put_contents("complete/jmdict_tag_names.sql", $overall_tag_names_sql_string); 
}
