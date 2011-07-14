<?php

$db = 'lichess';
$collection = 'game2';
$since = new \MongoDate(date_create('-15 seconds')->getTimestamp());

$mongo = new Mongo();
print $mongo->selectDB($db)->selectCollection($collection)->count(array(
    'updatedAt' => array(
        '$gt' => $since
    )
));
