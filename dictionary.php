<?php
use Kreait\Firebase\Factory;

class Dexter {

  public $factory;
  public $dictionary;

  public function __construct() {
    if (!isset($this->factory)) {
      $this->factory = (new Factory)
        ->withServiceAccount('../mugendecks_service_account.json')
        ->withDatabaseUri('https://mugendecks-default-rtdb.firebaseio.com');
    }

    if (!isset($this->dictionary)) {
      $this->dictionary = $this->factory->createDatabase();
    }
  }

  function getWordById($word_id) {
    $reference = $this->dictionary->getReference("words/${word_id}");
    return $reference->getValue();
  }

  function getRange($start) {
    $reference = $this->dictionary->getReference("words")
        ->orderByChild('id')
        ->startAt($start)
        ->limitToFirst($start + 10);
    return $reference->getValue();
  }

  function getWordByKanji($kanji) {
    $reference = $this->dictionary->getReference("words")->orderByChild('kanji/text')->equalTo($kanji);
    
    return $reference->getValue();
  }
}