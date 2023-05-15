<?php
  session_start();

  // ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
  // error_reporting(E_ALL);

  require('scripts.php');
  require '../vendor/autoload.php';

  use Kreait\Firebase\Factory;

  $factory = (new Factory)
    ->withServiceAccount('../mugendecks_service_account.json')
    ->withDatabaseUri('https://mugendecks-default-rtdb.firebaseio.com');


  $word_id = 66286;
  if (isset($_GET['word_id']) && intval($_GET['word_id']) >= 0) {
    $word_id = $_GET['word_id'];
  }

  $database = $factory->createDatabase();
  $reference = $database->getReference("words/${word_id}");

  $snapshot = $reference->getSnapshot();
  $value = $snapshot->getValue();

  echo print_r($value);

  echo '<br><br><br>';

  echo print_r($value['kana']);
