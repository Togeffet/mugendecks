<?php
session_start();
require '/unimportant.php'; // Important file outside webserver

$js_version = 1;
$css_version = 1;

// Database connection
$conn = mysqli_connect($dblocation, $dbuser, $dbpass, "jmdict");
$conn->set_charset("utf8mb4");

function echoHeaderMeta($css_version, $allow_crawl = true, $title = "無限デックス", $description = "Learn Japanese using scripts from your favorite anime, novels, movies, and more!", 
    $social_title = "無限デックス", $social_description = "Learn Japanese using scripts from your favorite anime, novels, movies, and more!") {
  echo '<style>html { background-color: #212121; color: #FAFAFA; } body { opacity: 0 } }</style>
  <base href="/" />
  <meta charset="utf-8" />
  <meta name="google" content="notranslate" />'.
  (!$allow_crawl ? '<meta name="robots" content="noindex">' : '');
  echo '<title>出来るか? - '.$title.'</title>
  <meta name="type" content="website" />
  <meta name="image" content="apple-touch-icon.png" />
  <meta name="url" content="https://mugendecks.com" />
  <meta name="description" content="'.$description.'" />

  <meta property="og:title" content="'.$social_title.'" />
  <meta property="og:type" content="website" />
  <meta property="og:image" content="apple-touch-icon.png" />
  <meta property="og:url" content="https://mugendecks.com" />
  <meta property="og:description" content="'.$social_description.'" />

  <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300;500;900&family=Noto+Serif+JP:wght@300;500;900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@300;500;900&display=swap" rel="stylesheet">

  <link rel="preload" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap" as="style">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons&display=swap">';

  echo '<link rel="preload" href="styles/styles.css?'.$css_version.'" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">
  <noscript><link rel="stylesheet" href="styles.css?'.$css_version.'"></noscript>';
}

function echoDefaultJSLink($js_version) {
  echo "<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>\n
        <script src='/js/js.js?$js_version' type='text/javascript'></script>";
}
?>
