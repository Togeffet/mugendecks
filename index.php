<?php
session_start();
require_once('dictionary.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require('scripts.php');
require '../vendor/autoload.php';

if (isset($_GET['search_term']) && $_GET['search_term'] != "") {
  $search_term = $_GET['search_term'];
  $search_term = "%${search_term}%";

  $stmt = mysqli_prepare(
    $conn,
    "SELECT DISTINCT V.vocab_id, KJ.kanji, KN.kana, G.meaning, G.applies_to_kanji, G.applies_to_kana FROM
            (SELECT vocab_id FROM jmdict_kanji KJ WHERE KJ.kanji LIKE ?
              UNION
             SELECT vocab_id FROM jmdict_kana KN WHERE KN.kana LIKE ?
              UNION
             SELECT vocab_id FROM jmdict_glossary G WHERE G.meaning LIKE ?) V
          LEFT JOIN jmdict_kanji KJ ON KJ.vocab_id = V.vocab_id
          LEFT JOIN jmdict_kana KN ON KN.vocab_id = V.vocab_id AND (KN.applies_to_kanji = 0 OR KN.applies_to_kanji = KJ.kanji_sub_id)
          LEFT JOIN jmdict_glossary G ON G.vocab_id = V.vocab_id
          GROUP BY V.vocab_id
          LIMIT 50"
  );
  mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);
} else {
  $stmt = mysqli_prepare(
    $conn,
    "SELECT V.vocab_id, KJ.kanji, KN.kana, G.meaning, G.applies_to_kanji, G.applies_to_kana FROM (SELECT * FROM jmdict_vocab ORDER BY RAND() LIMIT 1) V
          LEFT JOIN jmdict_kanji KJ ON KJ.vocab_id = V.vocab_id
          LEFT JOIN jmdict_kana KN ON KN.vocab_id = V.vocab_id AND (KN.applies_to_kanji = 0 OR KN.applies_to_kanji = KJ.kanji_sub_id)
          LEFT JOIN jmdict_glossary G ON G.vocab_id = V.vocab_id"
  );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php
  echoHeaderMeta($css_version);
  ?>
</head>
<body>
  <div class="mainspace">
    <div class="content">
      <div><a href="/">無限デックス</a></div>
      <div>Test out the database with a search or just refresh the page for a random vocab word! Right now it's just returning simple data (e.g. only one definition and no tags yet but I'm working on that query now) but it'll search kanji, kana, and meanings for your search term so try it out!</div>
      <form action="/" method="get">
        <input type="text" name="search_term" placeholder="Search" />
        <input type="submit" value="Search" />
      </form>

      <table>
        <tr><td>Vocab ID</td><td>Kanji</td><td>Kana</td><td>Meaning</td><td>When read as (kanji)</td><td>When read as (kana)</td></tr>
        <?php
        if (mysqli_stmt_execute($stmt)) {
          $result = $stmt->get_result();
          while ($row = $result->fetch_assoc()) {
            $vocab_id = $row['vocab_id'];
            $kanji = $row['kanji'];
            $kana = $row['kana'];
            $meaning = $row['meaning'];
            $applies_to_kanji = $row['applies_to_kanji'];
            $applies_to_kana = $row['applies_to_kana'];
            echo "<tr><td>$vocab_id</td><td>$kanji</td><td>$kana</td><td>$meaning</td><td>$applies_to_kanji</td><td>$applies_to_kana</td></tr>\n";
          }
        }
        ?>
      </table>
    </div>
  </div>
</body>
<?php echoDefaultJSLink($js_version) ?>
</html>